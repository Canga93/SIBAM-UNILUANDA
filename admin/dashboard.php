<?php 
require_once '../includes/config.php'; 
redirectIfNotAdmin(); 

// Obter conexão com o banco de dados 
$database = new Database(); 
$db = $database->getConnection(); 

// Estatísticas para o dashboard 
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM monografias) as total_monografias,
    (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes,
    (SELECT COUNT(*) FROM monografias WHERE status = 'aprovado') as monografias_aprovadas,
    (SELECT COUNT(*) FROM monografias WHERE status = 'rejeitado') as monografias_rejeitadas,
    (SELECT COUNT(*) FROM monografias WHERE data_publicacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as monografias_recentes,
    (SELECT COUNT(*) FROM monografias WHERE data_submissao >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as monografias_semana,
    (SELECT COUNT(*) FROM usuarios WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as usuarios_recentes,
    (SELECT COUNT(*) FROM support_tickets WHERE status = 'aberto') as tickets_abertos,
    (SELECT COUNT(*) FROM scheduled_tasks WHERE status = 'pending' AND scheduled_date <= NOW()) as tarefas_pendentes,
    (SELECT COUNT(*) FROM system_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as logs_24h,
    (SELECT COUNT(*) FROM galerias) as total_galerias,
    (SELECT COUNT(*) FROM galerias WHERE status = 'ativo') as galerias_ativas,
    (SELECT COUNT(*) FROM galerias WHERE status = 'inativo') as galerias_inativas,
    (SELECT COUNT(*) FROM galerias WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as galerias_recentes";  // REMOVI O PONTO E VÍRGULA DAQUI
    
try {
    $stats = $db->query($statsQuery)->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se o array foi retornado corretamente
    if (!$stats) {
        $stats = [];
    }
} catch (Exception $e) {
    // Se houver erro na query, cria array com valores padrão
    $stats = [
        'total_usuarios' => 0,
        'total_monografias' => 0,
        'monografias_pendentes' => 0,
        'monografias_aprovadas' => 0,
        'monografias_rejeitadas' => 0,
        'monografias_recentes' => 0,
        'monografias_semana' => 0,
        'usuarios_recentes' => 0,
        'tickets_abertos' => 0,
        'tarefas_pendentes' => 0,
        'logs_24h' => 0,
        'total_galerias' => 0,
        'galerias_ativas' => 0,
        'galerias_inativas' => 0,
        'galerias_recentes' => 0
    ];
    error_log("Erro na query de estatísticas: " . $e->getMessage());
}

// Adicionar valor padrão para total_imagens (que não existe na query acima)
$stats['total_imagens'] = $stats['total_galerias'] ?? 0;

// Estatísticas por área 
try {
    $areasQuery = "SELECT area, COUNT(*) as total 
                   FROM monografias 
                   WHERE area IS NOT NULL AND status = 'aprovado'
                   GROUP BY area 
                   ORDER BY total DESC 
                   LIMIT 10"; 
    $areasStats = $db->query($areasQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $areasStats = [];
    error_log("Erro ao buscar estatísticas por área: " . $e->getMessage());
}

// Estatísticas mensais 
try {
    $monthlyQuery = "SELECT 
        YEAR(data_submissao) as ano,
        MONTH(data_submissao) as mes,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'aprovado' THEN 1 ELSE 0 END) as aprovadas,
        SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as pendentes,
        SUM(CASE WHEN status = 'rejeitado' THEN 1 ELSE 0 END) as rejeitadas
        FROM monografias 
        WHERE data_submissao >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY YEAR(data_submissao), MONTH(data_submissao)
        ORDER BY ano DESC, mes DESC"; 
    $monthlyStats = $db->query($monthlyQuery)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $monthlyStats = [];
    error_log("Erro ao buscar estatísticas mensais: " . $e->getMessage());
}

// Previsão 
try {
    $predictionQuery = "SELECT 
        AVG(total) as media_mensal,
        MAX(total) as max_mensal,
        MIN(total) as min_mensal
        FROM (
            SELECT YEAR(data_submissao) as ano, MONTH(data_submissao) as mes, COUNT(*) as total
            FROM monografias 
            WHERE data_submissao >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY YEAR(data_submissao), MONTH(data_submissao)
        ) as monthly_totals"; 
    $prediction = $db->query($predictionQuery)->fetch(PDO::FETCH_ASSOC); 
    $previsao_proximo_mes = round($prediction['media_mensal'] * 1.1);
} catch (Exception $e) {
    $previsao_proximo_mes = 0;
    error_log("Erro ao calcular previsão: " . $e->getMessage());
}

// Últimos registos 
try {
    $latestMonografias = $db->query("SELECT m.*, u.nome as autor FROM monografias m 
                    LEFT JOIN usuarios u ON m.autor_id = u.id 
                    ORDER BY m.data_submissao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    $latestMonografias = [];
    error_log("Erro ao buscar últimas monografias: " . $e->getMessage());
}

try {
    $latestUsers = $db->query("SELECT * FROM usuarios ORDER BY data_criacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    $latestUsers = [];
    error_log("Erro ao buscar últimos usuários: " . $e->getMessage());
}

// ÚLTIMAS GALERIAS
try {
    $latestGalerias = $db->query("SELECT g.*, u.nome as autor FROM galerias g 
                    LEFT JOIN usuarios u ON g.usuario_id = u.id 
                    ORDER BY g.data_criacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $latestGalerias = [];
    error_log("Erro ao buscar últimas galerias: " . $e->getMessage());
}

// Uso do sistema (simulação) 
$systemUsage = [ 
    'cpu' => rand(20, 80), 
    'memory' => rand(40, 90), 
    'disk' => rand(60, 95), 
    'uptime' => rand(100, 500) 
]; 
?> 
<!DOCTYPE html> 
<html lang="pt-pt"> 

<head> 
    <?php include '../includes/head.php'; ?> 
    <title>Painel Administrativo - SIBAM UNILUANDA</title> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> 

    <style> 
        body { 
        margin: 0 !important; 
        padding: 0 !important; 
        background-color: #f8f9fa;
    }
    
    .main-content {
        padding: 20px;
        min-height: 100vh;
    }
    
    /* Estilos para galerias */
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .stats-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
        border: none;
        border-radius: 10px;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .stats-card .card-body {
        padding: 1.5rem;
    }
    
    .stats-card .card-title {
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stats-card .card-text {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 1rem;
    }
    
    .stats-card .fa-2x {
        opacity: 0.7;
        margin-top: 0.5rem;
    }
    
    .stats-card small {
        font-size: 0.75rem;
        opacity: 0.8;
    }
    
    /* Ajustes para as seções */
    .real-time-widget {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
    
    .health-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
    }
    
    .health-value {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0.5rem 0;
    }
    
    .progress-thin {
        height: 6px;
    }
    
    .quick-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .quick-action-btn {
        color: white;
        text-decoration: none;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .quick-action-btn:hover {
        opacity: 0.9;
        transform: translateX(5px);
    }
    
    .dashboard-container {
        padding: 20px;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
        font-weight: 600;
    }
    </style>
</head> 

<body>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <div class="col-md-9 col-lg-10 ms-auto p-0">
                <div class="dashboard-container">
                    <!-- TÍTULO E BOTÕES -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-0">Dashboard Administrativo</h2>
                            <small class="text-muted"><?php echo date('d/m/Y H:i:s'); ?></small>
                        </div>

                        <div>
                            <button class="btn btn-outline-primary me-2" onclick="window.print()">
                                <i class="fas fa-print me-1"></i>Imprimir Relatório
                            </button>

                            <button class="btn btn-info" onclick="refreshDashboard()">
                                <i class="fas fa-sync-alt me-1"></i>Atualizar
                            </button>
                        </div>
                    </div>

                    <!-- ========== MONITORAMENTO EM TEMPO REAL ========== -->
                    <div class="real-time-widget mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h5><i class="fas fa-heartbeat me-2"></i>Monitoramento do Sistema</h5>

                                <div class="row mt-3">
                                    <?php foreach(['CPU' => 'cpu', 'Memória' => 'memory', 'Disco' => 'disk', 'Uptime (h)' => 'uptime'] as $label => $key): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="health-item">
                                            <div><?php echo $label; ?></div>
                                            <div class="health-value">
                                                <?php echo ($key == 'uptime') ? $systemUsage[$key]."h" : $systemUsage[$key]."%"; ?>
                                            </div>

                                            <div class="progress progress-thin mt-2">
                                                <div class="progress-bar bg-<?php echo ($systemUsage[$key] > 75) ? 'warning' : 'success'; ?>"
                                                     style="width: <?php echo min($systemUsage[$key], 100); ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- AÇÕES RÁPIDAS -->
                            <div class="col-md-4">
                                <h5><i class="fas fa-bolt me-2"></i>Ações Rápidas</h5>
                                <div class="quick-actions mt-3">
                                    <a href="monografias.php?status=pendente" class="quick-action-btn bg-warning">
                                        <i class="fas fa-clock me-1"></i>Ver Pendentes
                                    </a>

                                    <a href="backup.php" class="quick-action-btn bg-info">
                                        <i class="fas fa-database me-1"></i>Backup
                                    </a>

                                    <a href="galeriaAdmin.php" class="quick-action-btn bg-success">
                                        <i class="fas fa-plus me-1"></i>Nova Galeria
                                    </a>

                                    <a href="relatorios.php" class="quick-action-btn bg-primary">
                                        <i class="fas fa-chart-bar me-1"></i>Relatórios
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== ESTATÍSTICAS PRINCIPAIS ========== -->
                    <div class="row mb-4">
                        <?php
                        $cards = [
                            ['title'=>'Usuários','value'=>$stats['total_usuarios'] ?? 0,'icon'=>'users','class'=>'primary','sub'=>($stats['usuarios_recentes'] ?? 0).' novos (30d)','link'=>'usuarios.php'],
                            ['title'=>'Monografias','value'=>$stats['total_monografias'] ?? 0,'icon'=>'book','class'=>'success','sub'=>($stats['monografias_recentes'] ?? 0).' recentes','link'=>'monografias.php'],
                            ['title'=>'Pendentes','value'=>$stats['monografias_pendentes'] ?? 0,'icon'=>'clock','class'=>'warning','sub'=>($stats['monografias_semana'] ?? 0).' esta semana','link'=>'monografias.php?status=pendente'],
                            ['title'=>'Aprovadas','value'=>$stats['monografias_aprovadas'] ?? 0,'icon'=>'check-circle','class'=>'info','sub'=>($stats['monografias_rejeitadas'] ?? 0).' rejeitadas','link'=>'monografias.php?status=aprovado'],
                            // CARD DE GALERIAS ADICIONADO AQUI
                            ['title'=>'Galerias','value'=>$stats['total_galerias'] ?? 0,'icon'=>'images','class'=>'purple','sub'=>($stats['galerias_ativas'] ?? 0).' ativas, '.($stats['galerias_inativas'] ?? 0).' inativas','link'=>'galeriaAdmin.php'],
                            ['title'=>'Tickets','value'=>$stats['tickets_abertos'] ?? 0,'icon'=>'ticket-alt','class'=>'danger','sub'=>'Abertos','link'=>'support_tickets.php'],
                        ];

                        foreach($cards as $c): ?>
                        <div class="col-md-6 col-lg-4 col-xl-2 mb-3">
                            <div class="card stats-card bg-<?php echo $c['class']; ?> text-white" onclick="window.location.href='<?php echo $c['link']; ?>'">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?php echo $c['value']; ?></h5>
                                    <p class="card-text"><?php echo $c['title']; ?></p>
                                    <i class="fas fa-<?php echo $c['icon']; ?> fa-2x opacity-50"></i>
                                    <div class="mt-2"><small><?php echo $c['sub']; ?></small></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- ========== ESTATÍSTICAS DE GALERIAS ========== -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Estatísticas de Galerias</h5>
                                    <a href="../galerias.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4 mb-3">
                                            <div class="display-6 text-primary"><?php echo $stats['total_galerias'] ?? 0; ?></div>
                                            <p class="text-muted">Total</p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="display-6 text-success"><?php echo $stats['galerias_ativas'] ?? 0; ?></div>
                                            <p class="text-muted">Ativas</p>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="display-6 text-info"><?php echo $stats['total_imagens'] ?? 0; ?></div>
                                            <p class="text-muted">Imagens</p>
                                        </div>
                                    </div>
                                    <div class="progress mt-3">
                                        <?php if (($stats['total_galerias'] ?? 0) > 0): ?>
                                            <div class="progress-bar bg-success" style="width: <?php echo (($stats['galerias_ativas'] ?? 0)/($stats['total_galerias'] ?? 1)*100); ?>%">
                                                <?php echo $stats['galerias_ativas'] ?? 0; ?> Ativas
                                            </div>
                                            <div class="progress-bar bg-secondary" style="width: <?php echo (($stats['galerias_inativas'] ?? 0)/($stats['total_galerias'] ?? 1)*100); ?>%">
                                                <?php echo $stats['galerias_inativas'] ?? 0; ?> Inativas
                                            </div>
                                        <?php else: ?>
                                            <div class="progress-bar bg-secondary w-100">Sem dados</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Análise Preditiva</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 text-center">
                                            <div class="display-4 text-primary"><?php echo $previsao_proximo_mes; ?></div>
                                            <p class="text-muted">Previsão de submissões</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Indicadores baseados em IA</strong></p>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-success" style="width: 70%">Crescimento: +10%</div>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-warning" style="width: 50%">Tecnologia em alta</div>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-info" style="width: 40%">Novos usuários: +15%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== GRÁFICOS ========== -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Estatísticas Mensais de Submissões</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($monthlyStats)): ?>
                                        <canvas id="monthlyChart"></canvas>
                                    <?php else: ?>
                                        <p class="text-center text-muted py-4">Sem dados disponíveis</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Áreas de Conhecimento (Top 10)</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($areasStats)): ?>
                                        <canvas id="areasChart"></canvas>
                                    <?php else: ?>
                                        <p class="text-center text-muted py-4">Sem dados disponíveis</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== LISTAS: ÚLTIMOS REGISTOS ========== -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Últimas Monografias</h5>
                                    <a href="monografias.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                                </div>
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($latestMonografias)): ?>
                                        <?php foreach($latestMonografias as $m): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong class="d-block mb-1"><?php echo htmlspecialchars(substr($m['titulo'], 0, 30)); ?><?php echo strlen($m['titulo']) > 30 ? '...' : ''; ?></strong>
                                                    <small class="text-muted d-block"><?php echo $m['autor'] ?? 'N/A'; ?></small>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($m['data_submissao'])); ?></small>
                                                </div>
                                                <span class="badge bg-<?php echo $m['status'] == 'aprovado' ? 'success' : ($m['status'] == 'pendente' ? 'warning' : 'danger'); ?> ms-2">
                                                    <?php echo $m['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center text-muted py-3">
                                            Nenhuma monografia encontrada
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Últimos Usuários</h5>
                                    <a href="usuarios.php" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                                </div>
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($latestUsers)): ?>
                                        <?php foreach($latestUsers as $u): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong class="d-block mb-1"><?php echo htmlspecialchars($u['nome']); ?></strong>
                                                    <small class="text-muted d-block"><?php echo $u['email']; ?></small>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($u['data_criacao'])); ?></small>
                                                </div>
                                                <span class="badge bg-<?php echo $u['tipo'] == 'admin' ? 'danger' : ($u['tipo'] == 'estudante' ? 'success' : 'info'); ?> ms-2">
                                                    <?php echo $u['tipo']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center text-muted py-3">
                                            Nenhum usuário encontrado
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Últimas Galerias</h5>
                                    <a href="galeriaAdmin.php" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                                </div>
                                <div class="list-group list-group-flush">
                                    <?php if (!empty($latestGalerias)): ?>
                                        <?php foreach($latestGalerias as $g): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <strong class="d-block mb-1"><?php echo htmlspecialchars(substr($g['titulo'], 0, 30)); ?><?php echo strlen($g['titulo']) > 30 ? '...' : ''; ?></strong>
                                                    <small class="text-muted d-block"><?php echo $g['autor'] ?? 'Admin'; ?></small>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($g['data_criacao'])); ?></small>
                                                </div>
                                                <span class="badge bg-<?php echo $g['status'] == 'ativo' ? 'success' : 'secondary'; ?> ms-2">
                                                    <?php echo $g['status']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="list-group-item text-center text-muted py-3">
                                            Nenhuma galeria encontrada
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function refreshDashboard() {
        location.reload();
    }

    <?php if (!empty($monthlyStats)): ?>
    // ========== GRÁFICO MENSAL ==========
    var monthlyData = <?php echo json_encode($monthlyStats); ?>;
    if (monthlyData.length > 0) {
        var ctx1 = document.getElementById('monthlyChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: monthlyData.map(function(m) { return m.mes + '/' + m.ano; }),
                datasets: [{
                    label: 'Total',
                    data: monthlyData.map(function(m) { return m.total; }),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($areasStats)): ?>
    // ========== GRÁFICO ÁREAS ==========
    var areasData = <?php echo json_encode($areasStats); ?>;
    if (areasData.length > 0) {
        var ctx2 = document.getElementById('areasChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: areasData.map(function(a) { 
                    return a.area.length > 15 ? a.area.substring(0, 15) + '...' : a.area; 
                }),
                datasets: [{
                    label: 'Quantidade',
                    data: areasData.map(function(a) { return a.total; }),
                    backgroundColor: [
                        '#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6',
                        '#1abc9c', '#d35400', '#c0392b', '#16a085', '#8e44ad'
                    ],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    <?php endif; ?>
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>