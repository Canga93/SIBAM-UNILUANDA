<?php
require_once '../includes/config.php';

// Script para limpar tokens expirados (executar via cron diariamente)
$query = "DELETE FROM password_resets WHERE expires_at <= NOW()";
$stmt = $db->prepare($query);

if ($stmt->execute()) {
    $deleted = $stmt->rowCount();
    echo "Tokens expirados removidos: $deleted";
} else {
    echo "Erro ao limpar tokens expirados";
}

// Limpar tentativas de login antigas
$query = "DELETE FROM login_attempts WHERE time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR))";
$stmt = $db->prepare($query);
$stmt->execute();
?>