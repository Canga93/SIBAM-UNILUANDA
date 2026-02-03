<?php
// Relatório Financeiro
try {
    $query = "SELECT t.id, t.transaction_code, t.amount, t.status, t.created_at, 
                     u.username, u.email 
              FROM transactions t 
              LEFT JOIN users u ON t.user_id = u.id 
              WHERE t.created_at BETWEEN :start_date AND :end_date 
              ORDER BY t.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $transactions = [];
    $error = "Tabela de transações não encontrada ou erro na consulta.";
}
?>

<div class="report-content">
    <h4>Relatório Financeiro</h4>
    <p class="text-muted">Período: <?php echo date('d/m/Y', strtotime($start_date)); ?> a <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-warning">
            <?php echo $error; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive mt-4">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Usuário</th>
                        <th>Email</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo $transaction['id']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['transaction_code']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['email']); ?></td>
                                <td>€<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $transaction['status'] === 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Nenhuma transação encontrada no período selecionado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>