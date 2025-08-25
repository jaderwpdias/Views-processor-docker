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
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f9fafb;
            --border-radius: 16px;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--dark-color);
            overflow-x: hidden;
        }

        /* Navbar Moderna */
        .navbar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.75rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-nav .btn {
            border-radius: 12px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar-nav .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Cards Modernos */
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .dashboard-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }

        /* Métricas */
        .metric-card {
            text-align: center;
            padding: 2rem 1.5rem;
            position: relative;
        }

        .metric-value {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            line-height: 1;
        }

        .metric-label {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .metric-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            opacity: 0.3;
            color: var(--primary-color);
        }

        /* Progress Bars Modernas */
        .progress-custom {
            height: 12px;
            border-radius: 20px;
            background: #e5e7eb;
            overflow: hidden;
            position: relative;
        }

        .progress-custom .progress-bar {
            border-radius: 20px;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            background: linear-gradient(90deg, var(--success-color), var(--primary-color));
        }

        .progress-custom .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Status Indicators */
        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.75rem;
            position: relative;
        }

        .status-indicator::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 50%;
            background: inherit;
            opacity: 0.3;
            animation: pulse 2s infinite;
        }

        .status-ok { 
            background-color: var(--success-color);
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }
        .status-warning { 
            background-color: var(--warning-color);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
        }
        .status-critical { 
            background-color: var(--danger-color);
            box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 350px;
            margin: 1.5rem 0;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        /* Real-time Indicator */
        .real-time-indicator {
            animation: pulse 2s infinite;
            color: var(--success-color);
        }

        @keyframes pulse {
            0%, 100% { 
                opacity: 1;
                transform: scale(1);
            }
            50% { 
                opacity: 0.7;
                transform: scale(1.1);
            }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .metric-value {
                font-size: 2rem;
            }
            
            .dashboard-card {
                margin-bottom: 1rem;
            }
            
            .chart-container {
                height: 250px;
            }
        }

        /* Animações de entrada */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        /* Loading states */
        .loading {
            position: relative;
            overflow: hidden;
        }

        .loading::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
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
                <div class="dashboard-card metric-card fade-in">
                    <i class="fas fa-microchip metric-icon"></i>
                    <div class="metric-value" id="cpu-usage">
                        <?= $latestMetrics ? number_format($latestMetrics['cpu_usage'], 1) : '0' ?>%
                    </div>
                    <div class="metric-label">CPU</div>
                    <div class="progress-custom mt-3">
                        <div class="progress-bar" id="cpu-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['cpu_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? $latestMetrics['cpu_cores'] : 0 ?> cores | 
                        <?= $latestMetrics ? $latestMetrics['cpu_temperature'] : 0 ?>°C
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card metric-card fade-in">
                    <i class="fas fa-memory metric-icon"></i>
                    <div class="metric-value" id="memory-usage">
                        <?= $latestMetrics ? number_format($latestMetrics['memory_usage'], 1) : '0' ?>%
                    </div>
                    <div class="metric-label">Memória</div>
                    <div class="progress-custom mt-3">
                        <div class="progress-bar" id="memory-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['memory_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? number_format($latestMetrics['memory_used'], 0) : 0 ?> / 
                        <?= $latestMetrics ? number_format($latestMetrics['memory_total'], 0) : 0 ?> MB
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card metric-card fade-in">
                    <i class="fas fa-hdd metric-icon"></i>
                    <div class="metric-value" id="disk-usage">
                        <?= $latestMetrics ? $latestMetrics['disk_usage'] : 0 ?>%
                    </div>
                    <div class="metric-label">Disco</div>
                    <div class="progress-custom mt-3">
                        <div class="progress-bar" id="disk-progress" 
                             style="width: <?= $latestMetrics ? $latestMetrics['disk_usage'] : 0 ?>%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <?= $latestMetrics ? $latestMetrics['disk_used'] : 0 ?> / 
                        <?= $latestMetrics ? $latestMetrics['disk_total'] : 0 ?>
                    </small>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card metric-card fade-in">
                    <i class="fas fa-network-wired metric-icon"></i>
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
