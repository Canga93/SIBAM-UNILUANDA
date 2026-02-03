<?php
// Relatório de Monografias
$query = "SELECT m.id, m.titulo, m.autor, m.orientador, m.status, m.data_submissao, 
                 u.username as submetido_por 
          FROM monografias m 
          LEFT JOIN users u ON m.user_id = u.id 
          WHERE m.data_submissao BETWEEN :start_date AND :end_date 
          ORDER BY m.data_submissao DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="report-content">
    <h4>Relatório de Monografias</h4>
    <p class="text-muted">Período: <?php echo date('d/m/Y', strtotime($start_date)); ?> a <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
    
    <div class="table-responsive mt-4">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Orientador</th>
                    <th>Status</th>
                    <th>Submetido por</th>
                    <th>Data de Submissão</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($monografias) > 0): ?>
                    <?php foreach ($monografias as $monografia): ?>
                        <tr>
                            <td><?php echo $monografia['id']; ?></td>
                            <td><?php echo htmlspecialchars($monografia['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($monografia['autor']); ?></td>
                            <td><?php echo htmlspecialchars($monografia['orientador']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    if ($monografia['status'] === 'approved') echo 'success';
                                    elseif ($monografia['status'] === 'pending') echo 'warning';
                                    else echo 'danger';
                                ?>">
                                    <?php echo ucfirst($monografia['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($monografia['submetido_por']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($monografia['data_submissao'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Nenhuma monografia encontrada no período selecionado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>