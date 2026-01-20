<?php
require_once 'includes/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: monografias.php");
    exit();
}

$monografia_id = (int)$_GET['id'];

// Buscar dados da monografia
$query = "SELECT m.*, u.nome as autor, u.email as autor_email 
          FROM monografias m 
          LEFT JOIN usuarios u ON m.autor_id = u.id 
          WHERE m.id = :id AND m.status = 'aprovado'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $monografia_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    header("Location: monografias.php?error=not_found");
    exit();
}

$monografia = $stmt->fetch(PDO::FETCH_ASSOC);

// Formatar palavras-chave
$palavras_chave = !empty($monografia['palavras_chave']) ? 
                  explode(',', $monografia['palavras_chave']) : [];
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title><?php echo htmlspecialchars($monografia['titulo']); ?> - SIBAM UNILUANDA</title>
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
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="home.php">Início</a></li>
                <li class="breadcrumb-item"><a href="monografias.php">Monografias</a></li>
                <li class="breadcrumb-item active">Detalhes</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h1 class="card-title h3 mb-3"><?php echo htmlspecialchars($monografia['titulo']); ?></h1>
                        
                        <div class="d-flex align-items-center mb-4">
                            <img src="assets/images/avatar/<?php echo htmlspecialchars($monografia['foto'] ?? 'default.png'); ?>" 
                                 alt="Autor" 
                                 class="rounded-circle me-3" 
                                 width="50" 
                                 height="50"
                                 onerror="this.src='assets/images/avatar/default.png'">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($monografia['autor']); ?></h6>
                                <small class="text-muted">Autor</small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Resumo</h5>
                            <p class="text-justify"><?php echo nl2br(htmlspecialchars($monografia['resumo'])); ?></p>
                        </div>
                        
                        <?php if (!empty($monografia['orientador'])): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">Orientador</h5>
                            <p><?php echo htmlspecialchars($monografia['orientador']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($palavras_chave)): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">Palavras-chave</h5>
                            <div>
                                <?php foreach ($palavras_chave as $palavra): ?>
                                    <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars(trim($palavra)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informações</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Área</span>
                                <strong><?php echo htmlspecialchars($monografia['area']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Data de Publicação</span>
                                <strong><?php echo formatDate($monografia['data_publicacao']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Data de Submissão</span>
                                <strong><?php echo formatDate($monografia['data_submissao']); ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Status</span>
                                <span class="badge bg-success">Aprovado</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Download</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                        <p class="mb-3">Faça download da monografia completa</p>
                        <a href="<?php echo UPLOAD_PATH . htmlspecialchars($monografia['arquivo']); ?>" 
                           class="btn btn-primary w-100" 
                           download>
                            <i class="fas fa-download me-2"></i>Baixar Monografia
                        </a>
                        <small class="text-muted mt-2 d-block"><?php echo htmlspecialchars($monografia['arquivo']); ?></small>
                    </div>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Contato do Autor</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Entre em contato com o autor para mais informações</p>
                        <a href="mailto:<?php echo htmlspecialchars($monografia['autor_email']); ?>" class="btn btn-outline-primary w-100">
                            <i class="fas fa-envelope me-2"></i>Enviar Email
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>