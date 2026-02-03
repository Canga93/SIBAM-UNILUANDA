<?php
// Configurações básicas
define('SITE_NAME', 'SIBAM - UNILUANDA');
define('SITE_URL', 'http://localhost/sibam-uniluanda');

// Configurações de upload
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx']);
define('UPLOAD_PATH', 'assets/uploads/monografias/');

// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivos necessários NA ORDEM CORRETA
require_once 'database.php';
require_once 'auth.php';       // ADICIONAR ESTA LINHA
require_once 'functions.php';

// Criar instância do banco de dados
$database = new Database();
$db = $database->getConnection();

// Verificar e criar tabelas se necessário
require_once 'database_setup.php';
$dbSetup = new DatabaseSetup($db);
$missing_tables = $dbSetup->checkTablesExist();

if (!empty($missing_tables)) {
    // Se faltarem tabelas, criá-las
    if (!$dbSetup->createTables()) {
        die("Erro ao criar tabelas do banco de dados. Verifique as permissões.");
    }
}

// Verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Verificar se usuário é admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'admin';
}

// Redirecionar se não estiver logado
function redirectIfNotLogged($url = 'home.php') {
    if (!isLoggedIn()) {
        header("Location: $url");
        exit();
    }
}

// Redirecionar se não for admin
function redirectIfNotAdmin($url = 'home.php') {
    if (!isAdmin()) {
        header("Location: $url");
        exit();
    }
}
?>