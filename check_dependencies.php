<?php
// Incluir configuração primeiro - isso deve carregar todas as dependências
require_once 'includes/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    header("Location: home.php");
    exit();
}

// Agora criar instância da Auth - a classe já deve estar carregada via config.php
$auth = new Auth($db);
$message = '';

// Obter dados do usuário atual
$user = $auth->getUserData($_SESSION['user_id']);

if (!$user) {
    header("Location: home.php?error=user_not_found");
    exit();
}

// ... o resto do código permanece igual até o final do processamento POST ...
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Meu Perfil - SIBAM UNILUANDA</title>
    <style>
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #007bff;
        }
        .password-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Meu Perfil</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <img src="assets/images/avatar/<?php echo htmlspecialchars($user['foto'] ?? 'default.png'); ?>" 
                                         alt="Foto de Perfil" 
                                         class="avatar-preview mb-3" 
                                         id="avatarPreview"
                                         onerror="this.src='assets/images/avatar/default.png'">
                                    <div class="mb-3">
                                        <label for="foto" class="form-label">Alterar Foto</label>
                                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                        <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB</small>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">Nome Completo *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label">Tipo de Usuário</label>
                                        <input type="text" class="form-control" id="tipo" 
                                               value="<?php echo htmlspecialchars(ucfirst($user['tipo'])); ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_criacao" class="form-label">Data de Registro</label>
                                        <input type="text" class="form-control" id="data_criacao" 
                                               value="<?php echo formatDate($user['data_criacao']); ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seção de Alteração de Senha -->
                            <div class="password-section">
                                <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Alterar Senha</h5>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_password" class="form-label">Senha Atual</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                        <small class="form-text text-muted">Obrigatório apenas para alterar senha</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">Nova Senha</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="6">
                                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Atualizar Perfil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Estatísticas do Usuário -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Minhas Estatísticas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <?php
                                // Contar monografias do usuário
                                $monografiasQuery = "SELECT COUNT(*) as total FROM monografias WHERE autor_id = :user_id";
                                $monografiasStmt = $db->prepare($monografiasQuery);
                                $monografiasStmt->bindParam(':user_id', $_SESSION['user_id']);
                                $monografiasStmt->execute();
                                $totalMonografias = $monografiasStmt->fetch(PDO::FETCH_ASSOC)['total'];
                                ?>
                                <h3 class="text-primary"><?php echo $totalMonografias; ?></h3>
                                <p class="text-muted">Monografias</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php
                                // Contar monografias aprovadas
                                $aprovadasQuery = "SELECT COUNT(*) as total FROM monografias WHERE autor_id = :user_id AND status = 'aprovado'";
                                $aprovadasStmt = $db->prepare($aprovadasQuery);
                                $aprovadasStmt->bindParam(':user_id', $_SESSION['user_id']);
                                $aprovadasStmt->execute();
                                $totalAprovadas = $aprovadasStmt->fetch(PDO::FETCH_ASSOC)['total'];
                                ?>
                                <h3 class="text-success"><?php echo $totalAprovadas; ?></h3>
                                <p class="text-muted">Aprovadas</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php
                                // Contar monografias pendentes
                                $pendentesQuery = "SELECT COUNT(*) as total FROM monografias WHERE autor_id = :user_id AND status = 'pendente'";
                                $pendentesStmt = $db->prepare($pendentesQuery);
                                $pendentesStmt->bindParam(':user_id', $_SESSION['user_id']);
                                $pendentesStmt->execute();
                                $totalPendentes = $pendentesStmt->fetch(PDO::FETCH_ASSOC)['total'];
                                ?>
                                <h3 class="text-warning"><?php echo $totalPendentes; ?></h3>
                                <p class="text-muted">Pendentes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Preview da imagem antes do upload
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            const preview = document.getElementById('avatarPreview');
            
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // Validação de formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const currentPassword = document.getElementById('current_password').value;
        
        // Se preencheu nova senha, deve preencher confirmação e senha atual
        if (newPassword && (!confirmPassword || !currentPassword)) {
            e.preventDefault();
            alert('Para alterar a senha, é necessário preencher todos os campos de senha.');
            return false;
        }
        
        // Verificar se as senhas coincidem
        if (newPassword && newPassword !== confirmPassword) {
            e.preventDefault();
            alert('As novas senhas não coincidem.');
            return false;
        }
        
        // Verificar comprimento mínimo
        if (newPassword && newPassword.length < 6) {
            e.preventDefault();
            alert('A nova senha deve ter pelo menos 6 caracteres.');
            return false;
        }
    });
    </script>
</body>
</html>