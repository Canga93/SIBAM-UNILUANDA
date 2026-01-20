<?php
require_once 'includes/config.php';
require_once 'includes/password_reset.php';

$message = '';
$valid_token = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: forgot_password.php?error=Token inválido");
    exit();
}

$password_reset = new PasswordReset($db);
$token_data = $password_reset->verifyToken($token);

if (!$token_data) {
    $message = '<div class="alert alert-danger">Token inválido ou expirado. <a href="forgot_password.php">Solicite um novo link</a>.</div>';
} else {
    $valid_token = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validações
        if (strlen($password) < 6) {
            $message = '<div class="alert alert-danger">A senha deve ter pelo menos 6 caracteres.</div>';
        } elseif ($password !== $confirm_password) {
            $message = '<div class="alert alert-danger">As senhas não coincidem.</div>';
        } else {
            // Redefinir senha
            $result = $password_reset->resetPassword($token, $password);
            
            if ($result['success']) {
                $message = '<div class="alert alert-success">Senha redefinida com sucesso! <a href="home.php">Faça login</a> com sua nova senha.</div>';
                $valid_token = false; // Token foi usado
            } else {
                $message = '<div class="alert alert-danger">' . $result['error'] . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Redefinir Senha - SIBAM UNILUANDA</title>
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
        .password-strength {
            height: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 5px;
        }
        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            width: 0%;
            transition: width 0.3s ease;
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
                                <p class="text-muted">Redefinir Senha</p>
                            </div>
                            
                            <?php echo $message; ?>
                            
                            <?php if ($valid_token): ?>
                            <form method="POST" id="resetForm">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Nova Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Mínimo 6 caracteres" required minlength="6">
                                        <button type="button" class="input-group-text toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength mt-2">
                                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                                    </div>
                                    <small class="form-text text-muted" id="passwordStrengthText">Força da senha</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" placeholder="Digite novamente a senha" required>
                                        <button type="button" class="input-group-text toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted" id="passwordMatchText"></small>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitButton">
                                        <i class="fas fa-key me-2"></i>Redefinir Senha
                                    </button>
                                </div>
                            </form>
                            <?php else: ?>
                            <div class="text-center">
                                <a href="forgot_password.php" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Solicitar Novo Link
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            <a href="home.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Voltar para o Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Alternar visibilidade da senha
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
        
        // Verificar força da senha
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('passwordStrengthText');
        const confirmInput = document.getElementById('confirm_password');
        const matchText = document.getElementById('passwordMatchText');
        const submitButton = document.getElementById('submitButton');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', checkPasswordStrength);
            confirmInput.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordStrength() {
            const password = passwordInput.value;
            let strength = 0;
            
            // Verificar comprimento
            if (password.length >= 6) strength += 20;
            if (password.length >= 8) strength += 20;
            
            // Verificar caracteres diversos
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Atualizar barra e texto
            strengthBar.style.width = strength + '%';
            
            if (strength === 0) {
                strengthBar.style.backgroundColor = '#dc3545';
                strengthText.textContent = 'Muito fraca';
                strengthText.className = 'form-text text-danger';
            } else if (strength <= 40) {
                strengthBar.style.backgroundColor = '#fd7e14';
                strengthText.textContent = 'Fraca';
                strengthText.className = 'form-text text-warning';
            } else if (strength <= 60) {
                strengthBar.style.backgroundColor = '#ffc107';
                strengthText.textContent = 'Média';
                strengthText.className = 'form-text text-warning';
            } else if (strength <= 80) {
                strengthBar.style.backgroundColor = '#20c997';
                strengthText.textContent = 'Forte';
                strengthText.className = 'form-text text-success';
            } else {
                strengthBar.style.backgroundColor = '#198754';
                strengthText.textContent = 'Muito forte';
                strengthText.className = 'form-text text-success';
            }
            
            checkPasswordMatch();
        }
        
        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            
            if (confirm.length === 0) {
                matchText.textContent = '';
                matchText.className = 'form-text text-muted';
                submitButton.disabled = false;
            } else if (password === confirm) {
                matchText.textContent = 'Senhas coincidem';
                matchText.className = 'form-text text-success';
                submitButton.disabled = false;
            } else {
                matchText.textContent = 'Senhas não coincidem';
                matchText.className = 'form-text text-danger';
                submitButton.disabled = true;
            }
        }
    });
    </script>
</body>
</html>