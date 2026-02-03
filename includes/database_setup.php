<?php
class DatabaseSetup {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function createTables() {
        try {
            // Tabela de usuários
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nome VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    tipo ENUM('admin', 'estudante', 'professor') DEFAULT 'estudante',
                    foto VARCHAR(255) DEFAULT 'default.png',
                    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Tabela de tentativas de login
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    time INT,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
                )
            ");
            
            // Tabela de monografias
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS monografias (
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
                )
            ");
            
            // Tabela de redefinição de senha
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(100) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_token (token),
                    INDEX idx_email (email)
                )
            ");
            
            // Tabela de notificações
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    message TEXT NOT NULL,
                    link VARCHAR(255),
                    is_read TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
                )
            ");
            
            // Tabela de logs do sistema
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(100) NOT NULL,
                    details TEXT,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
                )
            ");
            
            // Inserir usuário admin padrão
            $this->createAdminUser();
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Erro ao criar tabelas: " . $e->getMessage());
            return false;
        }
    }
    
    private function createAdminUser() {
        // Verificar se já existe um admin
        $checkQuery = "SELECT COUNT(*) as count FROM usuarios WHERE tipo = 'admin'";
        $stmt = $this->conn->query($checkQuery);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Inserir usuário admin padrão
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $insertQuery = "
                INSERT INTO usuarios (nome, email, password, tipo) 
                VALUES ('Administrador', 'admin@uniluanda.edu.ao', :password, 'admin')
            ";
            
            $stmt = $this->conn->prepare($insertQuery);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
        }
    }
    
    public function checkTablesExist() {
        $tables = ['usuarios', 'monografias', 'login_attempts', 'password_resets', 'notifications', 'system_logs'];
        $missing_tables = [];
        
        foreach ($tables as $table) {
            $checkQuery = "SHOW TABLES LIKE '$table'";
            $stmt = $this->conn->query($checkQuery);
            if ($stmt->rowCount() == 0) {
                $missing_tables[] = $table;
            }
        }
        
        return $missing_tables;
    }
}
?>