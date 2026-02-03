<?php
session_start(); // ADICIONE ISTO NO INÍCIO
require_once '../includes/config.php';

// Verificar se é admin
if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$usuarioId = $_SESSION['user_id'] ?? null;

// Conexão
$conn = $db;

// Função para logs do admin
function logAdmin($conn, $adminId, $acao) {
    if (!$adminId) return;
    try {
        $stmt = $conn->prepare("INSERT INTO logs_admin (admin_id, acao, data_hora) VALUES (?, ?, NOW())");
        $stmt->execute([$adminId, $acao]);
    } catch (Exception $e) {
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// Upload de imagens (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    // CORREÇÃO: Substituir FILTER_SANITIZE_STRING (deprecated)
    $titulo = isset($_POST['titulo']) ? htmlspecialchars(trim($_POST['titulo'])) : '';
    $descricao = isset($_POST['descricao']) ? htmlspecialchars(trim($_POST['descricao'])) : '';
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;

    if (!empty($_FILES['imagens']['name'][0]) && $titulo && $categoria_id) {
        $uploadCount = 0;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // CORREÇÃO: Verificar se a pasta existe e criá-la
        $uploadDir = '../assets/uploads/galerias/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        foreach ($_FILES['imagens']['tmp_name'] as $k => $tmp) {
            if ($_FILES['imagens']['error'][$k] !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Validar tipo de arquivo
            $fileType = mime_content_type($tmp);
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['error'] = "Tipo de arquivo não permitido: " . $_FILES['imagens']['name'][$k];
                continue;
            }
            
            // Validar tamanho
            if ($_FILES['imagens']['size'][$k] > $maxSize) {
                $_SESSION['error'] = "Arquivo muito grande: " . $_FILES['imagens']['name'][$k] . " (máx: 5MB)";
                continue;
            }
            
            // Gerar nome único
            $ext = strtolower(pathinfo($_FILES['imagens']['name'][$k], PATHINFO_EXTENSION));
            $nome = uniqid() . '_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $nome;
            
            // CORREÇÃO: Usar caminho absoluto
            if (move_uploaded_file($tmp, $uploadPath)) {
                try {
                    $stmt = $conn->prepare("INSERT INTO galerias (titulo, descricao, imagem, categoria_id, status, usuario_id, data_criacao) 
                                          VALUES (?, ?, ?, ?, 'ativo', ?, NOW())");
                    $stmt->execute([$titulo, $descricao, $nome, $categoria_id, $usuarioId]);
                    $uploadCount++;
                } catch (Exception $e) {
                    // Remove o arquivo se falhar no banco
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                    error_log("Erro ao salvar no banco: " . $e->getMessage());
                    $_SESSION['error'] = "Erro ao salvar no banco de dados: " . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = "Erro ao fazer upload do arquivo: " . $_FILES['imagens']['name'][$k];
            }
        }
        
        if ($uploadCount > 0) {
            $_SESSION['success'] = "$uploadCount imagem(ns) enviada(s) com sucesso!";
            logAdmin($conn, $usuarioId, "Upload de $uploadCount imagens na galeria");
        } elseif (!isset($_SESSION['error'])) {
            $_SESSION['error'] = "Nenhuma imagem foi enviada. Verifique os arquivos.";
        }
        
        // REDIRECIONAMENTO PARA DASHBOARD
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Preencha todos os campos obrigatórios e selecione pelo menos uma imagem.";
        header("Location: dashboard.php");
        exit;
    }
}

// Exclusão de imagens (admin)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    try {
        // Buscar imagem
        $stmt = $conn->prepare("SELECT imagem FROM galerias WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();
        
        if ($img) {
            // Excluir arquivo físico
            $filePath = "../assets/uploads/galerias/$img";
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Excluir do banco
            $stmt = $conn->prepare("DELETE FROM galerias WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success'] = "Imagem excluída com sucesso!";
            logAdmin($conn, $usuarioId, "Exclusão da imagem ID $id");
        } else {
            $_SESSION['error'] = "Imagem não encontrada!";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao excluir imagem: " . $e->getMessage();
    }
    
    // REDIRECIONAMENTO PARA DASHBOARD
    header("Location: dashboard.php");
    exit;
}

// Alterar status (ativo/inativo)
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    try {
        $stmt = $conn->prepare("UPDATE galerias SET status = IF(status='ativo','inativo','ativo') WHERE id = ?");
        $stmt->execute([$id]);
        
        $stmt = $conn->prepare("SELECT status FROM galerias WHERE id = ?");
        $stmt->execute([$id]);
        $newStatus = $stmt->fetchColumn();
        
        $_SESSION['success'] = "Status alterado para: " . $newStatus;
        logAdmin($conn, $usuarioId, "Alterou status da imagem ID $id para $newStatus");
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao alterar status: " . $e->getMessage();
    }
    
    header("Location: dashboard.php");
    exit;
}

// Categorias
try {
    $categorias = $conn->query("SELECT * FROM categorias_galeria ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categorias = [];
    error_log("Erro ao buscar categorias: " . $e->getMessage());
}

// Filtro por categoria e status
$filtro = "WHERE 1=1";
$params = [];
$searchParams = [];

if (!empty($_GET['cat']) && is_numeric($_GET['cat'])) {
    $filtro .= " AND g.categoria_id = ?";
    $params[] = $_GET['cat'];
    $searchParams['cat'] = $_GET['cat'];
}

if (!empty($_GET['status']) && in_array($_GET['status'], ['ativo', 'inativo'])) {
    $filtro .= " AND g.status = ?";
    $params[] = $_GET['status'];
    $searchParams['status'] = $_GET['status'];
}

// Busca por título
if (!empty($_GET['search'])) {
    $searchTerm = "%" . $_GET['search'] . "%";
    $filtro .= " AND (g.titulo LIKE ? OR g.descricao LIKE ?)";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $searchParams['search'] = $_GET['search'];
}

// Paginação
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Buscar galerias
try {
    $query = "SELECT g.*, c.nome AS categoria, u.nome AS autor 
              FROM galerias g 
              LEFT JOIN categorias_galeria c ON g.categoria_id = c.id
              LEFT JOIN usuarios u ON g.usuario_id = u.id
              $filtro
              ORDER BY g.data_criacao DESC
              LIMIT :limit OFFSET :offset";
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    // Bind dos parâmetros
    foreach ($params as $i => $param) {
        $stmt->bindValue($i + 1, $param);
    }
    
    $stmt->execute();
    $galerias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $galerias = [];
    error_log("Erro ao buscar galerias: " . $e->getMessage());
}

// Total de registros
try {
    $countQuery = "SELECT COUNT(*) FROM galerias g $filtro";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);
} catch (Exception $e) {
    $totalRecords = 0;
    $totalPages = 1;
    error_log("Erro ao contar registros: " . $e->getMessage());
}

// Estatísticas para admin
$total = $ativas = $inativas = 0;
try {
    $total = $conn->query("SELECT COUNT(*) FROM galerias")->fetchColumn();
    $ativas = $conn->query("SELECT COUNT(*) FROM galerias WHERE status='ativo'")->fetchColumn();
    $inativas = $conn->query("SELECT COUNT(*) FROM galerias WHERE status='inativo'")->fetchColumn();
} catch (Exception $e) {
    error_log("Erro ao buscar estatísticas: " . $e->getMessage());
}

// Função para paginação (específica para esta página)
function generateGalleryPagination($current, $total, $url, $searchParams = []) {
    if ($total <= 1) return '';
    
    $html = '<nav aria-label="Paginação"><ul class="pagination justify-content-center">';
    
    // Botão anterior
    if ($current > 1) {
        $prevUrl = $url . '?page=' . ($current - 1);
        foreach ($searchParams as $key => $value) {
            $prevUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $prevUrl . '">&laquo; Anterior</a>';
        $html .= '</li>';
    }
    
    // Números das páginas
    $start = max(1, $current - 2);
    $end = min($total, $current + 2);
    
    for($i = $start; $i <= $end; $i++){
        $active = $i == $current ? 'active' : '';
        $pageUrl = $url . '?page=' . $i;
        foreach ($searchParams as $key => $value) {
            $pageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $pageUrl . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Botão próximo
    if ($current < $total) {
        $nextUrl = $url . '?page=' . ($current + 1);
        foreach ($searchParams as $key => $value) {
            $nextUrl .= '&' . urlencode($key) . '=' . urlencode($value);
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $nextUrl . '">Próxima &raquo;</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

// Função formatDate (se não existir em functions.php)
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (empty($date) || $date == '0000-00-00 00:00:00') {
            return 'N/A';
        }
        return date('d/m/Y H:i', strtotime($date));
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Galerias - SIBAM UNILUANDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #2c3e50, #4a6572);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        
        .stats-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            height: 250px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .gallery-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.05);
        }
        
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 15px;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .gallery-meta {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .status-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        
        .action-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .action-buttons {
            opacity: 1;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        
        .upload-area.dragover {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .preview-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #dee2e6;
        }
        
        .page-item.active .page-link {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        
        .btn-purple {
            background-color: #6f42c1;
            color: white;
        }
        
        .btn-purple:hover {
            background-color: #5a32a3;
            color: white;
        }
    </style>
</head>
<body>
    
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-images me-2"></i>Gerenciar Galerias</h1>
                    <p class="mb-0">Total: <?php echo $total; ?> imagens | Ativas: <?php echo $ativas; ?> | Inativas: <?php echo $inativas; ?></p>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left me-1"></i> Voltar ao Dashboard
                    </a>
                    <a href="../index.php" class="btn btn-outline-light">
                        <i class="fas fa-home me-1"></i> Site
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Mensagens -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Estatísticas e Gráfico -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="chart-container">
                    <h5><i class="fas fa-chart-pie me-2"></i>Distribuição de Status</h5>
                    <canvas id="graficoGaleria" height="100"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card bg-primary text-white p-3 h-100">
                    <h5><i class="fas fa-chart-bar me-2"></i>Estatísticas Rápidas</h5>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total de Imagens:</span>
                            <strong><?php echo $total; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Imagens Ativas:</span>
                            <strong class="text-success"><?php echo $ativas; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Imagens Inativas:</span>
                            <strong class="text-warning"><?php echo $inativas; ?></strong>
                        </div>
                        <div class="progress mt-3" style="height: 10px;">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo $total > 0 ? ($ativas/$total*100) : 0; ?>%">
                            </div>
                            <div class="progress-bar bg-warning" 
                                 style="width: <?php echo $total > 0 ? ($inativas/$total*100) : 0; ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Upload -->
        <div class="card mb-4">
            <div class="card-header bg-purple text-white">
                <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload de Novas Imagens</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="upload" value="1">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Título *</label>
                            <input type="text" name="titulo" class="form-control" 
                                   placeholder="Digite o título da galeria" required 
                                   value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoria *</label>
                            <select name="categoria_id" class="form-select" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach($categorias as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" 
                                        <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $c['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição (opcional)</label>
                        <textarea name="descricao" class="form-control" rows="2" 
                                  placeholder="Descrição da galeria"><?php echo isset($_POST['descricao']) ? htmlspecialchars($_POST['descricao']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Selecionar Imagens *</label>
                        <div class="upload-area" id="dropArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Arraste e solte imagens aqui</h5>
                            <p class="text-muted">ou clique para selecionar</p>
                            <input type="file" name="imagens[]" id="fileInput" 
                                   multiple class="d-none" accept="image/*" required>
                            <button type="button" class="btn btn-primary mt-2" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-folder-open me-1"></i> Escolher Arquivos
                            </button>
                        </div>
                        <div id="filePreview" class="image-preview"></div>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i> Formatos: JPG, PNG, GIF, WebP. Máx: 5MB por imagem.
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-upload me-2"></i>Enviar Imagens
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filtros e Busca -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por título..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <select name="cat" class="form-select">
                            <option value="">Todas categorias</option>
                            <?php foreach($categorias as $c): ?>
                                <option value="<?php echo $c['id']; ?>" 
                                    <?php echo (isset($_GET['cat']) && $_GET['cat'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Todos status</option>
                            <option value="ativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                            <option value="inativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Galerias -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-images me-2"></i>Imagens da Galeria 
                    <span class="badge bg-primary ms-2"><?php echo $totalRecords; ?></span>
                </h5>
                <div>
                    <span class="text-muted me-3">
                        Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <?php if(count($galerias) > 0): ?>
                    <div class="row">
                        <?php foreach($galerias as $g): ?>
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="gallery-item">
                                    <?php 
                                    $imagePath = '../assets/uploads/galerias/' . htmlspecialchars($g['imagem']);
                                    $placeholder = '../assets/images/placeholder.jpg';
                                    ?>
                                    <img src="<?php echo $imagePath; ?>" 
                                         alt="<?php echo htmlspecialchars($g['titulo']); ?>" 
                                         onerror="this.onerror=null; this.src='<?php echo $placeholder; ?>'">
                                    
                                    <div class="gallery-overlay">
                                        <h6 class="gallery-title"><?php echo htmlspecialchars($g['titulo']); ?></h6>
                                        <p class="gallery-meta mb-1">
                                            <i class="fas fa-user me-1"></i> 
                                            <?php echo htmlspecialchars($g['autor'] ?? 'Admin'); ?>
                                        </p>
                                        <p class="gallery-meta mb-1">
                                            <i class="fas fa-folder me-1"></i> 
                                            <?php echo htmlspecialchars($g['categoria'] ?? 'Sem categoria'); ?>
                                        </p>
                                        <small>
                                            <i class="fas fa-calendar me-1"></i> 
                                            <?php echo formatDate($g['data_criacao']); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="action-buttons">
                                        <a href="?toggle=<?php echo $g['id']; ?>" 
                                           class="btn btn-sm btn-<?php echo $g['status'] == 'ativo' ? 'warning' : 'success'; ?>"
                                           title="<?php echo $g['status'] == 'ativo' ? 'Desativar' : 'Ativar'; ?>">
                                            <i class="fas fa-<?php echo $g['status'] == 'ativo' ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="?delete=<?php echo $g['id']; ?>" 
                                           class="btn btn-sm btn-danger ms-1"
                                           onclick="return confirm('Tem certeza que deseja excluir esta imagem?')"
                                           title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="position-absolute top-0 start-0 p-2">
                                        <span class="badge status-badge bg-<?php echo $g['status'] == 'ativo' ? 'success' : 'secondary'; ?>">
                                            <?php echo $g['status'] == 'ativo' ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if($totalPages > 1): ?>
                        <div class="mt-4">
                            <?php echo generateGalleryPagination($page, $totalPages, 'dashboard.php', $searchParams); ?>
                            <p class="text-center text-muted mt-2">
                                Mostrando <?php echo count($galerias); ?> de <?php echo $totalRecords; ?> imagens
                            </p>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Nenhuma imagem encontrada</h4>
                        <p class="text-muted">
                            <?php if (!empty($_GET['search']) || !empty($_GET['cat']) || !empty($_GET['status'])): ?>
                                Tente outros filtros de busca.
                            <?php else: ?>
                                Adicione sua primeira imagem usando o formulário acima.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de estatísticas
        var ctx = document.getElementById('graficoGaleria');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Ativas', 'Inativas'],
                    datasets: [{
                        data: [<?php echo $ativas; ?>, <?php echo $inativas; ?>],
                        backgroundColor: ['#28a745', '#ffc107'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.parsed;
                                    let total = <?php echo $total; ?>;
                                    let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Drag and drop para upload
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
        
        if (dropArea && fileInput && filePreview) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropArea.classList.add('dragover');
            }
            
            function unhighlight() {
                dropArea.classList.remove('dragover');
            }
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                updatePreview();
            }
            
            fileInput.addEventListener('change', updatePreview);
            
            function updatePreview() {
                filePreview.innerHTML = '';
                const files = fileInput.files;
                
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!file.type.match('image.*')) continue;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-thumb';
                        img.title = file.name;
                        filePreview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
                
                // Atualizar texto do drop area
                if (files.length > 0) {
                    dropArea.querySelector('h5').textContent = `${files.length} arquivo(s) selecionado(s)`;
                    dropArea.querySelector('p').textContent = 'Clique para alterar';
                }
            }
        }
        
        // Confirmar antes de excluir
        document.querySelectorAll('a[href*="delete"]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Tem certeza que deseja excluir esta imagem? Esta ação não pode ser desfeita.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Auto-submit para filtros
        document.querySelectorAll('select[name="cat"], select[name="status"]').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
        
        // Validação do formulário
        document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('fileInput');
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos uma imagem.');
                return false;
            }
            
            // Verificar tamanho dos arquivos
            let totalSize = 0;
            for (let i = 0; i < fileInput.files.length; i++) {
                totalSize += fileInput.files[i].size;
                if (fileInput.files[i].size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert(`O arquivo "${fileInput.files[i].name}" excede o tamanho máximo de 5MB.`);
                    return false;
                }
            }
            
            if (totalSize > 20 * 1024 * 1024) {
                e.preventDefault();
                alert('O tamanho total dos arquivos excede 20MB. Por favor, reduza o número de arquivos.');
                return false;
            }
        });
    </script>
</body>
</html>