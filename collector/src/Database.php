<?php

namespace IoT;

use PDO;
use PDOException;

class Database {
    private $pdo;
    
    public function __construct() {
        $host = getenv('DB_HOST') ?: 'iot-db';
        $dbname = getenv('DB_NAME') ?: 'iot_metrics';
        $user = getenv('DB_USER') ?: 'iot_user';
        $pass = getenv('DB_PASS') ?: 'iot_password';
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Erro de conexão com banco: " . $e->getMessage());
        }
    }
    
    public function saveMetrics(array $metrics): bool {
        try {
            $sql = "INSERT INTO system_metrics (
                timestamp, cpu_usage, cpu_cores, cpu_temperature,
                memory_total, memory_used, memory_available, memory_usage,
                disk_total, disk_used, disk_usage,
                network_rx_bytes, network_tx_bytes, network_rx_mb, network_tx_mb,
                uptime, load_1min, load_5min, load_15min
            ) VALUES (
                :timestamp, :cpu_usage, :cpu_cores, :cpu_temperature,
                :memory_total, :memory_used, :memory_available, :memory_usage,
                :disk_total, :disk_used, :disk_usage,
                :network_rx_bytes, :network_tx_bytes, :network_rx_mb, :network_tx_mb,
                :uptime, :load_1min, :load_5min, :load_15min
            )";
            
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                'timestamp' => $metrics['timestamp'],
                'cpu_usage' => $metrics['cpu']['usage'],
                'cpu_cores' => $metrics['cpu']['cores'],
                'cpu_temperature' => $metrics['cpu']['temperature'],
                'memory_total' => $metrics['memory']['total'],
                'memory_used' => $metrics['memory']['used'],
                'memory_available' => $metrics['memory']['available'],
                'memory_usage' => $metrics['memory']['usage'],
                'disk_total' => $metrics['disk']['total'],
                'disk_used' => $metrics['disk']['used'],
                'disk_usage' => $metrics['disk']['usage'],
                'network_rx_bytes' => $metrics['network']['rx_bytes'],
                'network_tx_bytes' => $metrics['network']['tx_bytes'],
                'network_rx_mb' => $metrics['network']['rx_mb'],
                'network_tx_mb' => $metrics['network']['tx_mb'],
                'uptime' => $metrics['system']['uptime'],
                'load_1min' => $metrics['system']['load_1min'],
                'load_5min' => $metrics['system']['load_5min'],
                'load_15min' => $metrics['system']['load_15min']
            ]);
            
        } catch (PDOException $e) {
            throw new \Exception("Erro ao salvar métricas: " . $e->getMessage());
        }
    }
    
    public function getLatestMetrics(): ?array {
        try {
            $sql = "SELECT * FROM system_metrics ORDER BY timestamp DESC LIMIT 1";
            $stmt = $this->pdo->query($sql);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getMetricsHistory(int $limit = 100): array {
        try {
            $sql = "SELECT * FROM system_metrics ORDER BY timestamp DESC LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function cleanupOldMetrics(int $daysToKeep = 7): int {
        try {
            $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
            $sql = "DELETE FROM system_metrics WHERE timestamp < :cutoff";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['cutoff' => $cutoffTime]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            return 0;
        }
    }
}
