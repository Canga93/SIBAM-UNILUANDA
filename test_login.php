<?php
session_start();
require_once 'includes/config.php';

// Se já estiver logado, redirecionar
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header("Location: home.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $auth = new Auth($db);
        
        if ($auth->login($email, $password)) {
            header("Location: home.php?login=success");
            exit();
        } else {
            $message = '<div class="alert alert-danger">Email ou senha incorretos</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Preencha todos os campos</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Login - SIBAM UNILUANDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Teste de Login</h3>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="admin@uniluanda.edu.ao" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       value="admin123" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Testar Login</button>
                        </form>
                        
                        <div class="mt-3">
                            <h5>Credenciais de Teste:</h5>
                            <p><strong>Email:</strong> admin@uniluanda.edu.ao</p>
                            <p><strong>Senha:</strong> admin123</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <a href="home.php" class="btn btn-secondary">Voltar para Home</a>
                    <a href="test_auth.php" class="btn btn-info">Testar Classe Auth</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>