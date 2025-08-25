<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../vendor/autoload.php';

use IoT\Database;

try {
    $db = new Database();
    
    $action = $_GET['action'] ?? 'latest';
    
    switch ($action) {
        case 'latest':
            $metrics = $db->getLatestMetrics();
            echo json_encode([
                'success' => true,
                'data' => $metrics,
                'timestamp' => time()
            ]);
            break;
            
        case 'history':
            $limit = (int) ($_GET['limit'] ?? 100);
            $metrics = $db->getMetricsHistory($limit);
            echo json_encode([
                'success' => true,
                'data' => $metrics,
                'count' => count($metrics)
            ]);
            break;
            
        case 'chart':
            $limit = (int) ($_GET['limit'] ?? 50);
            $metrics = $db->getMetricsForChart($limit);
            echo json_encode([
                'success' => true,
                'data' => $metrics,
                'count' => count($metrics)
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Ação inválida'
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
