<?php
require_once 'includes/config.php';
require_once 'includes/database_setup.php';

$dbSetup = new DatabaseSetup($db);
$missing_tables = $dbSetup->checkTablesExist();

echo "<h2>Verificação do Banco de Dados</h2>";

if (empty($missing_tables)) {
    echo "<div class='alert alert-success'>Todas as tabelas estão criadas corretamente!</div>";
} else {
    echo "<div class='alert alert-warning'>Tabelas faltando: " . implode(', ', $missing_tables) . "</div>";
    
    if (isset($_GET['action']) && $_GET['action'] == 'create_tables') {
        if ($dbSetup->createTables()) {
            echo "<div class='alert alert-success'>Tabelas criadas com sucesso!</div>";
            echo "<meta http-equiv='refresh' content='2;url=check_database.php'>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao criar tabelas. Verifique o log de erro.</div>";
        }
    } else {
        echo "<a href='check_database.php?action=create_tables' class='btn btn-primary'>Criar Tabelas</a>";
    }
}

// Verificar usuário admin
$adminQuery = "SELECT COUNT(*) as count FROM usuarios WHERE tipo = 'admin'";
$adminStmt = $db->query($adminQuery);
$adminCount = $adminStmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "<h3 class='mt-4'>Usuário Administrador</h3>";
if ($adminCount > 0) {
    echo "<div class='alert alert-success'>Usuário admin existe ($adminCount encontrado(s))</div>";
    
    // Mostrar informações do admin
    $adminInfoQuery = "SELECT id, nome, email FROM usuarios WHERE tipo = 'admin'";
    $adminInfoStmt = $db->query($adminInfoQuery);
    $admins = $adminInfoStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($admins as $admin) {
        echo "<p>ID: {$admin['id']}, Nome: {$admin['nome']}, Email: {$admin['email']}</p>";
    }
} else {
    echo "<div class='alert alert-warning'>Nenhum usuário admin encontrado</div>";
}
?>