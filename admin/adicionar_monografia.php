<?php
session_start();
require_once '../includes/config.php';

// Bloquear quem não estiver logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Faça login primeiro.");
    exit;
}

// Configurações de upload
$uploadDir = '../uploads/monografias/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $autor = trim($_POST['autor']);
    $resumo = trim($_POST['resumo']);
    $usuario_id = $_SESSION['user_id'];
    $arquivo = '';

    // Upload do arquivo
    if (!empty($_FILES['arquivo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $novoNome = uniqid('monografia_') . '.' . $ext;
        $destino = $uploadDir . $novoNome;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino)) {
            $arquivo = $novoNome;
        } else {
            $erro = "Erro ao fazer upload do arquivo.";
        }
    }

    if (empty($erro)) {
        $stmt = $pdo->prepare("INSERT INTO monografias (titulo, autor, resumo, arquivo, usuario_id, data_envio) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$titulo, $autor, $resumo, $arquivo, $usuario_id]);

        header("Location: monografias.php?success=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Monografia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h2>Adicionar Monografia</h2>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?= $erro; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Autor</label>
            <input type="text" name="autor" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Resumo</label>
            <textarea name="resumo" class="form-control" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Arquivo (PDF)</label>
            <input type="file" name="arquivo" class="form-control" accept=".pdf" required>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="monografias.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>
</body>
</html>
