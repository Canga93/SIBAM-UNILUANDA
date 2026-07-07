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
    $curso            = trim($_POST['curso']);
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
                    titulo = ?, curso = ?, area = ?, resumo = ?, orientador = ?,
                    data_publicacao = ?, palavras_chave = ?, arquivo = ?
                WHERE id = ?
            ");

            $update->execute([
                $titulo, $curso, $area, $resumo, $orientador,
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
        :root {
    --primary: #2c3e50;
    --secondary: #34495e;
    --accent: #3498db;
    --success: #27ae60;
    --danger: #e74c3c;
    --light-bg: #f4f6f9;
    --border: #dcdfe6;
    --text-dark: #2c3e50;
    --text-muted: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(120deg, #eef2f7, #dbe4f0);
            min-height: 100vh;
            padding: 30px 0;
            color: var(--text-dark);
        }

        /* Card principal */
        .form-container {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #eef0f4;
        }

        /* Header */
        .form-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 25px 35px;
        }

        .form-header h2 {
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .form-header small {
            font-weight: 300;
        }

        /* Corpo */
        .form-body {
            padding: 35px;
        }

        /* Labels */
        .form-label {
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 6px;
        }

        /* Inputs */
        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.8px solid var(--border);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            background-color: #fff;
        }

        .form-control::placeholder {
            color: #adb5bd;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.15);
        }

        /* Textarea */
        textarea.form-control {
            min-height: 140px;
            resize: vertical;
        }

        /* Alertas */
        .alert-custom {
            border-radius: 12px;
            padding: 16px 18px;
            font-size: 0.95rem;
        }

        /* Info do arquivo */
        .file-info {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 15px 18px;
            border-left: 5px solid var(--accent);
            font-size: 0.9rem;
        }

        /* Botões */
        .btn-custom {
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back {
            background: #95a5a6;
            border: none;
            color: #fff;
        }

        .btn-back:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        .btn-submit {
            background: var(--success);
            border: none;
            color: #fff;
        }

        .btn-submit:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
        }

        /* Footer */
        .text-muted small {
            font-size: 0.85rem;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .form-body {
                padding: 25px;
            }

            .btn-custom {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px;
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
                                <label class="form-label">Curso *</label>
                                <select class="form-select" name="curso" required>
                                    <?php
                                    $cursos = [
                                        "Engenharia Mecatronica",
                                        "Engenharia dos Transportes",
                                        "Engenharia Informatica",
                                        "Informatica de Gestao",
                                        "Gestao e Logistica",
                                        "Gestao Aeronautica"
                                    ];
                                    foreach ($cursos as $c):
                                    ?>
                                        <option value="<?= $c ?>" <?= $monografia['curso'] === $c ? 'selected' : '' ?>>
                                            <?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                                <label class="form-label">Área *</label>
                                <select class="form-select" name="area" required>
                                    <?php
                                    $areas = [
                                        "Engenharia",
                                        "Gestao",
                                       
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

        if (!area.value) {
            e.preventDefault();
            alert('Por favor, selecione o curso.');
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