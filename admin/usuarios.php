<?php
require_once '../includes/config.php';
require_once '../includes/head.php';


redirectIfNotAdmin();

// Variáveis de controle
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Se for adicionar ou editar
if (in_array($action, ['add', 'edit'])) {
    $usuario = [
        'nome' => '',
        'email' => '',
        'tipo' => 'estudante'
    ];

    // Buscar dados para edição
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$usuario) {
            header("Location: usuarios.php?error=Usuário não encontrado");
            exit();
        }
    }

    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = sanitizeInput($_POST['nome']);
        $email = sanitizeInput($_POST['email']);
        $tipo = sanitizeInput($_POST['tipo']);
        $senha = !empty($_POST['senha']) ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null;

        if ($action === 'edit' && $id > 0) {
            if ($senha) {
                // Se tem senha, atualizar com senha
                $query = "UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo, password = :password WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $senha);
            } else {
                // Sem senha, não atualizar o campo password
                $query = "UPDATE usuarios SET nome = :nome, email = :email, tipo = :tipo WHERE id = :id";
                $stmt = $db->prepare($query);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // Para adicionar novo usuário, senha é obrigatória
            if (!$senha) {
                $erro = "A senha é obrigatória para novo usuário!";
            } else {
                $query = "INSERT INTO usuarios (nome, email, tipo, password, data_criacao) 
                          VALUES (:nome, :email, :tipo, :password, NOW())";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', $senha);
            }
        }

        if (!isset($erro)) {
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':tipo', $tipo);
            
            if ($stmt->execute()) {
                header("Location: usuarios.php?success=Usuário salvo com sucesso!");
                exit();
            } else {
                $erro = "Erro ao salvar usuário";
            }
        }
    }

    ?>
    <!DOCTYPE html>
    <html lang="pt-pt">
    <head>
        <title><?php echo $action === 'edit' ? 'Editar Usuário' : 'Novo Usuário'; ?></title>
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
        background: linear-gradient(rgba(28, 39, 50, 0.9), rgba(39, 55, 72, 0.9)), url('assets/images/ipgest.png');
        background-size: cover;
        background-position: center;
        color: #fff;
        padding: 5rem 0;
        margin-bottom: 5rem;
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
    <div class="container py-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><?php echo $action === 'edit' ? 'Editar Usuário' : 'Novo Usuário'; ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required 
                               value="<?php echo htmlspecialchars($usuario['nome']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?php echo htmlspecialchars($usuario['email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control"
                               placeholder="<?php echo $action === 'edit' ? 'Deixe em branco para não alterar' : 'Senha obrigatória para novo usuário'; ?>"
                               <?php echo $action !== 'edit' ? 'required' : ''; ?>>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="admin" <?php echo $usuario['tipo']=='admin'?'selected':''; ?>>Administrador</option>
                            <option value="professor" <?php echo $usuario['tipo']=='professor'?'selected':''; ?>>Professor</option>
                            <option value="estudante" <?php echo $usuario['tipo']=='estudante'?'selected':''; ?>>Estudante</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Salvar</button>
                    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit();
}

// Se não for add/edit, mostra a lista de usuários
// --- (Mantendo seu código original abaixo com os ajustes) ---

