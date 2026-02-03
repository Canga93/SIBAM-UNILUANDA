<?php
class Logger {
    private $conn;
    private $log_file;
    
    public function __construct($db, $log_file = 'logs/system.log') {
        $this->conn = $db;
        $this->log_file = $log_file;
        
        // Criar diretório de logs se não existir
        $log_dir = dirname($log_file);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
    }
    
    // Log para arquivo
    public function log_to_file($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $log_message .= " " . json_encode($context);
        }
        
        $log_message .= PHP_EOL;
        
        file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
    }
    
    // Log para banco de dados
    public function log_to_db($user_id, $action, $details = null, $ip_address = null) {
        if ($ip_address === null) {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        $query = "INSERT INTO system_logs (user_id, action, details, ip_address, created_at) 
                  VALUES (:user_id, :action, :details, :ip_address, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':details', $details);
        $stmt->bindParam(':ip_address', $ip_address);
        
        return $stmt->execute();
    }
    
    // Log de erro
    public function error($message, $context = []) {
        $this->log_to_file('ERROR', $message, $context);
    }
    
    // Log de warning
    public function warning($message, $context = []) {
        $this->log_to_file('WARNING', $message, $context);
    }
    
    // Log de info
    public function info($message, $context = []) {
        $this->log_to_file('INFO', $message, $context);
    }
    
    // Log de atividade do usuário
    public function user_activity($user_id, $action, $details = null) {
        $this->log_to_db($user_id, $action, $details);
        $this->info("User activity: $action", ['user_id' => $user_id, 'details' => $details]);
    }
}

// Tabela para logs do sistema (adicionar ao script de instalação):
// CREATE TABLE system_logs (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT,
//     action VARCHAR(100) NOT NULL,
//     details TEXT,
//     ip_address VARCHAR(45),
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
// );

// Tabela para tentativas de login (adicionar ao script de instalação):
// CREATE TABLE login_attempts (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT,
//     time INT,
//     FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
// );
?>