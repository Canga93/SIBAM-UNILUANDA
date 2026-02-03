<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Habilitar CORS para preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar autenticação para endpoints protegidos
function require_auth() {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    
    if (empty($auth_header) {
        http_response_code(401);
        echo json_encode(['error' => 'Token de autorização necessário']);
        exit();
    }
    
    // Verificar token (implementação básica)
    $token = str_replace('Bearer ', '', $auth_header);
    
    // Em um sistema real, validaríamos o token JWT ou similar
    if ($token !== 'seu_token_secreto_aqui') {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit();
    }
}

// Roteamento básico
$request_uri = str_replace('/api/', '', $_SERVER['REQUEST_URI']);
$uri_parts = explode('?', $request_uri);
$path = $uri_parts[0];
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($path) {
        case 'monografias':
            if ($method == 'GET') {
                // Listar monografias
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 10;
                $search = $_GET['search'] ?? '';
                
                $query = "SELECT m.*, u.nome as autor 
                          FROM monografias m 
                          LEFT JOIN usuarios u ON m.autor_id = u.id 
                          WHERE m.status = 'aprovado'";
                
                $params = [];
                
                if (!empty($search)) {
                    $query .= " AND (m.titulo LIKE :search OR m.resumo LIKE :search OR u.nome LIKE :search)";
                    $params[':search'] = "%$search%";
                }
                
                $query .= " ORDER BY m.data_publicacao DESC LIMIT :limit OFFSET :offset";
                $params[':limit'] = (int)$limit;
                $params[':offset'] = ((int)$page - 1) * (int)$limit;
                
                $stmt = $db->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
                }
                $stmt->execute();
                
                $monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $monografias,
                    'pagination' => [
                        'page' => (int)$page,
                        'limit' => (int)$limit
                    ]
                ]);
            }
            break;
            
        case 'monografias/{id}':
            if ($method == 'GET') {
                // Obter monografia específica
                $id = $_GET['id'] ?? null;
                
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID da monografia é necessário']);
                    break;
                }
                
                $query = "SELECT m.*, u.nome as autor, u.email as autor_email 
                          FROM monografias m 
                          LEFT JOIN usuarios u ON m.autor_id = u.id 
                          WHERE m.id = :id AND m.status = 'aprovado'";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                
                $monografia = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($monografia) {
                    echo json_encode([
                        'success' => true,
                        'data' => $monografia
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Monografia não encontrada']);
                }
            }
            break;
            
        case 'estatisticas':
            if ($method == 'GET') {
                // Estatísticas públicas
                $statsQuery = "SELECT 
                    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                    (SELECT COUNT(*) FROM monografias WHERE status = 'aprovado') as total_monografias,
                    (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes";
                
                $statsStmt = $db->query($statsQuery);
                $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $stats
                ]);
            }
            break;
            
        case 'admin/monografias':
            // Endpoint protegido
            require_auth();
            
            if ($method == 'GET') {
                // Listar todas as monografias (admin)
                $query = "SELECT m.*, u.nome as autor 
                          FROM monografias m 
                          LEFT JOIN usuarios u ON m.autor_id = u.id 
                          ORDER BY m.data_submissao DESC";
                
                $stmt = $db->query($query);
                $monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $monografias
                ]);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint não encontrado']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>