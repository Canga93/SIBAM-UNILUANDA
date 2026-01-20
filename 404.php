<?php
http_response_code(404);
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Página Não Encontrada - SIBAM UNILUANDA</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="error-page">
                    <h1 class="display-1 text-primary">404</h1>
                    <h2 class="mb-4">Página Não Encontrada</h2>
                    <p class="lead mb-5">A página que você está procurando não existe ou foi movida.</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="home.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Página Inicial
                        </a>
                        <a href="monografias.php" class="btn btn-outline-primary">
                            <i class="fas fa-book me-2"></i>Ver Monografias
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Voltar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>