<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IoT\MetricsCollector;
use IoT\Database;

class IoTCollector {
    private $collector;
    private $db;
    private $redis;
    
    public function __construct() {
        $this->db = new Database();
        $this->collector = new MetricsCollector();
        $this->redis = new Predis\Client([
            'host' => getenv('REDIS_HOST') ?: 'iot-redis',
            'port' => 6379
        ]);
    }
    
    public function run() {
        echo "🚀 Iniciando coletor IoT...\n";
        
        while (true) {
            try {
                // Coletar métricas
                $metrics = $this->collector->collectAll();
                
                // Salvar no banco
                $this->db->saveMetrics($metrics);
                
                // Publicar no Redis para dashboard em tempo real
                $this->redis->publish('iot_metrics', json_encode($metrics));
                
                // Limpeza automática a cada 100 coletações (aproximadamente 8 minutos)
                static $cleanupCounter = 0;
                $cleanupCounter++;
                if ($cleanupCounter >= 100) {
                    $this->db->cleanupOldMetrics(7); // Manter 7 dias
                    $cleanupCounter = 0;
                }
                
                echo "📊 Métricas coletadas: " . date('Y-m-d H:i:s') . "\n";
                echo "   CPU: {$metrics['cpu']['usage']}% | ";
                echo "RAM: {$metrics['memory']['usage']}% | ";
                echo "Disco: {$metrics['disk']['usage']}% | ";
                echo "Rede: {$metrics['network']['rx_mb']}MB/s ↓ {$metrics['network']['tx_mb']}MB/s ↑\n";
                
                // Aguardar 5 segundos antes da próxima coleta
                sleep(5);
                
            } catch (Exception $e) {
                echo "❌ Erro: " . $e->getMessage() . "\n";
                sleep(10);
            }
        }
    }
}

// Executar o coletor
$collector = new IoTCollector();
$collector->run();
