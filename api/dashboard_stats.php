<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Verificar se é uma requisição GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Incluir configuração e autenticação
require_once '../includes/config.php';

// Verificar autenticação (token simples ou sessão)
$authHeader = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

// Verificar se o usuário está autenticado (simplificado para demonstração)
// Em produção, usar um sistema de autenticação mais robusto
if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

// Obter conexão com o banco de dados
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
    exit;
}

// Obter parâmetros da requisição
$statsType = $_GET['type'] ?? 'all';
$refreshCache = $_GET['refresh'] ?? false;

// Cache simples para evitar múltiplas consultas em curto período
$cacheKey = 'dashboard_stats_' . $statsType;
$cacheFile = '../cache/' . $cacheKey . '.json';
$cacheTime = 10; // segundos

// Verificar se existe cache válido
if (!$refreshCache && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

// Dados de resposta
$responseData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'status' => 'success'
];

try {
    switch ($statsType) {
        case 'basic':
            // Estatísticas básicas
            $statsQuery = "SELECT 
                (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                (SELECT COUNT(*) FROM monografias) as total_monografias,
                (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes,
                (SELECT COUNT(*) FROM monografias WHERE status = 'aprovado') as monografias_aprovadas,
                (SELECT COUNT(*) FROM support_tickets WHERE status = 'aberto') as tickets_abertos,
                (SELECT COUNT(*) FROM scheduled_tasks WHERE status = 'pending' AND scheduled_date <= NOW()) as tarefas_pendentes";
            
            $statsStmt = $db->query($statsQuery);
            $responseData['data'] = $statsStmt->fetch(PDO::FETCH_ASSOC);
            break;
            
        case 'realtime':
            // Dados em tempo real (últimos 5 minutos)
            $realtimeQuery = "SELECT 
                (SELECT COUNT(*) FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as logs_recentes,
                (SELECT COUNT(*) FROM monografias WHERE data_submissao >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as novas_monografias,
                (SELECT COUNT(*) FROM usuarios WHERE ultimo_login >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as usuarios_ativos";
            
            $realtimeStmt = $db->query($realtimeQuery);
            $responseData['data'] = $realtimeStmt->fetch(PDO::FETCH_ASSOC);
            $responseData['timeframe'] = 'last_5_minutes';
            break;
            
        case 'system':
            // Informações do sistema (simuladas)
            $responseData['data'] = [
                'cpu' => rand(20, 80),
                'memory' => rand(40, 90),
                'disk' => rand(60, 95),
                'uptime' => rand(100, 500),
                'online_users' => rand(5, 50)
            ];
            break;
            
        case 'notifications':
            // Notificações não lidas
            $notificationsQuery = "SELECT COUNT(*) as total FROM notifications WHERE is_read = 0";
            $notificationsStmt = $db->query($notificationsQuery);
            $responseData['data'] = ['unread_notifications' => $notificationsStmt->fetchColumn()];
            break;
            
        case 'activity':
            // Atividade recente
            $activityQuery = "SELECT 
                sl.action, 
                sl.description, 
                sl.created_at,
                u.nome as usuario_nome
                FROM system_logs sl 
                LEFT JOIN usuarios u ON sl.user_id = u.id 
                ORDER BY sl.created_at DESC 
                LIMIT 10";
            
            $activityStmt = $db->query($activityQuery);
            $responseData['data'] = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            // Todos os dados
            $statsQuery = "SELECT 
                (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                (SELECT COUNT(*) FROM monografias) as total_monografias,
                (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes,
                (SELECT COUNT(*) FROM monografias WHERE status = 'aprovado') as monografias_aprovadas,
                (SELECT COUNT(*) FROM support_tickets WHERE status = 'aberto') as tickets_abertos,
                (SELECT COUNT(*) FROM scheduled_tasks WHERE status = 'pending' AND scheduled_date <= NOW()) as tarefas_pendentes,
                (SELECT COUNT(*) FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as logs_recentes,
                (SELECT COUNT(*) FROM monografias WHERE data_submissao >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as novas_monografias,
                (SELECT COUNT(*) FROM usuarios WHERE ultimo_login >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as usuarios_ativos,
                (SELECT COUNT(*) FROM notifications WHERE is_read = 0) as notificacoes_nao_lidas";
            
            $statsStmt = $db->query($statsQuery);
            $basicStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            // Adicionar dados do sistema simulados
            $responseData['data'] = array_merge($basicStats, [
                'cpu' => rand(20, 80),
                'memory' => rand(40, 90),
                'disk' => rand(60, 95),
                'uptime' => rand(100, 500),
                'online_users' => rand(5, 50),
                'server_time' => date('Y-m-d H:i:s')
            ]);
            break;
    }
    
    // Salvar em cache
    if (!is_dir('../cache')) {
        mkdir('../cache', 0755, true);
    }
    file_put_contents($cacheFile, json_encode($responseData));
    
    echo json_encode($responseData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao obter estatísticas',
        'message' => $e->getMessage()
    ]);
}