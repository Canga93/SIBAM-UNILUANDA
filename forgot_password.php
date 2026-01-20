<?php
require_once 'includes/config.php';
require_once 'includes/password_reset.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (!isValidEmail($email)) {
        $message = '<div class="alert alert-danger">Por favor, informe um email válido.</div>';
    } else {
        $password_reset = new PasswordReset($db);
        $token = $password_reset->generateResetToken($email);
        
        if ($token) {
            // Enviar email
            if ($password_reset->sendResetEmail($email, $token)) {
                $message = '<div class="alert alert-success">Email de redefinição enviado! Verifique sua caixa de entrada.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erro ao enviar email. Tente novamente.</div>';
            }
        } else {
            // Mesmo se o email não existir, não revelamos essa informação
            $message = '<div class="alert alert-success">Se o email existir em nosso sistema, você receberá instruções de redefinição.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Recuperar Senha - SIBAM UNILUANDA</title>
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .auth-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-logo img {
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="auth-card card shadow">
                        <div class="card-body p-5">
                            <div class="auth-logo">
                                <a href="home.php">
                                    <i class="fas fa-book-open fa-3x text-primary"></i>
                                </a>
                                <h3 class="mt-3 mb-0">SIBAM UNILUANDA</h3>
                                <p class="text-muted">Recuperação de Senha</p>
                            </div>
                            
                            <?php echo $message; ?>
                            
                            <form method="POST">
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="seu@email.uniluanda.edu.ao" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Link de Redefinição
                                    </button>
                                </div>
                                
                                <div class="text-center">
                                    <a href="home.php" class="text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i>Voltar para o Login
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">Lembrou sua senha? 
                            <a href="home.php" class="text-decoration-none">Fazer login</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>