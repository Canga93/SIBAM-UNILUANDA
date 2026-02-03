<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

// Conexão com banco
$database = new Database();
$db = $database->getConnection();

// Diretório de uploads
$uploadDir = __DIR__ . '/../assets/uploads/monografias/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true); // Cria a pasta se não existir
}

// Processar cadastro via modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_monografia'])) {
    $titulo = sanitizeInput($_POST['titulo']);
    $resumo = sanitizeInput($_POST['resumo']);
    $area = sanitizeInput($_POST['area']);
    $autor_id = $_SESSION['user_id'] ?? 1; // Ajuste conforme login
    $arquivo = null;
    $erro = '';

    // Upload do arquivo
    if (!empty($_FILES['arquivo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $novoNome = uniqid('monografia_') . '.' . $ext;
        $destino = $uploadDir . $novoNome;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
            $arquivo = $novoNome; // Só o nome no banco
        } else {
            $erro = "Erro ao fazer upload do arquivo.";
        }
    } else {
        $erro = "Selecione um arquivo PDF para enviar.";
    }

    // Salvar no banco
    if (empty($erro) && !empty($arquivo)) {
        $sql = "INSERT INTO monografias (titulo, resumo, area, autor_id, arquivo, status, data_submissao)
                VALUES (:titulo, :resumo, :area, :autor_id, :arquivo, 'pendente', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':resumo' => $resumo,
            ':area' => $area,
            ':autor_id' => $autor_id,
            ':arquivo' => $arquivo
        ]);
        header("Location: monografias.php?success=" . urlencode("Monografia adicionada com sucesso!"));
        exit;
    } else {
        header("Location: monografias.php?error=" . urlencode($erro ?: "Erro ao salvar monografia."));
        exit;
    }
}

// Ações (aprovar, rejeitar, excluir)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $monografia_id = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'approve':
            $query = "UPDATE monografias SET status = 'aprovado' WHERE id = :id";
            $msg = "Monografia aprovada!";
            break;
        case 'reject':
            $query = "UPDATE monografias SET status = 'rejeitado' WHERE id = :id";
            $msg = "Monografia rejeitada!";
            break;
        case 'delete':
            $fileQuery = "SELECT arquivo FROM monografias WHERE id = :id";
            $fileStmt = $db->prepare($fileQuery);
            $fileStmt->bindParam(':id', $monografia_id);
            $fileStmt->execute();
            $monografia = $fileStmt->fetch(PDO::FETCH_ASSOC);

            if ($monografia && file_exists($uploadDir . $monografia['arquivo'])) {
                unlink($uploadDir . $monografia['arquivo']);
            }

            $query = "DELETE FROM monografias WHERE id = :id";
            $msg = "Monografia excluída!";
            break;
        default:
            header("Location: monografias.php");
            exit();
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $monografia_id);
    if ($stmt->execute()) {
        header("Location: monografias.php?success=" . urlencode($msg));
        exit();
    } else {
        header("Location: monografias.php?error=Erro ao processar a ação");
        exit();
    }
}

// Paginação e filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$query = "SELECT m.*, u.nome AS autor, u.email AS autor_email 
          FROM monografias m 
          LEFT JOIN usuarios u ON m.autor_id = u.id 
          WHERE 1=1";
$params = [];

if (!empty($status) && $status !== 'all') {
    $query .= " AND m.status = :status";
    $params[':status'] = $status;
}
if (!empty($search)) {
    $query .= " AND (m.titulo LIKE :search OR m.resumo LIKE :search OR u.nome LIKE :search)";
    $params[':search'] = "%$search%";
}

// Contar total
$countQuery = "SELECT COUNT(*) AS total FROM ($query) AS t";
$countStmt = $db->prepare($countQuery);
foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Paginação
$query .= " ORDER BY m.data_submissao DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

$stmt = $db->prepare($query);
foreach ($params as $k => $v) {
    $type = ($k === ':limit' || $k === ':offset') ? PDO::PARAM_INT : PDO::PARAM_STR;
    $stmt->bindValue($k, $v, $type);
}
$stmt->execute();
$monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$statsQuery = "SELECT status, COUNT(*) AS count FROM monografias GROUP BY status";
$stats = $db->query($statsQuery)->fetchAll(PDO::FETCH_ASSOC);
$statusCounts = ['pendente'=>0,'aprovado'=>0,'rejeitado'=>0];
foreach ($stats as $s) $statusCounts[$s['status']] = $s['count'];
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include '../includes/head.php'; ?>
    <title>Gestão de Monografias</title>
