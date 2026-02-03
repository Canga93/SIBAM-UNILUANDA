<?php
// Relatório de Usuários
$query = "SELECT id, username, email, role, status, created_at 
          FROM users 
          WHERE created_at BETWEEN :start_date AND :end_date 
          ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="report-content">
    <h4>Relatório de Usuários</h4>
    <p class="text-muted">Período: <?php echo date('d/m/Y', strtotime($start_date)); ?> a <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
    
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Status</th>
                    <th>Data de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst($user['role']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum usuário encontrado no período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>