<?php

require_once 'config.php';


// Processar submissão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = sanitizeInput($_POST['titulo']);
    $resumo = sanitizeInput($_POST['resumo']);
    $orientador = sanitizeInput($_POST['orientador']);
    $area = sanitizeInput($_POST['area']);
    $palavras_chave = sanitizeInput($_POST['palavras_chave']);
    $data_publicacao = sanitizeInput($_POST['data_publicacao']);
    
    // Processar upload do arquivo
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        // Verificar tipo de arquivo
        $fileType = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($fileType, ALLOWED_FILE_TYPES)) {
            $message = '<div class="alert alert-danger">Tipo de arquivo não permitido. Use apenas PDF, DOC ou DOCX.</div>';
        } 
        // Verificar tamanho do arquivo
        elseif ($_FILES['arquivo']['size'] > MAX_FILE_SIZE) {
            $message = '<div class="alert alert-danger">Arquivo muito grande. O tamanho máximo é 10MB.</div>';
        } else {
            // Fazer upload do arquivo
            $arquivoName = uploadFile($_FILES['arquivo'], UPLOAD_PATH);
            
            if ($arquivoName) {
                // Inserir no banco de dados
                $query = "INSERT INTO monografias (titulo, resumo, autor_id, orientador, area, palavras_chave, arquivo, data_publicacao, status) 
                          VALUES (:titulo, :resumo, :autor_id, :orientador, :area, :palavras_chave, :arquivo, :data_publicacao, 'pendente')";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':resumo', $resumo);
                $stmt->bindParam(':autor_id', $_SESSION['user_id']);
                $stmt->bindParam(':orientador', $orientador);
                $stmt->bindParam(':area', $area);
                $stmt->bindParam(':palavras_chave', $palavras_chave);
                $stmt->bindParam(':arquivo', $arquivoName);
                $stmt->bindParam(':data_publicacao', $data_publicacao);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Monografia submetida com sucesso! Aguarde a aprovação.</div>';
                    // Limpar formulário
                    $_POST = array();
                } else {
                    $message = '<div class="alert alert-danger">Erro ao submeter monografia. Tente novamente.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Erro no upload do arquivo. Tente novamente.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-danger">Por favor, selecione um arquivo para upload.</div>';
    }
}
// Exibir mensagem se existir
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); // Limpar mensagem após exibir
}

?>