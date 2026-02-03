<?php
class Notification {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Enviar notificação
    public function send($user_id, $type, $message, $link = null) {
        $query = "INSERT INTO notifications (user_id, type, message, link, is_read, created_at) 
                  VALUES (:user_id, :type, :message, :link, 0, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);
        
        return $stmt->execute();
    }
    
    // Obter notificações do usuário
    public function get_user_notifications($user_id, $limit = 10) {
        $query = "SELECT * FROM notifications 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Marcar notificação como lida
    public function mark_as_read($notification_id, $user_id) {
        $query = "UPDATE notifications SET is_read = 1 
                  WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    // Marcar todas como lidas
    public function mark_all_as_read($user_id) {
        $query = "UPDATE notifications SET is_read = 1 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    // Contar notificações não lidas
    public function count_unread($user_id) {
        $query = "SELECT COUNT(*) as count FROM notifications 
                  WHERE user_id = :user_id AND is_read = 0";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}

// Tabela para notificações (adicionar ao script de instalação):
// CREATE TABLE notifications (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     user_id INT NOT NULL,
//     type VARCHAR(50) NOT NULL,
//     message TEXT NOT NULL,
//     link VARCHAR(255),
//     is_read TINYINT(1) DEFAULT 0,
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
//     FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
// );
?>