<?php
require_once '../includes/config.php';

// Este script pode ser executado via cron para manter o cache atualizado
$database = new Database();
$db = $database->getConnection();

// Tipos de estatísticas para cache
$statTypes = ['all', 'basic', 'realtime', 'system', 'notifications', 'activity'];

foreach ($statTypes as $type) {
    $cacheFile = '../cache/dashboard_stats_' . $type . '.json';
    
    // URL para obter os dados
    $url = "http://" . $_SERVER['HTTP_HOST'] . "/api/dashboard_stats.php?type=$type&refresh=true";
    
    // Usar cURL para fazer a requisição
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer admin_token_123' // Token de administração
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Salvar resposta no cache
    if (!is_dir('../cache')) {
        mkdir('../cache', 0755, true);
    }
    
    file_put_contents($cacheFile, $response);
    echo "Cache atualizado para: $type\n";
}

echo "Todos os caches foram atualizados com sucesso.\n";