</head>
<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --light-color: #ecf0f1;
        --dark-color: #2c3e50;
        --gold-color: #f1c40f;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        color: var(--dark-color);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .navbar {
        background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 0.8rem 1rem;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--gold-color) !important;
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        color: var(--light-color) !important;
        transform: scale(1.02);
    }
    
    .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        margin: 0 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover {
        color: white !important;
        background-color: rgba(107, 101, 12, 0.2);
        transform: translateY(-2px);
    }
    
    .nav-link i {
        margin-right: 8px;
    }
    
    .hero-section {
        background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 5rem 0;
        margin-bottom: 3rem;
    }
    
    .hero-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .hero-lead {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }
    
    .btn-hero {
        padding: 0.8rem 2rem;
        font-weight: 600;
        border-radius: 50px;
        margin: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .feature-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }
    
    footer {
        background: var(--dark-color);
        color: var(--gold-color) !important;
        margin-top: auto;
    }
    
    .footer-links a {
        color: var(--gold-color) !important;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
        color: var(--light-color) !important;
        padding-left: 5px;
    }
    
    .social-icon {
        color: var(--gold-color) !important;
        font-size: 1.5rem;
        margin: 0 10px;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        color: var(--light-color) !important;
        transform: scale(1.2);
    }
    
    .user-avatar {
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    
    .user-avatar:hover {
        transform: scale(1.1);
        border-color: var(--gold-color);
    }
    
    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .dropdown-item {
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: var(--secondary-color);
        color: white;
        padding-left: 2rem;
    }
    
    .animate-delay-1 {
        animation-delay: 0.2s;
    }
    
    .animate-delay-2 {
        animation-delay: 0.4s;
    }
    
    .animate-delay-3 {
        animation-delay: 0.6s;
    }
</style>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 ms-auto">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Gestão de Monografias</h2>
                    <div>
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalMonografia">
                            <i class="fas fa-plus me-2"></i>Adicionar Monografia
                        </button>
                        <a href="../monografias.php" class="btn btn-outline-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i>Ver Site Público
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-4"><div class="card bg-warning text-dark"><div class="card-body"><h5><?php echo $statusCounts['pendente']; ?></h5>Pendentes</div></div></div>
                    <div class="col-md-4"><div class="card bg-success text-white"><div class="card-body"><h5><?php echo $statusCounts['aprovado']; ?></h5>Aprovadas</div></div></div>
                    <div class="col-md-4"><div class="card bg-danger text-white"><div class="card-body"><h5><?php echo $statusCounts['rejeitado']; ?></h5>Rejeitadas</div></div></div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">Filtros</div>
                    <div class="card-body">
                        <form class="row g-3">
                            <div class="col-md-5"><input class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buscar..."></div>
                            <div class="col-md-4">
                                <select class="form-select" name="status">
                                    <option value="all">Todos</option>
                                    <option value="pendente" <?php if($status=='pendente') echo 'selected';?>>Pendentes</option>
                                    <option value="aprovado" <?php if($status=='aprovado') echo 'selected';?>>Aprovadas</option>
                                    <option value="rejeitado" <?php if($status=='rejeitado') echo 'selected';?>>Rejeitadas</option>
                                </select>
                            </div>
                            <div class="col-md-3"><button class="btn btn-primary w-100">Filtrar</button></div>
                        </form>
                    </div>
                </div>

                <!-- Lista -->
                <div class="card">
                    <div class="card-header">Lista</div>
                    <div class="card-body">
                        <?php if ($monografias): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Título</th><th>Autor</th><th>Data</th><th>Status</th><th>Ações</th></tr></thead>
                                <tbody>
                                <?php foreach ($monografias as $m): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($m['titulo']); ?><br><small><?php echo htmlspecialchars($m['area']); ?></small></td>
                                    <td><?php echo htmlspecialchars($m['autor']); ?><br><small><?php echo htmlspecialchars($m['autor_email']); ?></small></td>
                                    <td><?php echo formatDate($m['data_submissao']); ?></td>
                                    <td><span class="badge bg-<?php echo $m['status']=='aprovado'?'success':($m['status']=='rejeitado'?'danger':'warning'); ?>"><?php echo ucfirst($m['status']); ?></span></td>
                                    <td>
                                        <div class="btn-group">
                                            <!-- Editar Monografia -->
                                            <a href="editar_monografia.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-info" title="Editar monografia"><i class="fas fa-edit"></i></a>
                                            <a href="../detalhes_monografia.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                            <?php if($m['status']=='pendente'): ?>
                                            <a href="?action=approve&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></a>
                                            <a href="?action=reject&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-times"></i></a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Excluir?')"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-center">Nenhuma monografia encontrada</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar -->
<div class="modal fade" id="modalMonografia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Adicionar Monografia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <div class="mb-3"><label>Título</label><input name="titulo" class="form-control" required></div>
            <div class="mb-3"><label>Resumo</label><textarea name="resumo" class="form-control" rows="4" required></textarea></div>
            <div class="mb-3"><label>Área</label><input name="area" class="form-control" required></div>
            <div class="mb-3"><label>Arquivo PDF</label><input type="file" name="arquivo" class="form-control" accept="application/pdf" required></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" name="adicionar_monografia" class="btn btn-success">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>


