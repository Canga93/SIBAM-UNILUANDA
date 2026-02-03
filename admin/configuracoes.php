<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

$message = '';

// Processar atualização de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Em um sistema real, isso seria salvo em uma tabela de configurações no banco de dados
    // Por simplicidade, estamos apenas exibindo uma mensagem de sucesso
    $message = '<div class="alert alert-success">Configurações atualizadas com sucesso!</div>';
}

// Configurações padrão (em um sistema real, isso viria do banco de dados)
$configuracoes = [
    'site_name' => 'SIBAM - UNILUANDA',
    'site_email' => 'sibam@uniluanda.edu.ao',
    'items_per_page' => 10,
    'allow_registration' => true,
    'require_approval' => true,
    'max_file_size' => 10,
    'allowed_file_types' => ['pdf', 'doc', 'docx']
];

?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include '../includes/head.php'; ?>
    <title>Configurações - Painel Administrativo</title>
</head>
<body>
    
    
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="p-4">
                    <h2 class="mb-4">Configurações do Sistema</h2>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Configurações Gerais</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="site_name" class="form-label">Nome do Site</label>
                                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                                   value="<?php echo htmlspecialchars($configuracoes['site_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="site_email" class="form-label">Email do Site</label>
                                            <input type="email" class="form-control" id="site_email" name="site_email" 
                                                   value="<?php echo htmlspecialchars($configuracoes['site_email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label">Itens por Página</label>
                                            <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                                   value="<?php echo htmlspecialchars($configuracoes['items_per_page']); ?>" min="5" max="50" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Configurações de Registro</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" 
                                                       <?php echo $configuracoes['allow_registration'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="allow_registration">Permitir novo registro de usuários</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="require_approval" name="require_approval" 
                                                       <?php echo $configuracoes['require_approval'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="require_approval">Exigir aprovação para monografias</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Configurações de Upload</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="max_file_size" class="form-label">Tamanho Máximo de Arquivo (MB)</label>
                                            <input type="number" class="form-control" id="max_file_size" name="max_file_size" 
                                                   value="<?php echo htmlspecialchars($configuracoes['max_file_size']); ?>" min="1" max="50" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tipos de Arquivo Permitidos</label>
                                            <div>
                                                <?php foreach (['pdf', 'doc', 'docx'] as $type): ?>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" id="file_type_<?php echo $type; ?>" 
                                                               name="allowed_file_types[]" value="<?php echo $type; ?>"
                                                               <?php echo in_array($type, $configuracoes['allowed_file_types']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="file_type_<?php echo $type; ?>">
                                                            .<?php echo strtoupper($type); ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Salvar Configurações</button>
                        </div>
                    </form>
                    
                    <div class="row mt-5">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Estatísticas do Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    // Estatísticas
                                    $statsQuery = "SELECT 
                                        (SELECT COUNT(*) FROM usuarios) as total_usuarios,
                                        (SELECT COUNT(*) FROM monografias) as total_monografias,
                                        (SELECT COUNT(*) FROM monografias WHERE status = 'pendente') as monografias_pendentes,
                                        (SELECT COUNT(*) FROM monografias WHERE data_publicacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as monografias_recentes";
                                    
                                    $statsStmt = $db->query($statsQuery);
                                    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total de Usuários
                                            <span class="badge bg-primary rounded-pill"><?php echo $stats['total_usuarios']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total de Monografias
                                            <span class="badge bg-success rounded-pill"><?php echo $stats['total_monografias']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Monografias Pendentes
                                            <span class="badge bg-warning rounded-pill"><?php echo $stats['monografias_pendentes']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Monografias Recentes (30 dias)
                                            <span class="badge bg-info rounded-pill"><?php echo $stats['monografias_recentes']; ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Ações Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="monografias.php?status=pendente" class="btn btn-warning">
                                            <i class="fas fa-clock me-2"></i>Ver Monografias Pendentes
                                        </a>
                                        <a href="usuarios.php" class="btn btn-primary">
                                            <i class="fas fa-users me-2"></i>Gerenciar Usuários
                                        </a>
                                        <a href="../monografias.php" class="btn btn-success" target="_blank">
                                            <i class="fas fa-eye me-2"></i>Ver Site Público
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>