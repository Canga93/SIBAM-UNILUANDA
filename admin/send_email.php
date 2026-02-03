<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

// Obter dados do formulário
$recipient_type = $_POST['recipient_type'] ?? 'all';
$selected_users = $_POST['selected_users'] ?? [];
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';
$include_stats = isset($_POST['include_stats']);

// Validar dados
if (empty($subject) || empty($message)) {
    header("Location: dashboard.php?email=error&message=Assunto e mensagem são obrigatórios");
    exit();
}

// Obter destinatários baseado no tipo
$recipients = [];
$query = "SELECT email, nome FROM usuarios WHERE 1=1";

switch ($recipient_type) {
    case 'admin':
        $query .= " AND tipo = 'admin'";
        break;
    case 'professor':
        $query .= " AND tipo = 'professor'";
        break;
    case 'estudante':
        $query .= " AND tipo = 'estudante'";
        break;
    case 'selected':
        if (!empty($selected_users)) {
            $placeholders = implode(',', array_fill(0, count($selected_users), '?'));
            $query .= " AND id IN ($placeholders)";
        } else {
            header("Location: dashboard.php?email=error&message=Nenhum usuário selecionado");
            exit();
        }
        break;
}

$stmt = $db->prepare($query);

if ($recipient_type === 'selected' && !empty($selected_users)) {
    $stmt->execute($selected_users);
} else {
    $stmt->execute();
}

$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($recipients)) {
    header("Location: dashboard.php?email=error&message=Nenhum destinatário encontrado");
    exit();
}

// Adicionar estatísticas se solicitado
if ($include_stats) {
    $statsQuery = "SELECT 
        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
        (SELECT COUNT(*) FROM monografias) as total_monografias,
        (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes,
        (SELECT COUNT(*) FROM monografias WHERE status = 'aprovado') as monografias_aprovadas";
    
    $statsStmt = $db->query($statsQuery);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    $message .= "\n\n--- ESTATÍSTICAS DO SISTEMA ---\n";
    $message .= "Total de Usuários: " . $stats['total_usuarios'] . "\n";
    $message .= "Total de Monografias: " . $stats['total_monografias'] . "\n";
    $message .= "Monografias Aprovadas: " . $stats['monografias_aprovadas'] . "\n";
    $message .= "Monografias Pendentes: " . $stats['monografias_pendentes'] . "\n";
}

// Enviar emails (em produção, integrar com sistema de email real)
$success_count = 0;
$error_count = 0;

foreach ($recipients as $recipient) {
    $to = $recipient['email'];
    $personalized_message = "Prezado(a) " . $recipient['nome'] . ",\n\n" . $message;
    
    $headers = "From: sibam@uniluanda.edu.ao\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Em produção, descomente a linha abaixo:
    // if (mail($to, $subject, $personalized_message, $headers)) {
    //     $success_count++;
    // } else {
    //     $error_count++;
    // }
    
    // Para demonstração, simular sucesso
    $success_count++;
}

// Registrar no log
$log_message = "Email enviado para $success_count destinatários. Tipo: $recipient_type";
$log_query = "INSERT INTO system_logs (user_id, action, details) VALUES (:user_id, 'email_sent', :details)";
$log_stmt = $db->prepare($log_query);
$log_stmt->bindParam(':user_id', $_SESSION['user_id']);
$log_stmt->bindParam(':details', $log_message);
$log_stmt->execute();

header("Location: dashboard.php?email=success&success_count=$success_count&error_count=$error_count");
exit();
?>