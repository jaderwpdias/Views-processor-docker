<?php

namespace IoT;

class MetricsCollector {
    
    public function collectAll(): array {
        return [
            'timestamp' => time(),
            'cpu' => $this->getCpuMetrics(),
            'memory' => $this->getMemoryMetrics(),
            'disk' => $this->getDiskMetrics(),
            'network' => $this->getNetworkMetrics(),
            'system' => $this->getSystemMetrics()
        ];
    }
    
    private function getCpuMetrics(): array {
        $hostRoot = getenv('HOST_ROOT') ?: '/host';
        
        // Detectar sistema operacional
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            // macOS
            return $this->getCpuMetricsDarwin();
        } else {
            // Linux
            return $this->getCpuMetricsLinux($hostRoot);
        }
    }
    
    private function getCpuMetricsDarwin(): array {
        // Usar comandos específicos do macOS
        $cpuUsage = shell_exec("top -l 1 | grep 'CPU usage' | awk '{print $3}' | sed 's/%//'");
        $cpuUsage = floatval(trim($cpuUsage));
        
        $cpuCores = shell_exec("sysctl -n hw.ncpu");
        $cpuCores = intval(trim($cpuCores));
        
        // Tentar obter temperatura (pode não estar disponível)
        $temp = 0;
        $tempOutput = shell_exec("sudo powermetrics -n 1 -i 1000 | grep 'CPU die temperature' | awk '{print $4}'");
        if ($tempOutput) {
            $temp = floatval(trim($tempOutput));
        }
        
        return [
            'usage' => $cpuUsage,
            'cores' => $cpuCores,
            'temperature' => $temp
        ];
    }
    
    private function getCpuMetricsLinux(string $hostRoot): array {
        $cpuInfo = file_get_contents($hostRoot . '/proc/stat');
        $lines = explode("\n", $cpuInfo);
        $cpuLine = $lines[0];
        
        preg_match('/cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $cpuLine, $matches);
        
        if (count($matches) >= 9) {
            $user = $matches[1];
            $nice = $matches[2];
            $system = $matches[3];
            $idle = $matches[4];
            $iowait = $matches[5];
            $irq = $matches[6];
            $softirq = $matches[7];
            $steal = $matches[8];
            
            $total = $user + $nice + $system + $idle + $iowait + $irq + $softirq + $steal;
            $usage = $total - $idle;
            $usagePercent = round(($usage / $total) * 100, 2);
            
            return [
                'usage' => $usagePercent,
                'cores' => $this->getCpuCores($hostRoot),
                'temperature' => $this->getCpuTemperature($hostRoot)
            ];
        }
        
        return ['usage' => 0, 'cores' => 0, 'temperature' => 0];
    }
    
    private function getCpuCores(string $hostRoot = ''): int {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            $cpuCores = shell_exec("sysctl -n hw.ncpu");
            return intval(trim($cpuCores));
        } else {
            $cpuInfo = file_get_contents($hostRoot . '/proc/cpuinfo');
            return substr_count($cpuInfo, 'processor');
        }
    }
    
    private function getCpuTemperature(string $hostRoot = ''): float {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            // macOS - tentar obter temperatura
            $tempOutput = shell_exec("sudo powermetrics -n 1 -i 1000 | grep 'CPU die temperature' | awk '{print $4}'");
            if ($tempOutput) {
                return floatval(trim($tempOutput));
            }
            return 0;
        } else {
            // Linux
            $tempFiles = [
                $hostRoot . '/sys/class/thermal/thermal_zone0/temp',
                $hostRoot . '/sys/class/hwmon/hwmon0/temp1_input',
                $hostRoot . '/sys/class/hwmon/hwmon1/temp1_input'
            ];
            
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    $temp = file_get_contents($file);
                    return round($temp / 1000, 1);
                }
            }
            return 0;
        }
    }
    
    private function getMemoryMetrics(): array {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            return $this->getMemoryMetricsDarwin();
        } else {
            return $this->getMemoryMetricsLinux();
        }
    }
    
    private function getMemoryMetricsDarwin(): array {
        // Usar vm_stat para macOS
        $vmStat = shell_exec("vm_stat");
        $lines = explode("\n", $vmStat);
        
        $pageSize = 4096; // Tamanho padrão da página no macOS
        $total = 0;
        $free = 0;
        
        foreach ($lines as $line) {
            if (strpos($line, 'Pages free:') !== false) {
                $free = intval(preg_replace('/[^0-9]/', '', $line)) * $pageSize;
            }
        }
        
        // Obter memória total
        $totalOutput = shell_exec("sysctl -n hw.memsize");
        $total = intval(trim($totalOutput));
        
        $used = $total - $free;
        $usagePercent = round(($used / $total) * 100, 2);
        
        return [
            'total' => round($total / 1024 / 1024, 2), // MB
            'used' => round($used / 1024 / 1024, 2),   // MB
            'available' => round($free / 1024 / 1024, 2), // MB
            'usage' => $usagePercent
        ];
    }
    
    private function getMemoryMetricsLinux(): array {
        $hostRoot = getenv('HOST_ROOT') ?: '/host';
        $memInfo = file_get_contents($hostRoot . '/proc/meminfo');
        $lines = explode("\n", $memInfo);
        
        $total = 0;
        $available = 0;
        
        foreach ($lines as $line) {
            if (strpos($line, 'MemTotal:') === 0) {
                $total = (int) preg_replace('/[^0-9]/', '', $line);
            } elseif (strpos($line, 'MemAvailable:') === 0) {
                $available = (int) preg_replace('/[^0-9]/', '', $line);
            }
        }
        
        $used = $total - $available;
        $usagePercent = round(($used / $total) * 100, 2);
        
        return [
            'total' => round($total / 1024, 2), // MB
            'used' => round($used / 1024, 2),   // MB
            'available' => round($available / 1024, 2), // MB
            'usage' => $usagePercent
        ];
    }
    
    private function getDiskMetrics(): array {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            return $this->getDiskMetricsDarwin();
        } else {
            return $this->getDiskMetricsLinux();
        }
    }
    
    private function getDiskMetricsDarwin(): array {
        // Usar df para macOS
        $output = shell_exec("df -h / | tail -1");
        $parts = preg_split('/\s+/', trim($output));
        
        if (count($parts) >= 5) {
            $total = $parts[1];
            $used = $parts[2];
            $usagePercent = (int) rtrim($parts[4], '%');
            
            return [
                'total' => $total,
                'used' => $used,
                'usage' => $usagePercent
            ];
        }
        
        return ['total' => '0', 'used' => '0', 'usage' => 0];
    }
    
    private function getDiskMetricsLinux(): array {
        $hostRoot = getenv('HOST_ROOT') ?: '/host';
        $output = shell_exec("df -h {$hostRoot} 2>/dev/null");
        $lines = explode("\n", $output);
        
        if (count($lines) >= 2) {
            $parts = preg_split('/\s+/', trim($lines[1]));
            if (count($parts) >= 5) {
                $total = $parts[1];
                $used = $parts[2];
                $usagePercent = (int) rtrim($parts[4], '%');
                
                return [
                    'total' => $total,
                    'used' => $used,
                    'usage' => $usagePercent
                ];
            }
        }
        
        return ['total' => '0', 'used' => '0', 'usage' => 0];
    }
    
    private function getNetworkMetrics(): array {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            return $this->getNetworkMetricsDarwin();
        } else {
            return $this->getNetworkMetricsLinux();
        }
    }
    
    private function getNetworkMetricsDarwin(): array {
        // Usar netstat para macOS
        $netstat = shell_exec("netstat -ib | grep -E '^(en|wl)' | head -1");
        $parts = preg_split('/\s+/', trim($netstat));
        
        if (count($parts) >= 7) {
            $rxBytes = intval($parts[6]);
            $txBytes = intval($parts[9]);
            
            return [
                'rx_bytes' => $rxBytes,
                'tx_bytes' => $txBytes,
                'rx_mb' => round($rxBytes / 1024 / 1024, 2),
                'tx_mb' => round($txBytes / 1024 / 1024, 2)
            ];
        }
        
        return ['rx_bytes' => 0, 'tx_bytes' => 0, 'rx_mb' => 0, 'tx_mb' => 0];
    }
    
    private function getNetworkMetricsLinux(): array {
        $hostRoot = getenv('HOST_ROOT') ?: '/host';
        $netDev = file_get_contents($hostRoot . '/proc/net/dev');
        $lines = explode("\n", $netDev);
        
        $totalRx = 0;
        $totalTx = 0;
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false && !strpos($line, 'lo:')) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 10) {
                    $totalRx += (int) $parts[1];
                    $totalTx += (int) $parts[9];
                }
            }
        }
        
        return [
            'rx_bytes' => $totalRx,
            'tx_bytes' => $totalTx,
            'rx_mb' => round($totalRx / 1024 / 1024, 2),
            'tx_mb' => round($totalTx / 1024 / 1024, 2)
        ];
    }
    
    private function getSystemMetrics(): array {
        $os = php_uname('s');
        
        if ($os === 'Darwin') {
            return $this->getSystemMetricsDarwin();
        } else {
            return $this->getSystemMetricsLinux();
        }
    }
    
    private function getSystemMetricsDarwin(): array {
        // Uptime no macOS
        $uptime = shell_exec("uptime");
        preg_match('/up\s+([^,]+)/', $uptime, $matches);
        $uptimeStr = $matches[1] ?? '0';
        
        // Converter para segundos (aproximado)
        $uptimeSeconds = $this->parseUptimeString($uptimeStr);
        
        // Load average
        $loadAvg = shell_exec("uptime | awk -F'load averages:' '{print $2}'");
        $loadParts = explode(',', $loadAvg);
        
        $load1 = floatval(trim($loadParts[0]));
        $load5 = floatval(trim($loadParts[1]));
        $load15 = floatval(trim($loadParts[2]));
        
        return [
            'uptime' => $uptimeSeconds,
            'uptime_formatted' => $uptimeStr,
            'load_1min' => $load1,
            'load_5min' => $load5,
            'load_15min' => $load15
        ];
    }
    
    private function getSystemMetricsLinux(): array {
        $hostRoot = getenv('HOST_ROOT') ?: '/host';
        $uptime = file_get_contents($hostRoot . '/proc/uptime');
        $parts = explode(' ', $uptime);
        $uptimeSeconds = (float) $parts[0];
        
        $loadAvg = file_get_contents($hostRoot . '/proc/loadavg');
        $loadParts = explode(' ', $loadAvg);
        
        return [
            'uptime' => $uptimeSeconds,
            'uptime_formatted' => $this->formatUptime($uptimeSeconds),
            'load_1min' => (float) $loadParts[0],
            'load_5min' => (float) $loadParts[1],
            'load_15min' => (float) $loadParts[2]
        ];
    }
    
    private function parseUptimeString(string $uptimeStr): float {
        // Converter strings como "2 days, 3 hours, 15 minutes" para segundos
        $seconds = 0;
        
        if (preg_match('/(\d+)\s*days?/', $uptimeStr, $matches)) {
            $seconds += intval($matches[1]) * 86400;
        }
        
        if (preg_match('/(\d+)\s*hours?/', $uptimeStr, $matches)) {
            $seconds += intval($matches[1]) * 3600;
        }
        
        if (preg_match('/(\d+)\s*minutes?/', $uptimeStr, $matches)) {
            $seconds += intval($matches[1]) * 60;
        }
        
        return $seconds;
    }
    
    private function formatUptime(float $seconds): string {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } else {
            return "{$minutes}m";
        }
    }
}
