<?php
require_once '../includes/config.php';


$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Processar envio de resposta interna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['responder_interno'])) {
    $mensagem_original_id = (int)$_POST['mensagem_id'];
    $resposta_texto = trim($_POST['resposta']);
    
    if ($mensagem_original_id > 0 && !empty($resposta_texto)) {
        // Buscar dados da mensagem original
        $stmt = $db->prepare("SELECT * FROM mensagens WHERE id = ?");
        $stmt->execute([$mensagem_original_id]);
        $original = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($original) {
            // Tentar obter o usuario_id da mensagem original; se não existir,
            // procurar pelo email na tabela usuarios para ligar a conversa ao utilizador certo
            $usuario_id = $original['usuario_id'] ?? null;
            if (empty($usuario_id)) {
                $stmtUser = $db->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
                $stmtUser->execute([$original['email']]);
                $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
                if ($userRow) {
                    $usuario_id = $userRow['id'];
                }
            }

            // Verificar se já existe uma conversa para este email
            $stmt = $db->prepare("SELECT id, usuario_id FROM conversas WHERE email_contato = ? LIMIT 1");
            $stmt->execute([$original['email']]);
            $conversa = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$conversa) {
                // Criar nova conversa já associada ao utilizador (se encontrado)
                $stmt = $db->prepare("INSERT INTO conversas (usuario_id, email_contato, nome_contato, assunto, data_inicio) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$usuario_id, $original['email'], $original['nome'], $original['assunto']]);
                $conversa_id = $db->lastInsertId();
            } else {
                $conversa_id = $conversa['id'];
                // Se a conversa já existia mas ainda não estava ligada a um utilizador,
                // e agora conseguimos identificá-lo, associamos agora
                if (empty($conversa['usuario_id']) && !empty($usuario_id)) {
                    $db->prepare("UPDATE conversas SET usuario_id = ? WHERE id = ?")->execute([$usuario_id, $conversa_id]);
                }
            }
            
            // Inserir a resposta como nova mensagem na conversa
            $admin_id = $_SESSION['user_id'] ?? 0; // ID do admin logado
            $destinatario_id = $usuario_id;
            
            $stmt = $db->prepare("INSERT INTO mensagens_internas (conversa_id, remetente_id, destinatario_id, mensagem, data_envio, lida) VALUES (?, ?, ?, ?, NOW(), 0)");
            $stmt->execute([$conversa_id, $admin_id, $destinatario_id, $resposta_texto]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Resposta enviada com sucesso. O usuário verá em 'Minhas Mensagens'.";
            } else {
                $error = "Erro ao salvar resposta.";
            }
        } else {
            $error = "Mensagem original não encontrada.";
        }
    } else {
        $error = "Preencha a resposta.";
    }
}

// Processar outras ações (marcar lida, etc.)
if (isset($_GET['action'])) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($_GET['action'] == 'marcar_lida' && $id) {
        $stmt = $db->prepare("UPDATE mensagens SET status = 'lida' WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Mensagem marcada como lida.";
        } else {
            $error = "Erro ao atualizar.";
        }
    }
    
    elseif ($_GET['action'] == 'marcar_nao_lida' && $id) {
        $stmt = $db->prepare("UPDATE mensagens SET status = 'nao lida' WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Mensagem marcada como não lida.";
        } else {
            $error = "Erro ao atualizar.";
        }
    }
    
    elseif ($_GET['action'] == 'apagar' && $id) {
        $stmt = $db->prepare("DELETE FROM mensagens WHERE id = ?");
        if ($stmt->execute([$id])) {
            $message = "Mensagem apagada.";
        } else {
            $error = "Erro ao apagar.";
        }
    }
    
    elseif ($_GET['action'] == 'apagar_todas') {
        $db->exec("DELETE FROM mensagens");
        $message = "Todas as mensagens foram apagadas.";
    }
}

// Filtro de status
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'todas';
$where = '';
if ($filtro == 'lidas') {
    $where = "WHERE status = 'lida'";
} elseif ($filtro == 'nao_lidas') {
    $where = "WHERE status = 'nao lida'";
}

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$total = $db->query("SELECT COUNT(*) FROM mensagens $where")->fetchColumn();
$totalPages = ceil($total / $limit);

