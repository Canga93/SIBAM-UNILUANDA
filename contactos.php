<?php
require_once 'includes/config.php';

$message = '';

// Processar formulário de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $assunto = sanitizeInput($_POST['assunto']);
    $mensagem = sanitizeInput($_POST['mensagem']);
    
    // Validação básica
    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        $message = '<div class="alert alert-danger">Por favor, preencha todos os campos.</div>';
    } elseif (!isValidEmail($email)) {
        $message = '<div class="alert alert-danger">Por favor, informe um email válido.</div>';
    } else {
        // Simular envio de email (em produção, integrar com sistema de email)
        $to = "sibam@uniluanda.edu.ao";
        $subject = "Contacto via SIBAM: $assunto";
        $body = "Nome: $nome\nEmail: $email\n\nMensagem:\n$mensagem";
        $headers = "From: $email";
        
        // Em ambiente real, usar mail() ou biblioteca de email
        // if (mail($to, $subject, $body, $headers)) {
        if (true) { // Simulação de sucesso
            $message = '<div class="alert alert-success">Mensagem enviada com sucesso! Entraremos em contacto em breve.</div>';
            $_POST = array(); // Limpar formulário
        } else {
            $message = '<div class="alert alert-danger">Erro ao enviar mensagem. Tente novamente.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Contactos - SIBAM UNILUANDA</title>
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
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="mb-3">Contactos</h1>
                    <p class="lead">Entre em contacto connosco para mais informações</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                                <h5>Endereço</h5>
                                <p>Universidade UNILUANDA<br>Luanda, Angola</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-phone fa-3x text-primary mb-3"></i>
                                <h5>Telefone</h5>
                                <p>+244 937 161 868<br>Segunda a Sexta, 8h-18h</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                                <h5>Email</h5>
                                <p>sibam@uniluanda.edu.ao<br>suporte@uniluanda.edu.ao</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                                <h5>Horário de Atendimento</h5>
                                <p>Segunda a Sexta<br>8h00 - 18h00</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Envie-nos uma mensagem</h5>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome Completo *</label>
                                    <input type="text" class="form-control" id="nome" name="nome" 
                                           value="<?php echo $_POST['nome'] ?? ''; ?>" required>
                                    <div class="invalid-feedback">Por favor, informe seu nome.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $_POST['email'] ?? ''; ?>" required>
                                    <div class="invalid-feedback">Por favor, informe um email válido.</div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="assunto" class="form-label">Assunto *</label>
                                <input type="text" class="form-control" id="assunto" name="assunto" 
                                       value="<?php echo $_POST['assunto'] ?? ''; ?>" required>
                                <div class="invalid-feedback">Por favor, informe o assunto.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mensagem" class="form-label">Mensagem *</label>
                                <textarea class="form-control" id="mensagem" name="mensagem" rows="5" required><?php echo $_POST['mensagem'] ?? ''; ?></textarea>
                                <div class="invalid-feedback">Por favor, escreva sua mensagem.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>