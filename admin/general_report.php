<?php
// Relatório Geral do Sistema
$stats = [];

// Estatísticas de usuários
$query = "SELECT COUNT(*) as total, 
                 SUM(status = 'active') as active,
                 SUM(status = 'inactive') as inactive,
                 SUM(role = 'admin') as admins
          FROM users";
$stmt = $db->prepare($query);
$stmt->execute();
$userStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Estatísticas de monografias
$query = "SELECT COUNT(*) as total,
                 SUM(status = 'approved') as approved,
                 SUM(status = 'pending') as pending,
                 SUM(status = 'rejected') as rejected
          FROM monografias";
$stmt = $db->prepare($query);
$stmt->execute();
$monografiaStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Estatísticas financeiras (se aplicável)
try {
    $query = "SELECT COUNT(*) as total_transactions,
                     SUM(amount) as total_amount,
                     SUM(status = 'paid') as paid,
                     SUM(status = 'pending') as pending_payments
              FROM transactions";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $financialStats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $financialStats = ['total_transactions' => 0, 'total_amount' => 0, 'paid' => 0, 'pending_payments' => 0];
}
?>

<div class="report-content">
    <h4>Relatório Geral do Sistema</h4>
    <p class="text-muted">Período: <?php echo date('d/m/Y', strtotime($start_date)); ?> a <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Usuários</h5>
                </div>
                <div class="card-body">
                    <h3><?php echo $userStats['total']; ?></h3>
                    <p class="mb-1">Ativos: <?php echo $userStats['active']; ?></p>
                    <p class="mb-1">Inativos: <?php echo $userStats['inactive']; ?></p>
                    <p class="mb-0">Administradores: <?php echo $userStats['admins']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-center mb-4">
                <div class="card-header bg-success text-white">
                    <h5>Monografias</h5>
                </div>
                <div class="card-body">
                    <h3><?php echo $monografiaStats['total']; ?></h3>
                    <p class="mb-1">Aprovadas: <?php echo $monografiaStats['approved']; ?></p>
                    <p class="mb-1">Pendentes: <?php echo $monografiaStats['pending']; ?></p>
                    <p class="mb-0">Rejeitadas: <?php echo $monografiaStats['rejected']; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card text-center mb-4">
                <div class="card-header bg-info text-white">
                    <h5>Financeiro</h5>
                </div>
                <div class="card-body">
                    <h3><?php echo $financialStats['total_transactions']; ?></h3>
                    <p class="mb-1">Total: €<?php echo number_format($financialStats['total_amount'], 2); ?></p>
                    <p class="mb-1">Pagas: <?php echo $financialStats['paid']; ?></p>
                    <p class="mb-0">Pendentes: <?php echo $financialStats['pending_payments']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>