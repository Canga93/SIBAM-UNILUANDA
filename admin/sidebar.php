<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<style> 
    body {
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden;
        height: 100%;
    }
    
    /* Sidebar fixo sem margens */
    #sidebar {
        margin: 0 !important;
        padding: 0 !important;
        left: 0;
        top: 0;
        bottom: 0;
        width: 250px; /* Largura fixa */
        height: 100vh;
        position: fixed;
        overflow-y: auto;
        z-index: 1000;
        border-radius: 0 !important;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    /* Conteúdo do sidebar sem margens */
    #sidebar .p-3 {
        padding: 1rem !important;
        margin: 0 !important;
        height: 100%;
    }
    
    /* Links do sidebar */
    #sidebar .nav-link {
        padding: 0.75rem 1rem;
        margin: 0.25rem 0;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    #sidebar .nav-link:hover {
        background-color: rgba(163, 147, 6, 0.1);
        transform: translateX(5px);
    }
    
    #sidebar .nav-link.active {
        background-color: #007bff;
    }
    
    /* Conteúdo principal deve ter margem à esquerda igual à largura do sidebar */
    .main-content {
        margin-left: 250px;
        padding: 0;
        width: calc(100% - 250px);
        min-height: 100vh;
        background-color: #f8f9fa;
    }
    
    /* Ajuste para telas pequenas */
    @media (max-width: 768px) {
        #sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        
        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }
    
    /* Remover qualquer espaçamento extra */
    * {
        box-sizing: border-box;
    }
    
    /* Ajustes específicos para elementos dentro do sidebar */
    #sidebar h5 {
        margin-top: 0;
        padding-top: 1rem;
    }
    
    #sidebar .nav {
        margin: 0;
        padding: 0;
    }
    
    #sidebar .nav-item {
        margin: 0;
        padding: 0;
    }
    </style>
<body>
    <div class="col-md-3 col-lg-2 bg-dark text-white vh-100 position-fixed" id="sidebar">
    <div class="p-3">
        <h5 class="text-center mb-4">Painel Administrativo</h5>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="monografias.php" class="nav-link text-white">
                    <i class="fas fa-book me-2"></i>Monografias
                </a>
            </li>
            <li class="nav-item">
                <a href="usuarios.php" class="nav-link text-white">
                    <i class="fas fa-users me-2"></i>Usuários
                </a>
            </li>
            <li class="nav-item">
                <a href="relatorios.php" class="nav-link text-white">
                    <i class="fas fa-chart-bar me-2"></i>Relatórios
                </a>
            </li>
            <li class="nav-item">
                <a href="configuracoes.php" class="nav-link text-white">
                    <i class="fas fa-cog me-2"></i>Configurações
                </a>
            </li>
            <li class="nav-item">
                <a href="backup.php" class="nav-link text-white">
                    <i class="fas fa-database me-2"></i>Backup
                </a>
            </li>
            <li class="nav-item">
                <a href="../galerias.php" class="nav-link text-white">
                    <i class="fas fa-database me-2"></i>Galerias
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="../home.php" class="nav-link text-white">
                    <i class="fas fa-arrow-left me-2"></i>Voltar ao Site
                </a>
            </li>
        </ul>
        
        <div class="mt-5 pt-4 border-top">
            <small class="text-muted">Usuário: <?php echo htmlspecialchars($_SESSION['user_nome']); ?></small>
            <br>
            <small class="text-muted">Tipo: <?php echo ucfirst($_SESSION['user_tipo']); ?></small>
            <br>
            <small class="text-muted"><?php echo date('d/m/Y H:i'); ?></small>
        </div>
    </div>
</div>

</body>
</html>