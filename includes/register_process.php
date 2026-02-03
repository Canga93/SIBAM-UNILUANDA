<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $tipo = sanitizeInput($_POST['tipo']);
    
    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "Nome é obrigatório.";
    }
    
    if (!isValidEmail($email)) {
        $errors[] = "Email inválido.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "A password deve ter pelo menos 6 caracteres.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "As passwords não coincidem.";
    }
    
    if (!isset($_POST['terms'])) {
        $errors[] = "Você deve aceitar os termos e condições.";
    }
    
    if (empty($errors)) {
        $auth = new Auth();
        
        if ($auth->register($nome, $email, $password, $tipo)) {
            // Registro bem-sucedido, fazer login automaticamente
            if ($auth->login($email, $password)) {
                header("Location: ../home.php?register=success");
                exit();
            } else {
                header("Location: ../home.php?register=success_login_error");
                exit();
            }
        } else {
            header("Location: ../home.php?register=error&message=" . urlencode("Email já está em uso."));
            exit();
        }
    } else {
        header("Location: ../home.php?register=error&message=" . urlencode(implode(" ", $errors)));
        exit();
    }
} else {
    header("Location: ../home.php");
    exit();
}
?>