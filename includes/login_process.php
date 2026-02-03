<?php
// Incluir configuração primeiro
require_once 'config.php';

// Agora incluir as outras dependências
require_once 'auth.php';
require_once 'logging.php';

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validar entrada
    if (empty($email) || empty($password)) {
        header("Location: ../home.php?login=error&message=Email e senha são obrigatórios");
        exit();
    }
    
    $auth = new Auth($db);
    $logger = new Logger($db);
    
    // Verificar se existe bloqueio por tentativas excessivas
    $userQuery = "SELECT id FROM usuarios WHERE email = :email";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bindParam(':email', $email);
    $userStmt->execute();
    
    if ($userStmt->rowCount() === 1) {
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar força bruta (com fallback seguro)
        if (method_exists($auth, 'check_brute_force') && $auth->check_brute_force($user['id'], $db)) {
            if (method_exists($logger, 'user_activity')) {
                $logger->user_activity(null, 'login_attempt_blocked', 'Tentativa de login bloqueada por força bruta: ' . $email);
            }
            header("Location: ../home.php?login=error&message=Bloqueado por muitas tentativas. Tente novamente mais tarde.");
            exit();
        }
    }
    
    // AGORA O MÉTODO login() ESTÁ DEFINIDO NA CLASSE Auth
    if ($auth->login($email, $password)) {
        // Login bem-sucedido
        if (method_exists($logger, 'user_activity')) {
            $logger->user_activity($_SESSION['user_id'], 'login_success', 'Login realizado com sucesso');
        }
        header("Location: ../home.php?login=success");
        exit();
    } else {
        // Login falhou
        if ($userStmt->rowCount() === 1) {
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);
            if (method_exists($auth, 'record_login_attempt')) {
                $auth->record_login_attempt($user['id'], $db);
            }
        }
        
        if (method_exists($logger, 'user_activity')) {
            $logger->user_activity(null, 'login_failed', 'Tentativa de login falhou: ' . $email);
        }
        header("Location: ../home.php?login=error&message=Email ou senha incorretos");
        exit();
    }
} else {
    // Se não for POST, redirecionar
    header("Location: ../home.php");
    exit();
}
?>