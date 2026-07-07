<?php
// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default values for session variables to prevent undefined index errors
$_SESSION['user_foto'] = $_SESSION['user_foto'] ?? 'shine.jpg';
$_SESSION['user_nome'] = $_SESSION['user_nome'] ?? 'Usuário';
$_SESSION['user_tipo'] = $_SESSION['user_tipo'] ?? 'visitante';

// Consultar mensagens não lidas para administradores (se logado e admin)
$unreadMessagesCount = 0;
$recentUnreadMessages = [];
if (isLoggedIn() && isAdmin()) {
    require_once 'config.php'; // Inclui conexão com banco de dados
    $database = new Database();
    $db = $database->getConnection();
    
    try {
        // Contar mensagens não lidas
        $stmt = $db->query("SELECT COUNT(*) FROM mensagens WHERE status = 'nao lida'");
        $unreadMessagesCount = $stmt->fetchColumn();
        
        // Buscar últimas 5 mensagens não lidas para exibir no dropdown
        $stmt = $db->prepare("SELECT id, nome, assunto, data_envio FROM mensagens WHERE status = 'nao lida' ORDER BY data_envio DESC LIMIT 5");
        $stmt->execute();
        $recentUnreadMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Se a tabela não existir, ignora
        error_log("Erro ao buscar mensagens: " . $e->getMessage());
    }
}
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="home.php"><i class="fas fa-book-open me-2"></i>SIBAM-UNILUANDA</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarLinks">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarLinks">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="sobre.php"><i class="fas fa-info-circle"></i> Sobre</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="monografias.php"><i class="fas fa-book"></i> Monografias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contactos.php"><i class="fas fa-envelope"></i> Contactos</a>
                </li>
                <?php if (isLoggedIn()): ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="d-flex align-items-center">
            <?php if (isLoggedIn()): ?>
               
                <!-- Notificações para administradores -->
                <?php if (isAdmin()): ?>
                <div class="dropdown me-3">
                    <button class="btn btn-outline-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadMessagesCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unreadMessagesCount > 9 ? '9+' : $unreadMessagesCount; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
                        <li><h6 class="dropdown-header">Mensagens não lidas (<?php echo $unreadMessagesCount; ?>)</h6></li>
                        <?php if (!empty($recentUnreadMessages)): ?>
                            <?php foreach ($recentUnreadMessages as $msg): ?>
                                <li>
                                    <a class="dropdown-item" href="admin/mensagens.php#mensagem-<?php echo $msg['id']; ?>">
                                        <strong><?php echo htmlspecialchars($msg['nome']); ?></strong>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($msg['assunto']); ?></small>
                                        <small class="text-muted"><?php echo date('d/m H:i', strtotime($msg['data_envio'])); ?></small>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endforeach; ?>
                            <li><a class="dropdown-item text-center" href="admin/mensagens.php">Ver todas as mensagens</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item-text text-muted">Nenhuma mensagem não lida</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Perfil do usuário -->
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="assets/images/avatar/<?php echo htmlspecialchars($_SESSION['user_foto']); ?>" 
                             alt="Foto de Perfil" 
                             class="rounded-circle me-2 user-avatar" 
                             width="40" 
                             height="40"
                             onerror="this.src='assets/images/avatar/default.png'">
                        <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Painel</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <button class="btn btn-light btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-sign-in-alt me-2"></i> Entrar
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Exibir mensagens de alerta -->
<?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
<div class="alert alert-success alert-dismissible fade show mb-0 text-center" role="alert">
    <i class="fas fa-check-circle me-2"></i>Login realizado com sucesso!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php elseif (isset($_GET['login']) && $_GET['login'] === 'error'): ?>
<div class="alert alert-danger alert-dismissible fade show mb-0 text-center" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>Email ou password incorretos!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php elseif (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<div class="alert alert-info alert-dismissible fade show mb-0 text-center" role="alert">
    <i class="fas fa-info-circle me-2"></i>Logout realizado com sucesso!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php elseif (isset($_GET['error']) && $_GET['error'] === 'permission_denied'): ?>
<div class="alert alert-warning alert-dismissible fade show mb-0 text-center" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>Você não tem permissão para acessar esta página!
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
