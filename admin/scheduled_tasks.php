<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

$stmt = $pdo->query("SELECT * FROM scheduled_tasks ORDER BY scheduled_date ASC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <title>Tarefas Agendadas</title>
    <?php include '../includes/head.php'; ?>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container mt-4">
    <h3>Tarefas Agendadas</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Título</th><th>Descrição</th><th>Data</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['title']); ?></td>
                <td><?= htmlspecialchars($t['description']); ?></td>
                <td><?= $t['scheduled_date']; ?></td>
                <td><?= htmlspecialchars($t['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
