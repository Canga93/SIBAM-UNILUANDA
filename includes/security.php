<?php
// Proteção contra ataques comuns
class Security {
    
    // Prevenção de XSS
    public static function xss_clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::xss_clean($value);
            }
            return $data;
        }
        
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    // Prevenção de SQL Injection
    public static function sql_escape($data, $conn) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sql_escape($value, $conn);
            }
            return $data;
        }
        
        return $conn->quote($data);
    }
    
    // Validação de email
    public static function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Validação de URL
    public static function validate_url($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    // Geração de token CSRF
    public static function generate_csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Verificação de token CSRF
    public static function verify_csrf_token($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // Proteção contra força bruta
    public static function check_brute_force($user_id, $conn) {
        $now = time();
        $valid_attempts = $now - (2 * 60 * 60); // 2 horas
        
        $query = "SELECT time FROM login_attempts WHERE user_id = :user_id AND time > :valid_attempts";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':valid_attempts', $valid_attempts);
        $stmt->execute();
        
        return $stmt->rowCount() > 5; // Mais de 5 tentativas em 2 horas
    }
    
    // Registro de tentativa de login
    public static function record_login_attempt($user_id, $conn) {
        $query = "INSERT INTO login_attempts (user_id, time) VALUES (:user_id, :time)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':time', time());
        $stmt->execute();
    }
}

// Prevenção de clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevenção de XSS
header('X-XSS-Protection: 1; mode=block');

// Prevenção de MIME sniffing
header('X-Content-Type-Options: nosniff');

// Política de segurança de conteúdo
// header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;");
?>