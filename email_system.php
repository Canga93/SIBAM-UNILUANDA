<?php
session_start();
require_once "includes/config.php"; // Conexão com o banco

$link_gerado = null; // Variável para exibir o link na tela

// Função simples para enviar email (pode ajustar com PHPMailer depois)
function enviarEmail($destinatario, $assunto, $mensagem) {
    $headers = "From: sistema@seudominio.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($destinatario, $assunto, $mensagem, $headers);
}

// Se for pedido de redefinição de senha
if (isset($_POST['email_reset'])) {
    $email = $_POST['email_reset'];
    $token = bin2hex(random_bytes(50));
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Salva token no banco
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expira_em) VALUES (?, ?, ?)");
    $stmt->execute([$email, $token, $expira]);

    // Gera link de redefinição
    $link_gerado = "http://localhost/SIBAM-UNILUANDA/reset_password.php?token=$token";

    $mensagem = "<h2>Redefinição de Senha</h2>
                 <p>Clique no link para redefinir sua senha:</p>
                 <p><a href='$link_gerado'>$link_gerado</a></p>";

    enviarEmail($email, "Redefinição de Senha", $mensagem);
}

// Enviar mensagem do chat
if (isset($_POST['mensagem'], $_POST['destinatario'])) {
    $remetente = $_SESSION['user_id'] ?? 1; // Padrão para teste
    $destinatario = $_POST['destinatario'];
    $mensagem = $_POST['mensagem'];

    $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)");
    $stmt->execute([$remetente, $destinatario, $mensagem]);
}

// Listar mensagens
$mensagens = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT m.*, u.nome AS remetente 
                           FROM mensagens m
                           JOIN usuarios u ON m.remetente_id = u.id
                           WHERE m.destinatario_id = ?
                           ORDER BY m.data_envio DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Email & Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-envelope"></i> Mensagens & Redefinição de Senha</h2>

    <!-- Se gerou link, exibe -->
    <?php if ($link_gerado): ?>
        <div class="alert alert-success">
            <strong>Link Gerado:</strong> <a href="<?= $link_gerado ?>" target="_blank"><?= $link_gerado ?></a>
        </div>
    <?php endif; ?>

    <!-- Formulário de Redefinição de Senha -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Redefinir Senha</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="email_reset" class="form-label">Seu Email</label>
                    <input type="email" class="form-control" id="email_reset" name="email_reset" required>
                </div>
                <button type="submit" class="btn btn-primary">Gerar Link</button>
            </form>
        </div>
    </div>

    <!-- Caixa de Mensagens -->
    <div class="card">
        <div class="card-header bg-success text-white">Mensagens Recebidas</div>
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
            <?php if (!empty($mensagens)): ?>
                <?php foreach ($mensagens as $msg): ?>
                    <div class="mb-2 p-2 border rounded">
                        <strong><?= htmlspecialchars($msg['remetente']) ?></strong>: 
                        <?= htmlspecialchars($msg['mensagem']) ?>
                        <small class="text-muted d-block"><?= $msg['data_envio'] ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhuma mensagem recebida.</p>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <form method="POST">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="number" class="form-control" name="destinatario" placeholder="ID do Destinatário" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="mensagem" placeholder="Digite sua mensagem" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success w-100">Enviar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>

