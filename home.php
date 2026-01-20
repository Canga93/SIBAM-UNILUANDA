<?php 
// Start session and check login
session_start();

// Set default values for session variables to prevent undefined index errors
$_SESSION['user_foto'] = $_SESSION['user_foto'] ?? 'User.jpg';
$_SESSION['user_nome'] = $_SESSION['user_nome'] ?? 'Usuário';
$_SESSION['user_tipo'] = $_SESSION['user_tipo'] ?? 'visitante';


require_once __DIR__ . '/includes/config.php';

$database = new Database();
$conn = $database->getConnection();

// Inicializa variável
$totalMonografias = 0;

// Prepara e executa a query usando PDO
try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM monografias WHERE status = 'aprovado'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalMonografias = (int)$row['total'];
} catch(PDOException $e) {
    echo "Erro na consulta: " . $e->getMessage();
}

$message = '';
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBAM - UNILUANDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
<body >

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
            </ul>
        </div>
        <div class="d-flex">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="assets/images/avatar/<?php echo htmlspecialchars($_SESSION['user_foto']); ?>" 
                             alt="Foto de Perfil" 
                             class="rounded-circle me-2 user-avatar" 
                             width="32" 
                             height="32"
                             onerror="this.src='assets/images/avatar/default.png'">
                        <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i> Perfil</a></li>
                        <?php if ($_SESSION['user_tipo'] === 'admin'): ?>
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

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="hero-title animate__animated animate__fadeInDown"><i class="fas fa-graduation-cap me-3"></i>Sistema de Busca Avançada de Monografias</h1>
        <p class="hero-lead animate__animated animate__fadeIn animate__delay-1s">Acesse, envie e gerencie monografias com facilidade. Plataforma acadêmica da UNILUANDA.</p>
        
        <div class="mt-4 animate__animated animate__fadeIn animate__delay-2s">
            <a href="monografias.php" class="btn btn-primary btn-hero mx-2">
                <i class="fas fa-search me-2"></i> Explorar Monografias
            </a>
            
            <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_tipo'] === 'admin'): ?>
                <button type="button" class="btn btn-outline-light btn-hero mx-2" data-bs-toggle="modal" data-bs-target="#registerModal">
                    <i class="fas fa-user-plus me-2"></i> Registar-se
                </button>
            <?php else: ?>
                <button class="btn btn-outline-light btn-hero mx-2" disabled title="Funcionalidade disponível apenas para visitantes e administradores">
                    <i class="fas fa-user-lock me-2"></i> Registar-se
                </button>
            <?php endif; ?>

            
            <a href="galerias.php" class="btn btn-warning btn-hero mx-2">
                <i class="fas fa-images me-2"></i> Explorar Galerias
            </a>
        </div>
    </div>
</section>

<<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-1">
                    <div class="card-body p-4">
                        <i class="fas fa-upload feature-icon"></i>
                        <h3 class="mb-3">Submissão</h3>
                        <p class="mb-4">Envie sua monografia de forma simples e rápida.</p>
                        
                        <?php 
                        // Verifica se o usuário está logado como estudante ou admin
                        $canSubmit = isset($_SESSION['logged_in']) && 
                                    $_SESSION['logged_in'] && 
                                    ($_SESSION['user_tipo'] === 'estudante' || $_SESSION['user_tipo'] === 'admin');
                        ?>
                        
                        <?php if ($canSubmit): ?>
                           <!-- Botão ATIVO apenas para estudantes e admins logados -->
                           <button 
                                type="button"
                                class="btn btn-outline-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalSubmissao">
                                Saiba mais
                            </button>
                        <?php else: ?>
                            <!-- Botão BLOQUEADO para visitantes e outros usuários -->
                            <button class="btn btn-outline-secondary" disabled 
                                    title="Faça login como estudante ou administrador para acessar">
                                <i class="fas fa-lock me-2"></i> Saiba mais
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-2">
                    <div class="card-body p-4">
                        <i class="fas fa-search feature-icon"></i>
                        <h3 class="mb-3">Consulta</h3>
                        <p class="mb-4">Acesse o acervo completo de monografias disponíveis.</p>
                        <?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['user_tipo'] === 'visitante' 
                                                    || $_SESSION['user_tipo'] === 'estudante' || $_SESSION['user_tipo'] === 'admin'): ?>                                                              
                            <a href="monografias.php" class="btn btn-outline-primary">Consultar</a>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary" disabled title="Funcionalidade disponível para todos os usuários">
                                <i class="fas fa-lock me-2"></i> Consultar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card animate__animated animate__fadeInUp animate-delay-3">
                    <div class="card-body p-4" >
                        <i class="fas fa-question-circle feature-icon"></i>
                        <h3 class="mb-3">Ajuda</h3>
                        <p class="mb-4">Tire suas dúvidas sobre o processo de submissão.</p>
                        <a href="ajuda.php" class="btn btn-outline-primary">Ajuda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional Info Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">Explore o conhecimento acadêmico da UNILUANDA</h2>
                <p class="lead mb-4">Nosso sistema oferece acesso a centenas de monografias produzidas por estudantes e pesquisadores da nossa universidade.</p>
                <div class="d-flex">
                    <div class="me-4">
                        <h3 class="text-primary">
                        <?php echo $totalMonografias; ?>
                        </h3>
                        <p>Monografias submetidas</p>
                    </div>
                    <div class="me-4">
                        <h3 class="text-primary">24/7</h3>
                        <p>Acesso permanente</p>
                    </div>
                    <div>
                        <h3 class="text-primary">100%</h3>
                        <p>Gratuito para alunos</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/biblioteca.jpg" alt="Biblioteca UNILUANDA" class="img-fluid rounded shadow" width="350 px" heigth="600 px">
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Links Rápidos</h5>
                <ul class="list-unstyled footer-links">
                    <li class="mb-2"><a href="home.php"><i class="fas fa-chevron-right me-2"></i>Página Inicial</a></li>
                    <li class="mb-2"><a href="sobre.php"><i class="fas fa-chevron-right me-2"></i>Sobre o SIBAM</a></li>
                    <li class="mb-2"><a href="regulamento.php"><i class="fas fa-chevron-right me-2"></i>Regulamento</a></li>
                    <li class="mb-2"><a href="contactos.php"><i class="fas fa-chevron-right me-2"></i>Contactos</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Contactos</h5>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-envelope me-2"></i> sibam@uniluanda.edu.ao</li>
                    <li class="mb-3"><i class="fas fa-phone me-2"></i> +244 937 161 868</li>
                    <li class="mb-3"><i class="fas fa-map-marker-alt me-2"></i> Luanda, Angola</li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-4">Redes Sociais</h5>
                <div class="mb-4">
                    <a href="https://facebook.com/uniluanda" class="social-icon" target="_blank">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="https://twitter.com/uniluanda" class="social-icon" target="_blank">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://instagram.com/uniluanda" class="social-icon" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://linkedin.com/uniluanda" class="social-icon" target="_blank">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <small>© <?php echo date("Y"); ?> SIBAM - UNILUANDA. Todos os direitos reservados.</small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Login Modal -->
<?php include 'modals/login.php'; ?>
<?php include 'modals/register.php'; ?>
<?php include 'modals/submissao.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="js/script.js"></script>
<script>
    // Adiciona animação de scroll suave para todos os links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Adiciona efeito de hover nos cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-10px)';
            card.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
        });
    });
</script>
</body>
</html>