$mensagens = $db->query("SELECT * FROM mensagens $where ORDER BY data_envio DESC LIMIT $offset, $limit")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include '../includes/head.php'; ?>
    <title>Gerenciar Mensagens - Admin</title>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="p-4">
                    <h2 class="mb-4">Mensagens de Contato</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    
                    <!-- Filtros e ações em massa -->
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <a href="?filtro=todas" class="btn btn-sm <?php echo $filtro=='todas'?'btn-primary':'btn-outline-secondary'; ?>">Todas</a>
                            <a href="?filtro=lidas" class="btn btn-sm <?php echo $filtro=='lidas'?'btn-primary':'btn-outline-secondary'; ?>">Lidas</a>
                            <a href="?filtro=nao_lidas" class="btn btn-sm <?php echo $filtro=='nao_lidas'?'btn-primary':'btn-outline-secondary'; ?>">Não Lidas</a>
                        </div>
                        <div>
                            <a href="?action=apagar_todas" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja apagar TODAS as mensagens?')">Apagar Todas</a>
                        </div>
                    </div>
                    
                    <!-- Lista de mensagens -->
                    <?php if (count($mensagens) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Assunto</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensagens as $msg): ?>
                                    <tr class="<?php echo $msg['status'] == 'nao lida' ? 'table-warning' : ''; ?>">
                                        <td><?php echo $msg['id']; ?></td>
                                        <td><?php echo htmlspecialchars($msg['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['assunto']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?></td>
                                        <td>
                                            <?php if ($msg['status'] == 'lida'): ?>
                                                <span class="badge bg-success">Lida</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Não lida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <!-- Botão Visualizar -->
                                            <a href="#" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalMensagem<?php echo $msg['id']; ?>"><i class="fas fa-eye"></i></a>
                                            
                                            <!-- Botão Responder Interno -->
                                            <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalResponder<?php echo $msg['id']; ?>"><i class="fas fa-reply"></i></a>
                                            
                                            <!-- Marcar como lida/não lida -->
                                            <?php if ($msg['status'] == 'lida'): ?>
                                                <a href="?action=marcar_nao_lida&id=<?php echo $msg['id']; ?>&filtro=<?php echo $filtro; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-outline-warning" title="Marcar como não lida"><i class="fas fa-envelope"></i></a>
                                            <?php else: ?>
                                                <a href="?action=marcar_lida&id=<?php echo $msg['id']; ?>&filtro=<?php echo $filtro; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-outline-success" title="Marcar como lida"><i class="fas fa-check-circle"></i></a>
                                            <?php endif; ?>
                                            
                                            <!-- Apagar -->
                                            <a href="?action=apagar&id=<?php echo $msg['id']; ?>&filtro=<?php echo $filtro; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apagar esta mensagem?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal Visualizar Mensagem -->
                                    <div class="modal fade" id="modalMensagem<?php echo $msg['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Mensagem de <?php echo htmlspecialchars($msg['nome']); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></p>
                                                    <p><strong>Assunto:</strong> <?php echo htmlspecialchars($msg['assunto']); ?></p>
                                                    <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s', strtotime($msg['data_envio'])); ?></p>
                                                    <hr>
                                                    <p><strong>Mensagem:</strong></p>
                                                    <p><?php echo nl2br(htmlspecialchars($msg['mensagem'])); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal Responder Internamente -->
                                    <div class="modal fade" id="modalResponder<?php echo $msg['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Responder a <?php echo htmlspecialchars($msg['nome']); ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="mensagem_id" value="<?php echo $msg['id']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Sua resposta (será enviada internamente)</label>
                                                            <textarea class="form-control" name="resposta" rows="6" required placeholder="Digite sua resposta..."></textarea>
                                                        </div>
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle"></i> A resposta será armazenada no sistema e o usuário poderá visualizá-la em "Minhas Mensagens" após fazer login.
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" name="responder_interno" class="btn btn-primary">Enviar Resposta</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <?php if ($totalPages > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?filtro=<?php echo $filtro; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h4>Nenhuma mensagem encontrada</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>