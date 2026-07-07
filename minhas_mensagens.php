<?php
require_once 'includes/config.php';


// Apenas estudantes podem acessar esta página
if ($_SESSION['user_tipo'] !== 'estudante') {
    if ($_SESSION['user_tipo'] === 'admin') {
        header('Location: admin/mensagens.php');
    } else {
        header('Location: home.php');
    }
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Processar envio de nova mensagem (resposta do estudante)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder'])) {
    $conversa_id = (int)$_POST['conversa_id'];
    $mensagem = trim($_POST['mensagem']);
    
    if ($conversa_id > 0 && !empty($mensagem)) {
        // Verificar se a conversa pertence ao usuário
        $stmt = $db->prepare("SELECT admin_id FROM conversas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$conversa_id, $user_id]);
        $conversa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($conversa) {
            $admin_id = $conversa['admin_id']; // Pode ser NULL se ainda não foi respondido por admin
            
            // Inserir resposta (remetente = estudante, destinatário = admin se existir, senão NULL)
            $stmt = $db->prepare("INSERT INTO mensagens_internas (conversa_id, remetente_id, destinatario_id, mensagem, lida) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$conversa_id, $user_id, $admin_id, $mensagem]);
            
            // Atualizar última atualização da conversa
            $db->prepare("UPDATE conversas SET ultima_atualizacao = NOW() WHERE id = ?")->execute([$conversa_id]);
            
            $success = "Mensagem enviada.";
        } else {
            $error = "Conversa não encontrada.";
        }
    } else {
        $error = "A mensagem não pode estar vazia.";
    }
}

// Marcar mensagens como lidas quando o usuário visualiza uma conversa
if (isset($_GET['conversa'])) {
    $conversa_id = (int)$_GET['conversa'];
    // Marcar como lidas as mensagens destinadas a este usuário nesta conversa
    $db->prepare("UPDATE mensagens_internas SET lida = 1 WHERE conversa_id = ? AND destinatario_id = ? AND lida = 0")
        ->execute([$conversa_id, $user_id]);
}

// Listar conversas do usuário
$stmt = $db->prepare("SELECT c.*, 
                      (SELECT COUNT(*) FROM mensagens_internas WHERE conversa_id = c.id AND destinatario_id = ? AND lida = 0) as nao_lidas,
                      (SELECT mensagem FROM mensagens_internas WHERE conversa_id = c.id ORDER BY data_envio DESC LIMIT 1) as ultima_mensagem
                      FROM conversas c
                      WHERE c.usuario_id = ?
                      ORDER BY c.ultima_atualizacao DESC");
$stmt->execute([$user_id, $user_id]);
$conversas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se uma conversa específica foi selecionada, carregar suas mensagens
$conversa_atual = null;
$mensagens = [];
if (isset($_GET['conversa'])) {
    $conversa_id = (int)$_GET['conversa'];
    // Verificar se pertence ao usuário
    $stmt = $db->prepare("SELECT * FROM conversas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$conversa_id, $user_id]);
    $conversa_atual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conversa_atual) {
        // Buscar mensagens da conversa
        $stmt = $db->prepare("SELECT mi.*, u.nome as remetente_nome 
                              FROM mensagens_internas mi
                              LEFT JOIN usuarios u ON mi.remetente_id = u.id
                              WHERE mi.conversa_id = ?
                              ORDER BY mi.data_envio ASC");
        $stmt->execute([$conversa_id]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Minhas Mensagens - SIBAM UNILUANDA</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --gold-color: #f1c40f;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--gold-color) !important;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: var(--light-color) !important;
            transform: scale(1.02);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: white !important;
            background-color: rgba(107, 101, 12, 0.2);
            transform: translateY(-2px);
        }
        
        .nav-link i {
            margin-right: 8px;
        }
        
        /* Estilos específicos para mensagens */
        .conversa-item {
            cursor: pointer;
            transition: background 0.2s;
        }
        .conversa-item:hover {
            background: #f8f9fa;
        }
        .conversa-item.active {
            background: #e7f1ff;
            border-left: 4px solid #0d6efd;
        }
        .mensagem-bubble {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 18px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }
        .mensagem-usuario {
            background-color: #007bff;
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .mensagem-admin {
            background-color: #e9ecef;
            color: #212529;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .chat-box {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background: #fff;
        }
        
        footer {
            background: var(--dark-color);
            color: var(--gold-color) !important;
            margin-top: auto;
        }
        .footer-links a {
            color: var(--gold-color) !important;
            text-decoration: none;
        }
        .footer-links a:hover {
            color: var(--light-color) !important;
            padding-left: 5px;
        }
        .social-icon {
            color: var(--gold-color) !important;
            font-size: 1.5rem;
            margin: 0 10px;
        }
        .social-icon:hover {
            color: var(--light-color) !important;
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4">Minhas Mensagens</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row">
            <!-- Lista de conversas -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Conversas</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (empty($conversas)): ?>
                            <div class="list-group-item text-center text-muted py-4">
                                Nenhuma conversa iniciada.
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversas as $conv): ?>
                                <a href="minhas_mensagens.php?conversa=<?php echo $conv['id']; ?>" 
                                   class="list-group-item list-group-item-action conversa-item <?php echo ($conversa_atual && $conversa_atual['id'] == $conv['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($conv['assunto'] ?: 'Sem assunto'); ?></strong>
                                        <?php if ($conv['nao_lidas'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?php echo $conv['nao_lidas']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="d-block text-muted">Início: <?php echo date('d/m/Y', strtotime($conv['data_inicio'])); ?></small>
                                    <small class="d-block text-truncate"><?php echo htmlspecialchars($conv['ultima_mensagem'] ?? 'Clique para ver'); ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Área de mensagens da conversa selecionada -->
            <div class="col-md-8">
                <?php if ($conversa_atual): ?>
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($conversa_atual['assunto'] ?: 'Conversa'); ?></h5>
                            <small>Com <?php echo htmlspecialchars($conversa_atual['nome_contato']); ?></small>
                        </div>
                        <div class="card-body">
                            <div class="chat-box" id="chatBox">
                                <?php foreach ($mensagens as $msg): ?>
                                    <?php
                                    $classe = ($msg['remetente_id'] == $user_id) ? 'mensagem-usuario' : 'mensagem-admin';
                                    $alinhamento = ($msg['remetente_id'] == $user_id) ? 'text-end' : 'text-start';
                                    ?>
                                    <div class="<?php echo $alinhamento; ?> mb-2">
                                        <div class="mensagem-bubble <?php echo $classe; ?> d-inline-block">
                                            <?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?>
                                            <small class="d-block opacity-75"><?php echo date('d/m H:i', strtotime($msg['data_envio'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Formulário de resposta -->
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="conversa_id" value="<?php echo $conversa_atual['id']; ?>">
                                <div class="input-group">
                                    <textarea class="form-control" name="mensagem" rows="2" placeholder="Digite sua mensagem..." required></textarea>
                                    <button class="btn btn-primary" type="submit" name="responder">Enviar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <script>
                        // Rolar para o final do chat
                        var chatBox = document.getElementById('chatBox');
                        chatBox.scrollTop = chatBox.scrollHeight;
                    </script>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x mb-3"></i>
                            <h5>Selecione uma conversa ao lado</h5>
                            <p>Ou aguarde o administrador responder à sua mensagem de contato.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>