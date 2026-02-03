<?php
class PasswordReset {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Gera token de redefinição
    public function generateResetToken($email) {
        // Verifica se email existe
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false; // Não revela que o email não existe
        }

        // Gera token e define validade (1h)
        $token = bin2hex(random_bytes(50));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Apaga tokens antigos
        $this->pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Insere novo token
        $stmt = $this->pdo->prepare("INSERT INTO password_resets (email, token, expira_em) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expira]);

        return $token;
    }

    // Envia email de redefinição
    public function sendResetEmail($email, $token) {
        $resetLink = "http://localhost/SIBAM-UNILUANDA/reset_password.php?token=$token";

        $assunto = "Redefinição de Senha - SIBAM UNILUANDA";
        $mensagem = "
            <h2>Redefinição de Senha</h2>
            <p>Olá,</p>
            <p>Recebemos uma solicitação para redefinir sua senha. Clique no link abaixo:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>Este link expira em 1 hora.</p>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@uniluanda.edu.ao" . "\r\n";

        return mail($email, $assunto, $mensagem, $headers);
    }
}

// Funções auxiliares
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
