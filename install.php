<?php
// Script de instalação para criar o banco de dados e tabelas
// AVISO: Este arquivo deve ser excluído após a instalação

if (file_exists('includes/config.php')) {
    die('O sistema já parece estar instalado. Exclua este arquivo por segurança.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'sibam_uniluanda';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    
    try {
        // Tentar conexão com o banco de dados
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar banco de dados se não existir
        $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $conn->exec("USE $dbname");
        
        // Criar tabelas
        $sql = "
        CREATE TABLE usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            tipo ENUM('admin', 'estudante', 'professor') DEFAULT 'estudante',
            foto VARCHAR(255) DEFAULT 'default.png',
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        
        CREATE TABLE monografias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            resumo TEXT NOT NULL,
            autor_id INT NOT NULL,
            orientador VARCHAR(100),
            area VARCHAR(100),
            palavras_chave VARCHAR(255),
            arquivo VARCHAR(255) NOT NULL,
            data_publicacao DATE,
            status ENUM('pendente', 'aprovado', 'rejeitado') DEFAULT 'pendente',
            data_submissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );
        
        CREATE TABLE galerias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            descricao TEXT,
            imagem VARCHAR(255) NOT NULL,
            data_publicacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            usuario_id INT,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        );
        
        INSERT INTO usuarios (nome, email, password, tipo) VALUES 
        ('Administrador', 'admin@uniluanda.edu.ao', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
        ";
        
        $conn->exec($sql);
        
        // Criar arquivo de configuração
        $configContent = "<?php
class Database {
    private \$host = \"$host\";
    private \$db_name = \"$dbname\";
    private \$username = \"$username\";
    private \$password = \"$password\";
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, \$this->username, \$this->password);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) {
            echo \"Connection error: \" . \$exception->getMessage();
        }
        return \$this->conn;
    }
}
?>";
        
        file_put_contents('includes/database.php', $configContent);
        
        // Criar diretórios necessários
        if (!file_exists('assets/images/avatar')) {
            mkdir('assets/images/avatar', 0777, true);
        }
        
        if (!file_exists('assets/uploads/monografias')) {
            mkdir('assets/uploads/monografias', 0777, true);
        }
        
        if (!file_exists('assets/uploads/galerias')) {
            mkdir('assets/uploads/galerias', 0777, true);
        }
        
        // Copiar avatar padrão
        copy('https://via.placeholder.com/150/007bff/ffffff?text=U', 'assets/images/avatar/default.png');
        
        $success = "Instalação concluída com sucesso! Exclua este arquivo (install.php) por segurança.";
        
    } catch (PDOException $e) {
        $error = "Erro na instalação: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - SIBAM UNILUANDA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3 class="mb-0">Instalação do SIBAM</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                            <div class="text-center mt-4">
                                <a href="home.php" class="btn btn-primary">Ir para o Site</a>
                            </div>
                        <?php else: ?>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="host" class="form-label">Host do Banco de Dados</label>
                                    <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dbname" class="form-label">Nome do Banco de Dados</label>
                                    <input type="text" class="form-control" id="dbname" name="dbname" value="sibam_uniluanda" required>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Usuário do Banco de Dados</label>
                                    <input type="text" class="form-control" id="username" name="username" value="root" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password do Banco de Dados</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Instalar</button>
                                </div>
                            </form>
                            
                            <div class="alert alert-info mt-4">
                                <h6 class="alert-heading">Informações Importantes:</h6>
                                <ul class="mb-0">
                                    <li>Certifique-se de que o MySQL está instalado e funcionando</li>
                                    <li>O usuário do banco deve ter permissões para criar bancos e tabelas</li>
                                    <li>Este arquivo será automaticamente invalidado após a instalação</li>
                                    <li>Exclua este arquivo manualmente após a instalação completa</li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>