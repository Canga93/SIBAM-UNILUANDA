<?php
require_once '../includes/config.php';
redirectIfNotAdmin();

class BackupSystem {
    private $conn;
    private $backup_path;
    
    public function __construct($db, $backup_path = '../backups/') {
        $this->conn = $db;
        $this->backup_path = $backup_path;
        
        // Criar diretório de backups se não existir
        if (!file_exists($this->backup_path)) {
            mkdir($this->backup_path, 0777, true);
        }
    }
    
    // Criar backup do banco de dados
    public function create_database_backup() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = $this->backup_path . $filename;
            
            // Obter todas as tabelas
            $tables = $this->conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            $backup_content = "";
            
            foreach ($tables as $table) {
                // Obter estrutura da tabela
                $create_table = $this->conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
                $backup_content .= $create_table['Create Table'] . ";\n\n";
                
                // Obter dados da tabela
                $data = $this->conn->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($data)) {
                    $backup_content .= "INSERT INTO $table VALUES ";
                    
                    $rows = [];
                    foreach ($data as $row) {
                        $values = array_map(function($value) {
                            if ($value === null) return 'NULL';
                            return "'" . addslashes($value) . "'";
                        }, $row);
                        
                        $rows[] = "(" . implode(', ', $values) . ")";
                    }
                    
                    $backup_content .= implode(",\n", $rows) . ";\n\n";
                }
            }
            
            // Salvar arquivo
            if (file_put_contents($filepath, $backup_content)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Falha ao salvar arquivo de backup'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Restaurar backup
    public function restore_database_backup($filepath) {
        try {
            if (!file_exists($filepath)) {
                return ['success' => false, 'error' => 'Arquivo de backup não encontrado'];
            }
            
            $sql = file_get_contents($filepath);
            
            // Desativar chaves estrangeiras temporariamente
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Executar queries
            $this->conn->exec($sql);
            
            // Reativar chaves estrangeiras
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Listar backups disponíveis
    public function list_backups() {
        $backups = [];
        $files = glob($this->backup_path . 'backup_*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file)
            ];
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($backups, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        return $backups;
    }
    
    // Excluir backup
    public function delete_backup($filename) {
        $filepath = $this->backup_path . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}

// Processar ações de backup
$backup_system = new BackupSystem($db);
$message = '';

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_backup':
            $result = $backup_system->create_database_backup();
            if ($result['success']) {
                $message = '<div class="alert alert-success">Backup criado com sucesso: ' . $result['filename'] . '</div>';
            } else {
                $message = '<div class="alert alert-danger">Erro ao criar backup: ' . $result['error'] . '</div>';
            }
            break;
            
        case 'restore_backup':
            if (!empty($_POST['backup_file'])) {
                $result = $backup_system->restore_database_backup($this->backup_path . $_POST['backup_file']);
                if ($result['success']) {
                    $message = '<div class="alert alert-success">Backup restaurado com sucesso!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erro ao restaurar backup: ' . $result['error'] . '</div>';
                }
            }
            break;
            
        case 'delete_backup':
            if (!empty($_POST['backup_file'])) {
                if ($backup_system->delete_backup($_POST['backup_file'])) {
                    $message = '<div class="alert alert-success">Backup excluído com sucesso!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erro ao excluir backup!</div>';
                }
            }
            break;
    }
}

$backups = $backup_system->list_backups();
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include '../includes/head.php'; ?>
    <title>Backup do Sistema - Painel Administrativo</title>
</head>
<body>
    
    
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-auto">
                <div class="p-4">
                    <h2 class="mb-4">Sistema de Backup</h2>
                    
                    <?php echo $message; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Criar Backup</h5>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Crie um backup completo do banco de dados do sistema.</p>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="create_backup">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-database me-2"></i>Criar Backup Agora
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Informações do Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Total de Backups
                                            <span class="badge bg-primary rounded-pill"><?php echo count($backups); ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Espaço em Disco
                                            <span class="badge bg-info rounded-pill">
                                                <?php echo round(disk_free_space('/') / (1024 * 1024 * 1024), 2); ?> GB livre
                                            </span>
                                        </li>
                                        <li class="list-group-item">
                                            <small class="text-muted">Último backup: 
                                                <?php echo count($backups) > 0 ? date('d/m/Y H:i', $backups[0]['modified']) : 'Nenhum'; ?>
                                            </small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Backups Disponíveis</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($backups) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Arquivo</th>
                                                <th>Tamanho</th>
                                                <th>Data</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($backups as $backup): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($backup['filename']); ?></td>
                                                    <td><?php echo round($backup['size'] / 1024, 2); ?> KB</td>
                                                    <td><?php echo date('d/m/Y H:i', $backup['modified']); ?></td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="restore_backup">
                                                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                                        onclick="return confirm('Tem certeza que deseja restaurar este backup? Todos os dados atuais serão substituídos.')">
                                                                    <i class="fas fa-undo me-1"></i>Restaurar
                                                                </button>
                                                            </form>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="delete_backup">
                                                                <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['filename']); ?>">
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                        onclick="return confirm('Tem certeza que deseja excluir este backup?')">
                                                                    <i class="fas fa-trash me-1"></i>Excluir
                                                                </button>
                                                            </form>
                                                            <a href="<?php echo $backup['filepath']; ?>" 
                                                               class="btn btn-sm btn-outline-primary"
                                                               download>
                                                                <i class="fas fa-download me-1"></i>Download
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Nenhum backup disponível</h4>
                                    <p class="text-muted">Crie seu primeiro backup usando o botão acima.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                        <ul class="mb-0">
                            <li>Faça backups regularmente para prevenir perda de dados</li>
                            <li>Armazene os backups em local seguro</li>
                            <li>Teste periodicamente a restauração dos backups</li>
                            <li>O sistema não faz backup automático - é responsabilidade do administrador</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</body>
</html>