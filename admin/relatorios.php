<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

// Garantir exceções no PDO
if (method_exists($db, 'setAttribute')) {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

// Datas e tipo de relatório
$start_date  = $_GET['start_date'] ?? date('Y-m-01');
$end_date    = $_GET['end_date']   ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'general';
$export      = $_GET['export'] ?? ''; // 'csv' para exportar

// Sanitização simples de datas
$start_date = preg_replace('/[^0-9\-]/', '', $start_date);
$end_date   = preg_replace('/[^0-9\-]/', '', $end_date);

// ---------- Funções utilitárias ----------
function csv_output($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    $output = fopen('php://output', 'w');
    // BOM para Excel reconhecer UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, $headers);
    foreach ($rows as $r) {
        // Garantir ordem dos headers
        $line = [];
        foreach ($headers as $h) {
            $line[] = isset($r[$h]) ? $r[$h] : '';
        }
        fputcsv($output, $line);
    }
    fclose($output);
    exit();
}

function tableExists(PDO $db, $table) {
    try {
        $db->query("SELECT 1 FROM {$table} LIMIT 1");
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

// ---------- KPIs (usados no relatório geral) ----------
$kpis = [
    'usuarios_total'      => 0,
    'monografias_total'   => 0,
    'novos_usuarios'      => 0,
    'novas_monografias'   => 0,
];
try {
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
    $kpis['usuarios_total'] = (int)$stmt->fetchColumn();

    if (tableExists($db, 'monografias')) {
        $stmt = $db->query("SELECT COUNT(*) FROM monografias");
        $kpis['monografias_total'] = (int)$stmt->fetchColumn();
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE DATE(data_criacao) BETWEEN :ini AND :fim");
    $stmt->execute([':ini'=>$start_date, ':fim'=>$end_date]);
    $kpis['novos_usuarios'] = (int)$stmt->fetchColumn();

    if (tableExists($db, 'monografias')) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM monografias WHERE DATE(data_submissao) BETWEEN :ini AND :fim");
        $stmt->execute([':ini'=>$start_date, ':fim'=>$end_date]);
        $kpis['novas_monografias'] = (int)$stmt->fetchColumn();
    }
} catch (Throwable $e) {
    // KPIs opcionais, não travar a página
}

// ---------- Dados do relatório ----------
$columns = [];
$rows = [];
$error_financeiro = '';

try {
    switch ($report_type) {
        case 'users':
            $columns = ['ID','Nome','Email','Tipo','Data de Criação'];
            $sql = "SELECT id AS `ID`, nome AS `Nome`, email AS `Email`, tipo AS `Tipo`, 
                           DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i') AS `Data de Criação`
                    FROM usuarios
                    WHERE DATE(data_criacao) BETWEEN :ini AND :fim
                    ORDER BY data_criacao DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':ini'=>$start_date, ':fim'=>$end_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'monografias':
            if (!tableExists($db, 'monografias')) {
                $columns = ['Aviso'];
                $rows = [['Aviso' => 'A tabela "monografias" não foi encontrada.']];
                break;
            }
            $columns = ['ID','Título','Autor','Status','Data de Submissão'];
            $sql = "SELECT m.id AS `ID`,
                           m.titulo AS `Título`,
                           u.nome AS `Autor`,
                           COALESCE(m.status,'-') AS `Status`,
                           DATE_FORMAT(m.data_submissao, '%d/%m/%Y %H:%i') AS `Data de Submissão`
                    FROM monografias m
                    LEFT JOIN usuarios u ON u.id = m.autor_id
                    WHERE DATE(m.data_submissao) BETWEEN :ini AND :fim
                    ORDER BY m.data_submissao DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':ini'=>$start_date, ':fim'=>$end_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'financial':
            if (!tableExists($db, 'pagamentos')) {
                // Ajuste aqui se sua tabela tiver outro nome/colunas
                $columns = ['Aviso'];
                $rows = [['Aviso' => 'A tabela "pagamentos" não foi encontrada. Ajuste o nome/colunas no código.']];
                $error_financeiro = 'Tabela de pagamentos ausente.';
                break;
            }
            $columns = ['ID','Usuário','Valor','Status','Data do Pagamento'];
            $sql = "SELECT p.id AS `ID`,
                           COALESCE(u.nome, CONCAT('Usuário #', p.usuario_id)) AS `Usuário`,
                           FORMAT(p.valor, 2, 'de_DE') AS `Valor`,
                           COALESCE(p.status,'-') AS `Status`,
                           DATE_FORMAT(p.data_pagamento, '%d/%m/%Y %H:%i') AS `Data do Pagamento`
                    FROM pagamentos p
                    LEFT JOIN usuarios u ON u.id = p.usuario_id
                    WHERE DATE(p.data_pagamento) BETWEEN :ini AND :fim
                    ORDER BY p.data_pagamento DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':ini'=>$start_date, ':fim'=>$end_date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'general':
        default:
            // Atividade geral (logs simples de eventos do período)
            $columns = ['Data/Hora','Tipo','Descrição'];

            $parts = [];
            $params = [':ini'=>$start_date, ':fim'=>$end_date];

            // Eventos de usuários
            $parts[] = "SELECT data_criacao AS data_evt, 'Usuário' AS tipo_evt, CONCAT('Novo usuário: ', nome) AS desc_evt
                        FROM usuarios
                        WHERE DATE(data_criacao) BETWEEN :ini AND :fim";

            // Eventos de monografias (se existir)
            if (tableExists($db, 'monografias')) {
                $parts[] = "SELECT data_submissao AS data_evt, 'Monografia' AS tipo_evt, CONCAT('Submissão: ', titulo) AS desc_evt
                            FROM monografias
                            WHERE DATE(data_submissao) BETWEEN :ini AND :fim";
            }

            $union = implode(" UNION ALL ", $parts);
            if (!empty($union)) {
                $sql = "SELECT DATE_FORMAT(data_evt, '%d/%m/%Y %H:%i') AS `Data/Hora`,
                               tipo_evt AS `Tipo`,
                               desc_evt AS `Descrição`
                        FROM (
                            $union
                        ) x
                        ORDER BY data_evt DESC
                        LIMIT 200";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $rows = [];
            }
            break;
    }
} catch (Throwable $e) {
    $columns = ['Erro'];
    $rows = [['Erro' => 'Falha ao gerar relatório: '.$e->getMessage()]];
}

// ---------- Exportação CSV ----------
if ($export === 'csv') {
    $filename = "relatorio_{$report_type}_{$start_date}_{$end_date}.csv";
    // Se não houver colunas/dados, criar cabeçalhos genéricos
    if (empty($columns) && !empty($rows)) {
        $columns = array_keys($rows[0]);
    }
    csv_output($filename, $columns, $rows);
}

// ---------- Renderização HTML começa aqui ----------
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include '../includes/head.php'; ?>
    <title>Relatórios - Painel Administrativo</title>
    <style>
        @media print {
            .no-print { display:none !important; }
            .card { box-shadow:none !important; }
        }
    </style>
</head>
<body>
    
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="p-4">
                    <h2 class="mb-4">Relatórios do Sistema</h2>
                    
                    <!-- Filtros de Relatório -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Filtros do Relatório</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <label for="report_type" class="form-label">Tipo de Relatório</label>
                                    <select class="form-select" id="report_type" name="report_type">
                                        <option value="general"     <?php echo $report_type === 'general' ? 'selected' : ''; ?>>Geral</option>
                                        <option value="users"       <?php echo $report_type === 'users' ? 'selected' : ''; ?>>Usuários</option>
                                        <option value="monografias" <?php echo $report_type === 'monografias' ? 'selected' : ''; ?>>Monografias</option>
                                        <option value="financial"   <?php echo $report_type === 'financial' ? 'selected' : ''; ?>>Financeiro</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label">Data Inicial</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">Data Final</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-sync-alt me-1"></i>Gerar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($report_type === 'general'): ?>
                    <!-- KPIs (apenas no geral) -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h4 class="mb-1"><?php echo number_format($kpis['usuarios_total']); ?></h4>
                                    <small>Total de Usuários</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h4 class="mb-1"><?php echo number_format($kpis['monografias_total']); ?></h4>
                                    <small>Total de Monografias</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h4 class="mb-1"><?php echo number_format($kpis['novos_usuarios']); ?></h4>
                                    <small>Novos Usuários (período)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h4 class="mb-1"><?php echo number_format($kpis['novas_monografias']); ?></h4>
                                    <small>Novas Monografias (período)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Conteúdo do Relatório -->
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Relatório do Sistema</h5>
                            <div class="no-print">
                                <button class="btn btn-sm btn-outline-primary me-2" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i>Imprimir
                                </button>
                                <button class="btn btn-sm btn-success" id="btnExport">
                                    <i class="fas fa-file-excel me-1"></i>Exportar Excel
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            // PRIORIDADE 1: se existir um arquivo em reports/<tipo>_report.php, usa-o
                            $report_file = __DIR__ . "/reports/{$report_type}_report.php";
                            if (file_exists($report_file)) {
                                include $report_file;
                            } else {
                                // Fallback: renderização inline
                                if (!empty($error_financeiro)) {
                                    echo '<div class="alert alert-warning">'.$error_financeiro.'</div>';
                                }
                                if (empty($columns)) {
                                    echo '<div class="alert alert-secondary">Nenhum dado disponível para o período selecionado.</div>';
                                } else {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-striped table-bordered align-middle">';
                                    echo '<thead><tr>';
                                    foreach ($columns as $col) {
                                        echo '<th>'.htmlspecialchars($col).'</th>';
                                    }
                                    echo '</tr></thead><tbody>';
                                    if (empty($rows)) {
                                        echo '<tr><td colspan="'.count($columns).'" class="text-center text-muted">Nenhum registro encontrado.</td></tr>';
                                    } else {
                                        foreach ($rows as $r) {
                                            echo '<tr>';
                                            foreach ($columns as $col) {
                                                echo '<td>'.htmlspecialchars($r[$col] ?? '').'</td>';
                                            }
                                            echo '</tr>';
                                        }
                                    }
                                    echo '</tbody></table></div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById('btnExport').addEventListener('click', function() {
    // Mantém filtros e adiciona export=csv
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location = window.location.pathname + '?' + params.toString();
});
</script>
</body>
</html>
