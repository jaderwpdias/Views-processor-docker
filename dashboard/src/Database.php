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
    
    public function getMetricsForChart(int $limit = 50): array {
        try {
            $sql = "SELECT 
                        timestamp, 
                        cpu_usage, 
                        memory_usage, 
                        disk_usage,
                        network_rx_mb,
                        network_tx_mb
                    FROM system_metrics 
                    ORDER BY timestamp DESC 
                    LIMIT :limit";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return array_reverse($stmt->fetchAll()); // Reverter para ordem cronológica
        } catch (PDOException $e) {
            return [];
        }
    }
}
