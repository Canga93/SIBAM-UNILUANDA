<?php
require_once '../includes/config.php';
redirectIfNotLogged();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../admin/monografias.php?error=id_invalido");
    exit();
}

$id = (int) $_GET['id'];

// Buscar monografia
try {
    $stmt = $db->prepare("SELECT * FROM monografias WHERE id = ?");
    $stmt->execute([$id]);
    $monografia = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    header("Location: ../admin/monografias.php?error=erro_bd");
    exit();
}

if (!$monografia) {
    header("Location: ../admin/monografias.php?error=nao_encontrada");
    exit();
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo          = trim($_POST['titulo']);
    $area            = trim($_POST['area']);
    $resumo          = trim($_POST['resumo']);
    $orientador      = trim($_POST['orientador']);
    $data_publicacao = $_POST['data_publicacao'] ?? null;
    $palavras_chave  = trim($_POST['palavras_chave']);

    if ($titulo && $area && $resumo) {

        // Arquivo atual
        $arquivo_nome = $monografia['arquivo'];

        // Upload opcional
        if (!empty($_FILES['arquivo']['name']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'doc', 'docx'];

            if (in_array($ext, $permitidos)) {
                $arquivo_nome = uniqid('mono_') . "." . $ext;
                move_uploaded_file(
                    $_FILES['arquivo']['tmp_name'],
                    "../uploads/monografias/" . $arquivo_nome
                );
            }
        }

        // Atualizar no banco
        try {
            $update = $db->prepare("
                UPDATE monografias SET
                    titulo = ?, area = ?, resumo = ?, orientador = ?,
                    data_publicacao = ?, palavras_chave = ?, arquivo = ?
                WHERE id = ?
            ");

            $update->execute([
                $titulo, $area, $resumo, $orientador,
                $data_publicacao, $palavras_chave, $arquivo_nome, $id
            ]);

            // Redirecionar para dashboard
            $dashboard = ($_SESSION['user_tipo'] === 'admin')
                ? '../admin/monografias.php'
                : '../aluno/dashboard.php';

            header("Location: " . $dashboard . "?success=monografia_atualizada");
            exit();
        } catch (Exception $e) {
            $erro = "Erro ao atualizar a monografia: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha os campos obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Monografia</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }
        
        .form-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 20px 30px;
        }
        
        .form-header h2 {
            font-weight: 600;
            margin: 0;
        }
        
        .form-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-custom {
            padding: 10px 25px;
            weight: 100px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-back {
            background: #95a5a6;
            border-color: #95a5a6;
            color: white;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: #7f8c8d;
            border-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn-submit {
            background: #27ae60;
            border-color: #27ae60;
            color: white;
        }
        
        .btn-submit:hover {
            background: #229954;
            border-color: #229954;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .alert-custom {
            border-radius: 8px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .file-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #3498db;
        }
        
        @media (max-width: 768px) {
            .form-body {
                padding: 20px;
            }
            
            .btn-custom {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="form-container">
                <!-- Header -->
                <div class="form-header">
                    <h2>
                        <i class="fas fa-edit me-2"></i>Editar Monografia
                        <small class="d-block mt-1" style="font-size: 0.8rem; opacity: 0.9;">
                            ID: <?= $id ?> | Atualize as informações abaixo
                        </small>
                    </h2>
                </div>

                <!-- Body -->
                <div class="form-body">
                    <!-- Error Message -->
                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger alert-custom">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= $erro ?>
                        </div>
                    <?php endif; ?>

                    <!-- Success Message -->
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-custom">
                            <i class="fas fa-check-circle me-2"></i>
                            Monografia atualizada com sucesso!
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- Título e Área -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-control" name="titulo"
                                       value="<?= htmlspecialchars($monografia['titulo']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Área *</label>
                                <select class="form-select" name="area" required>
                                    <?php
                                    $areas = [
                                        "Engenharia Mecatronica",
                                        "Engenharia dos Transportes",
                                        "Engenharia Informatica",
                                        "Informatica de Gestao",
                                        "Gestao e Logistica",
                                        "Gestao Aeronautica"
                                    ];
                                    foreach ($areas as $a):
                                    ?>
                                        <option value="<?= $a ?>" <?= $monografia['area'] === $a ? 'selected' : '' ?>>
                                            <?= $a ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Resumo -->
                        <div class="mb-4">
                            <label class="form-label">Resumo *</label>
                            <textarea class="form-control" name="resumo" rows="5" required><?= htmlspecialchars($monografia['resumo']) ?></textarea>
                        </div>

                        <!-- Orientador e Data -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Orientador</label>
                                <input type="text" class="form-control" name="orientador"
                                       value="<?= htmlspecialchars($monografia['orientador']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Data de Publicação</label>
                                <input type="date" class="form-control" name="data_publicacao"
                                       value="<?= $monografia['data_publicacao'] ?>">
                            </div>
                        </div>

                        <!-- Palavras-chave -->
                        <div class="mb-4">
                            <label class="form-label">Palavras-chave</label>
                            <input type="text" class="form-control" name="palavras_chave"
                                   value="<?= htmlspecialchars($monografia['palavras_chave']) ?>"
                                   placeholder="Separe por vírgulas">
                        </div>

                        <!-- Arquivo -->
                        <div class="mb-4">
                            <label class="form-label">Arquivo (opcional)</label>
                            <input type="file" class="form-control" name="arquivo"
                                   accept=".pdf,.doc,.docx">
                            
                            <div class="file-info mt-3">
                                <strong>Arquivo atual:</strong> 
                                <span class="ms-2"><?= htmlspecialchars($monografia['arquivo']) ?></span>
                                <div class="form-text">
                                    Deixe em branco para manter o arquivo atual. Formatos permitidos: PDF, DOC, DOCX
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between pt-3">
                            <a href="../admin/monografias.php" class="btn btn-custom btn-back">
                                <i class="fas fa-arrow-left me-1"></i> Voltar
                            </a>

                            <button type="submit" class="btn btn-custom btn-submit">
                                <i class="fas fa-save me-1"></i> Atualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Footer Note -->
            <div class="text-center mt-4 text-muted">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Campos marcados com * são obrigatórios
                </small>
            </div>
        </div>
    </div>
</div>

<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Form validation básica
    document.querySelector('form').addEventListener('submit', function(e) {
        const titulo = this.querySelector('input[name="titulo"]');
        const area = this.querySelector('select[name="area"]');
        const resumo = this.querySelector('textarea[name="resumo"]');
        
        if (!titulo.value.trim()) {
            e.preventDefault();
            alert('Por favor, preencha o título.');
            titulo.focus();
            return false;
        }
        
        if (!area.value) {
            e.preventDefault();
            alert('Por favor, selecione uma área.');
            area.focus();
            return false;
        }
        
        if (!resumo.value.trim()) {
            e.preventDefault();
            alert('Por favor, preencha o resumo.');
            resumo.focus();
            return false;
        }
    });
</script>
</body>
</html>