<?php
require_once __DIR__ . '/vendor/autoload.php';

use IoT\Database;

try {
    $db = new Database();
    $latestMetrics = $db->getLatestMetrics();
} catch (Exception $e) {
    $latestMetrics = null;
    error_log("Erro ao conectar com banco de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IoT Dashboard - Monitoramento do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .metric-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .progress-custom {
            height: 8px;
            border-radius: 10px;
            background: #ecf0f1;
        }
        .progress-custom .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-ok { background-color: #27ae60; }
        .status-warning { background-color: #f39c12; }
        .status-critical { background-color: #e74c3c; }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .real-time-indicator {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-microchip me-2"></i>
                IoT Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-circle text-success real-time-indicator me-2"></i>
                    Tempo Real
                </span>
                <a href="test-chart.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-chart-line me-1"></i>
                    Testar Gráfico
                </a>
                <a href="debug-dashboard.html" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-bug me-1"></i>
                    Debug
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Métricas Principais -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="dashboard-card p-4 text-center">
                    <i class="fas fa-microchip fa-3x text-primary mb-3"></i>
                    <div class="metric-value" id="cpu-usage">
                        <?= $latestMetrics ? number_format($latestMetrics['cpu_usage'], 1) : '0' ?>%
                    </div>
                    <div class="metric-label">CPU</div>
                    <div class="progress progress-custom mt-2">
                        <div class="progress-bar bg-primary" id="cpu-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['cpu_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? $latestMetrics['cpu_cores'] : 0 ?> cores | 
                        <?= $latestMetrics ? $latestMetrics['cpu_temperature'] : 0 ?>°C
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card p-4 text-center">
                    <i class="fas fa-memory fa-3x text-success mb-3"></i>
                    <div class="metric-value" id="memory-usage">
                        <?= $latestMetrics ? number_format($latestMetrics['memory_usage'], 1) : '0' ?>%
                    </div>
                    <div class="metric-label">Memória</div>
                    <div class="progress progress-custom mt-2">
                        <div class="progress-bar bg-success" id="memory-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['memory_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? number_format($latestMetrics['memory_used'], 0) : 0 ?> / 
                        <?= $latestMetrics ? number_format($latestMetrics['memory_total'], 0) : 0 ?> MB
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card p-4 text-center">
                    <i class="fas fa-hdd fa-3x text-warning mb-3"></i>
                    <div class="metric-value" id="disk-usage">
                        <?= $latestMetrics ? $latestMetrics['disk_usage'] : 0 ?>%
                    </div>
                    <div class="metric-label">Disco</div>
                    <div class="progress progress-custom mt-2">
                        <div class="progress-bar bg-warning" id="disk-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['disk_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? $latestMetrics['disk_used'] : 0 ?> / 
                        <?= $latestMetrics ? $latestMetrics['disk_total'] : 0 ?>
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card p-4 text-center">
                    <i class="fas fa-network-wired fa-3x text-info mb-3"></i>
                    <div class="metric-value" id="network-usage">
                        <?= $latestMetrics ? number_format($latestMetrics['network_rx_mb'], 1) : '0' ?>
                    </div>
                    <div class="metric-label">Rede (MB/s)</div>
                    <div class="mt-2">
                        <small class="text-muted">
                            ↓ <?= $latestMetrics ? number_format($latestMetrics['network_rx_mb'], 1) : '0' ?> MB/s<br>
                            ↑ <?= $latestMetrics ? number_format($latestMetrics['network_tx_mb'], 1) : '0' ?> MB/s
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-chart-line me-2"></i>
                        Histórico de Métricas
                    </h5>
                    <div class="chart-container">
                        <canvas id="metricsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="dashboard-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações do Sistema
                    </h5>
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Uptime:</span>
                                <span id="uptime"><?= $latestMetrics ? gmdate('H:i:s', $latestMetrics['uptime']) : '00:00:00' ?></span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Load 1min:</span>
                                <span id="load-1min"><?= $latestMetrics ? number_format($latestMetrics['load_1min'], 2) : '0.00' ?></span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Load 5min:</span>
                                <span id="load-5min"><?= $latestMetrics ? number_format($latestMetrics['load_5min'], 2) : '0.00' ?></span>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Load 15min:</span>
                                <span id="load-15min"><?= $latestMetrics ? number_format($latestMetrics['load_15min'], 2) : '0.00' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card p-4">
                    <h5 class="mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Status do Sistema
                    </h5>
                    <div id="system-status">
                        <div class="mb-2">
                            <span class="status-indicator status-ok"></span>
                            <span>CPU: Normal</span>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-ok"></span>
                            <span>Memória: Normal</span>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-ok"></span>
                            <span>Disco: Normal</span>
                        </div>
                        <div class="mb-2">
                            <span class="status-indicator status-ok"></span>
                            <span>Rede: Normal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
    
    <!-- Auto-reload da página -->
    <script>
        // Auto-reload a cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Mostrar contador de reload
        let countdown = 30;
        const countdownElement = document.createElement('div');
        countdownElement.style.cssText = 'position: fixed; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 5px; font-size: 12px; z-index: 9999;';
        document.body.appendChild(countdownElement);
        
        const countdownInterval = setInterval(function() {
            countdown--;
            countdownElement.textContent = `🔄 Recarregando em ${countdown}s`;
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                countdownElement.textContent = '🔄 Recarregando...';
            }
        }, 1000);
    </script>
</body>
</html>
