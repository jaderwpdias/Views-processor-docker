<?php
/**
 * Script de limpeza automática de dados antigos
 */

require_once __DIR__ . '/vendor/autoload.php';

use IoT\Database;

echo "🧹 Iniciando limpeza de dados antigos...\n";

try {
    $db = new Database();
    
    // Obter configuração de retenção
    $retentionDays = 7; // Padrão
    
    // Limpar dados antigos
    $deletedCount = $db->cleanupOldMetrics($retentionDays);
    
    echo "✅ Limpeza concluída!\n";
    echo "📊 Registros removidos: $deletedCount\n";
    
    // Verificar total atual
    $currentCount = count($db->getMetricsHistory(1000));
    echo "📈 Total de registros atuais: $currentCount\n";
    
} catch (Exception $e) {
    echo "❌ Erro na limpeza: " . $e->getMessage() . "\n";
}
