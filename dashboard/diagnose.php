<?php
/**
 * Script de diagnóstico para o Dashboard IoT
 * Acesse: http://localhost:8080/diagnose.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - IoT Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .status { font-weight: bold; }
        .details { margin-top: 10px; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico do Sistema IoT Dashboard</h1>
    
    <?php
    $tests = [];
    
    // Teste 1: Extensões PHP
    echo "<div class='test info'>";
    echo "<h3>1. Extensões PHP</h3>";
    $required_extensions = ['pdo', 'pdo_mysql', 'json', 'gd'];
    foreach ($required_extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<div class='success'>✓ $ext está instalada</div>";
        } else {
            echo "<div class='error'>✗ $ext NÃO está instalada</div>";
        }
    }
    echo "</div>";
    
    // Teste 2: Conectividade com banco
    echo "<div class='test info'>";
    echo "<h3>2. Conectividade com Banco de Dados</h3>";
    try {
        $host = getenv('DB_HOST') ?: 'iot-db';
        $dbname = getenv('DB_NAME') ?: 'iot_metrics';
        $user = getenv('DB_USER') ?: 'iot_user';
        $pass = getenv('DB_PASS') ?: 'iot_password';
        
        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        echo "<div class='success'>✓ Conectado ao banco de dados</div>";
        
        // Verificar tabela
        $stmt = $pdo->query("SHOW TABLES LIKE 'system_metrics'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✓ Tabela system_metrics existe</div>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM system_metrics");
            $count = $stmt->fetch()['count'];
            echo "<div class='info'>📊 Encontrados $count registros de métricas</div>";
            
            // Última métrica
            $stmt = $pdo->query("SELECT * FROM system_metrics ORDER BY timestamp DESC LIMIT 1");
            $latest = $stmt->fetch();
            if ($latest) {
                $time = date('Y-m-d H:i:s', $latest['timestamp']);
                echo "<div class='info'>🕐 Última métrica: $time</div>";
            }
        } else {
            echo "<div class='error'>✗ Tabela system_metrics não existe</div>";
        }
        
    } catch (PDOException $e) {
        echo "<div class='error'>✗ Erro de conexão: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    echo "</div>";
    
    // Teste 3: Variáveis de ambiente
    echo "<div class='test info'>";
    echo "<h3>3. Variáveis de Ambiente</h3>";
    $env_vars = [
        'DB_HOST' => 'Host do banco',
        'DB_NAME' => 'Nome do banco',
        'DB_USER' => 'Usuário do banco',
        'DB_PASS' => 'Senha do banco'
    ];
    
    foreach ($env_vars as $var => $description) {
        $value = getenv($var);
        if ($value) {
            $display_value = ($var === 'DB_PASS') ? '***' : $value;
            echo "<div class='success'>✓ $description ($var): $display_value</div>";
        } else {
            echo "<div class='warning'>⚠ $description ($var): NÃO definida</div>";
        }
    }
    echo "</div>";
    
    // Teste 4: Arquivos do sistema
    echo "<div class='test info'>";
    echo "<h3>4. Acesso aos Arquivos do Sistema</h3>";
    $system_files = [
        '/proc/stat' => 'CPU stats',
        '/proc/meminfo' => 'Memory info',
        '/proc/uptime' => 'Uptime',
        '/proc/loadavg' => 'Load average',
        '/proc/net/dev' => 'Network stats'
    ];
    
    foreach ($system_files as $file => $description) {
        if (file_exists($file)) {
            echo "<div class='success'>✓ $description ($file) acessível</div>";
        } else {
            echo "<div class='error'>✗ $description ($file) NÃO acessível</div>";
        }
    }
    echo "</div>";
    
    // Teste 5: API Endpoints
    echo "<div class='test info'>";
    echo "<h3>5. Teste de API Endpoints</h3>";
    
    $base_url = 'http://' . $_SERVER['HTTP_HOST'];
    $endpoints = [
        '/api/metrics.php?action=latest' => 'Últimas métricas',
        '/api/metrics.php?action=history&limit=5' => 'Histórico',
        '/api/metrics.php?action=chart&limit=10' => 'Dados do gráfico'
    ];
    
    foreach ($endpoints as $endpoint => $description) {
        $url = $base_url . $endpoint;
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['success'])) {
                echo "<div class='success'>✓ $description: Funcionando</div>";
            } else {
                echo "<div class='warning'>⚠ $description: Resposta inválida</div>";
            }
        } else {
            echo "<div class='error'>✗ $description: Erro de conexão</div>";
        }
    }
    echo "</div>";
    
    // Teste 6: Permissões de arquivo
    echo "<div class='test info'>";
    echo "<h3>6. Permissões de Arquivo</h3>";
    $files_to_check = [
        'index.php' => 'Página principal',
        'api/metrics.php' => 'API de métricas',
        'assets/js/dashboard.js' => 'JavaScript do dashboard'
    ];
    
    foreach ($files_to_check as $file => $description) {
        if (file_exists($file)) {
            if (is_readable($file)) {
                echo "<div class='success'>✓ $description ($file): Legível</div>";
            } else {
                echo "<div class='error'>✗ $description ($file): NÃO legível</div>";
            }
        } else {
            echo "<div class='error'>✗ $description ($file): NÃO existe</div>";
        }
    }
    echo "</div>";
    ?>
    
    <div class='test info'>
        <h3>📋 Resumo</h3>
        <p>Este diagnóstico verifica se todos os componentes do sistema IoT Dashboard estão funcionando corretamente.</p>
        <p><strong>Próximos passos:</strong></p>
        <ul>
            <li>Se todos os testes passaram: <a href="index.php">Acesse o Dashboard</a></li>
            <li>Se há erros: Verifique os logs com <code>./start.sh logs</code></li>
            <li>Para reiniciar: Execute <code>./start.sh restart</code></li>
        </ul>
    </div>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="index.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
            🚀 Ir para o Dashboard
        </a>
    </div>
</body>
</html>
