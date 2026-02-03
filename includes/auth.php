<?php
class Auth {
    private $conn;
    
    public function __construct($db = null) {
        if ($db === null) {
            // Criar conexão se não for fornecida
            require_once 'database.php';
            $database = new Database();
            $this->conn = $database->getConnection();
        } else {
            $this->conn = $db;
        }
    }
    
    // REGISTRAR USUÁRIO
    public function register($nome, $email, $password, $tipo = 'estudante') {
        // Verificar se email já existe
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return false; // Email já existe
        }
        
        // Inserir novo usuário
        $query = "INSERT INTO usuarios (nome, email, password, tipo, data_criacao) 
                  VALUES (:nome, :email, :password, :tipo, NOW())";
        $stmt = $this->conn->prepare($query);
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':tipo', $tipo);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // LOGIN - MÉTODO QUE ESTAVA FALTANDO
    public function login($email, $password) {
        $query = "SELECT id, nome, email, password, tipo, foto FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password'])) {
                // Definir sessão
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_tipo'] = $user['tipo'];
                $_SESSION['user_foto'] = $user['foto'] ?? 'default.png';
                
                return true;
            }
        }
        
        return false;
    }
    
    // LOGOUT
    public function logout() {
        session_destroy();
        return true;
    }
    
    // ATUALIZAR PERFIL
    public function updateProfile($userId, $nome, $email, $foto = null) {
        $query = "UPDATE usuarios SET nome = :nome, email = :email";
        $params = [':nome' => $nome, ':email' => $email, ':id' => $userId];
        
        if ($foto) {
            $query .= ", foto = :foto";
            $params[':foto'] = $foto;
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
    
    // VERIFICAR SE USUÁRIO EXISTE
    public function userExists($email) {
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    // OBTER USUÁRIO POR ID
    public function getUserById($id) {
        $query = "SELECT id, nome, email, tipo, foto, data_criacao FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // OBTER USUÁRIO POR EMAIL
    public function getUserByEmail($email) {
        $query = "SELECT id, nome, email, tipo, foto, data_criacao FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTER DADOS COMPLETOS DO USUÁRIO (INCLUINDO SENHA PARA VERIFICAÇÕES)
public function getUserData($userId) {
    $query = "SELECT id, nome, email, password, tipo, foto, data_criacao, data_atualizacao 
              FROM usuarios WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    return false;
}

// Também adicionar este método para atualizar a foto
public function updateUserPhoto($userId, $photo) {
    $query = "UPDATE usuarios SET foto = :foto WHERE id = :id";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':foto', $photo);
    $stmt->bindParam(':id', $userId);
    
    return $stmt->execute();
}
    
    // ALTERAR SENHA
    public function changePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $query = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
    
    // VERIFICAR SENHA ATUAL
    public function verifyCurrentPassword($userId, $password) {
        $query = "SELECT password FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return password_verify($password, $user['password']);
        }
        
        return false;
    }
    
    // MÉTODOS PARA PREVENÇÃO DE FORÇA BRUTA (COM VERIFICAÇÃO DE TABELA)
    public function check_brute_force($user_id, $conn = null) {
        $db = $conn ?: $this->conn;
        
        // Verificar se a tabela existe
        if (!$this->tableExists('login_attempts', $db)) {
            return false; // Se a tabela não existe, não há bloqueio
        }
        
        $now = time();
        $valid_attempts = $now - (2 * 60 * 60); // 2 horas
        
        try {
            $query = "SELECT time FROM login_attempts WHERE user_id = :user_id AND time > :valid_attempts";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':valid_attempts', $valid_attempts);
            $stmt->execute();
            
            return $stmt->rowCount() > 5; // Mais de 5 tentativas em 2 horas
        } catch (PDOException $e) {
            error_log("Erro ao verificar força bruta: " . $e->getMessage());
            return false;
        }
    }
    
    public function record_login_attempt($user_id, $conn = null) {
        $db = $conn ?: $this->conn;
        
        // Verificar se a tabela existe
        if (!$this->tableExists('login_attempts', $db)) {
            return false; // Se a tabela não existe, não registrar
        }
        
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $query = "INSERT INTO login_attempts (user_id, time, ip_address) VALUES (:user_id, :time, :ip_address)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':time', time());
            $stmt->bindParam(':ip_address', $ip_address);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
            return false;
        }
    }
    
    // VERIFICAR SE UMA TABELA EXISTE
    private function tableExists($tableName, $conn = null) {
        $db = $conn ?: $this->conn;
        
        try {
            $query = "SHOW TABLES LIKE :table_name";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':table_name', $tableName);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar tabela: " . $e->getMessage());
            return false;
        }
    }
    
    // LISTAR TODOS OS USUÁRIOS (PARA ADMIN)
    public function getAllUsers($limit = 100, $offset = 0) {
        $query = "SELECT id, nome, email, tipo, foto, data_criacao 
                  FROM usuarios 
                  ORDER BY data_criacao DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // CONTAR TOTAL DE USUÁRIOS
    public function countUsers() {
        $query = "SELECT COUNT(*) as total FROM usuarios";
        $stmt = $this->conn->query($query);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    // EXCLUIR USUÁRIO (APENAS ADMIN)
    public function deleteUser($userId) {
        // Não permitir excluir a si mesmo
        if (isset($_SESSION['user_id']) && $userId == $_SESSION['user_id']) {
            return false;
        }
        
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
    
    // ATUALIZAR TIPO DE USUÁRIO (APENAS ADMIN)
    public function updateUserType($userId, $tipo) {
        $query = "UPDATE usuarios SET tipo = :tipo WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
}
?>