// Processar ações (delete/promote/demote)
if (isset($_GET['action']) && isset($_GET['id']) && !in_array($_GET['action'], ['add','edit'])) {
    $usuario_id = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'promote':
            $query = "UPDATE usuarios SET tipo = 'admin' WHERE id = :id";
            $message = "Usuário promovido a administrador!";
            break;
        case 'demote':
            $query = "UPDATE usuarios SET tipo = 'estudante' WHERE id = :id";
            $message = "Usuário rebaixado a estudante!";
            break;
        case 'delete':
            if ($usuario_id == $_SESSION['user_id']) {
                header("Location: usuarios.php?error=Não é possível excluir sua própria conta");
                exit();
            }
            $query = "DELETE FROM usuarios WHERE id = :id";
            $message = "Usuário excluído com sucesso!";
            break;
        default:
            header("Location: usuarios.php");
            exit();
    }
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuario_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        header("Location: usuarios.php?success=" . urlencode($message));
        exit();
    } else {
        header("Location: usuarios.php?error=Erro ao processar a solicitação");
        exit();
    }
}

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtros
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Construir query
$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];
if (!empty($tipo) && $tipo !== 'all') {
    $query .= " AND tipo = :tipo";
    $params[':tipo'] = $tipo;
}
if (!empty($search)) {
    $query .= " AND (nome LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

// Contar total de registros
$countQuery = "SELECT COUNT(*) as total FROM ($query) as total_query";
$countStmt = $db->prepare($countQuery);
foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Adicionar ordenação e paginação
$query .= " ORDER BY data_criacao DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $limit;
$params[':offset'] = $offset;

// Executar query
$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    if ($key === ':limit' || $key === ':offset') {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value);
    }
}
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$statsQuery = "SELECT tipo, COUNT(*) as count FROM usuarios GROUP BY tipo";
$statsStmt = $db->query($statsQuery);
$stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
$tipoCounts = ['admin'=>0,'estudante'=>0,'professor'=>0];
foreach ($stats as $stat) { $tipoCounts[$stat['tipo']] = $stat['count']; }
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <title>Gestão de Usuários</title>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-9 col-lg-10 ms-auto">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Gestão de Usuários</h2>
                    <a href="?action=add" class="btn btn-success"><i class="fas fa-user-plus me-2"></i>Adicionar Usuário</a>
                </div>
                <!-- Alertas -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <!-- Estatísticas -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3"><div class="card bg-primary text-white"><div class="card-body"><h5><?php echo $tipoCounts['admin']; ?></h5><p>Administradores</p></div></div></div>
                    <div class="col-md-4 mb-3"><div class="card bg-success text-white"><div class="card-body"><h5><?php echo $tipoCounts['estudante']; ?></h5><p>Estudantes</p></div></div></div>
                    <div class="col-md-4 mb-3"><div class="card bg-info text-white"><div class="card-body"><h5><?php echo $tipoCounts['professor']; ?></h5><p>Professores</p></div></div></div>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header bg-light"><h5><i class="fas fa-filter me-2"></i>Filtros</h5></div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5"><input type="text" class="form-control" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>"></div>
                            <div class="col-md-4">
                                <select class="form-select" name="tipo">
                                    <option value="all" <?php echo $tipo==='all'?'selected':''; ?>>Todos</option>
                                    <option value="admin" <?php echo $tipo==='admin'?'selected':''; ?>>Administradores</option>
                                    <option value="estudante" <?php echo $tipo==='estudante'?'selected':''; ?>>Estudantes</option>
                                    <option value="professor" <?php echo $tipo==='professor'?'selected':''; ?>>Professores</option>
                                </select>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Filtrar</button></div>
                        </form>
                    </div>
                </div>

                <!-- Lista -->
                <div class="card">
                    <div class="card-header bg-light"><h5>Lista de Usuários</h5></div>
                    <div class="card-body">
                        <?php if (count($usuarios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Usuário</th><th>Email</th><th>Tipo</th><th>Data</th><th>Ações</th></tr></thead>
                                <tbody>
                                <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($usuario['nome']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><span class="badge bg-<?php echo $usuario['tipo']=='admin'?'primary':($usuario['tipo']=='professor'?'info':'success'); ?>"><?php echo ucfirst($usuario['tipo']); ?></span></td>
                                    <td><?php echo formatDate($usuario['data_criacao']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?action=edit&id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-info" title="Editar"><i class="fas fa-edit"></i></a>
                                            <?php if ($usuario['tipo']!=='admin' && $usuario['id']!=$_SESSION['user_id']): ?>
                                                <a href="?action=promote&id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-primary" title="Promover"><i class="fas fa-user-shield"></i></a>
                                            <?php endif; ?>
                                            <?php if ($usuario['tipo']==='admin' && $usuario['id']!=$_SESSION['user_id']): ?>
                                                <a href="?action=demote&id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-warning" title="Rebaixar"><i class="fas fa-user-graduate"></i></a>
                                            <?php endif; ?>
                                            <?php if ($usuario['id']!=$_SESSION['user_id']): ?>
                                                <a href="?action=delete&id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-danger" title="Excluir" onclick="return confirm('Tem certeza?')"><i class="fas fa-trash"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5"><i class="fas fa-users fa-3x text-muted mb-3"></i><h4>Nenhum usuário encontrado</h4></div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="mt-4"><?php echo generatePagination($page, $totalPages, "usuarios.php?search=$search&tipo=$tipo"); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>