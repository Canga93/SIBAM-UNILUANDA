<?php
require_once 'includes/config.php';

// Configuração de paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtros de busca
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$area = isset($_GET['area']) ? sanitizeInput($_GET['area']) : '';
$ano = isset($_GET['ano']) ? sanitizeInput($_GET['ano']) : '';

// Construir query
$query = "SELECT m.*, u.nome as autor FROM monografias m 
          LEFT JOIN usuarios u ON m.autor_id = u.id 
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (m.titulo LIKE :search OR m.resumo LIKE :search OR u.nome LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($area)) {
    $query .= " AND m.area = :area";
    $params[':area'] = $area;
}

if (!empty($ano)) {
    $query .= " AND YEAR(m.data_publicacao) = :ano";
    $params[':ano'] = $ano;
}

// Contar total de registros
$countQuery = "SELECT COUNT(*) as total FROM monografias m 
               LEFT JOIN usuarios u ON m.autor_id = u.id 
               WHERE 1=1";

if (!empty($search)) {
    $countQuery .= " AND (m.titulo LIKE :search OR m.resumo LIKE :search OR u.nome LIKE :search)";
}

if (!empty($area)) {
    $countQuery .= " AND m.area = :area";
}

if (!empty($ano)) {
    $countQuery .= " AND YEAR(m.data_publicacao) = :ano";
}

$stmt = $db->prepare($countQuery);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

if (!empty($area)) {
    $stmt->bindValue(':area', $area, PDO::PARAM_STR);
}

if (!empty($ano)) {
    $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
}

$stmt->execute();
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Adicionar paginação e ordenação
$query .= " ORDER BY m.data_publicacao DESC LIMIT :limit OFFSET :offset";

// Executar query
$stmt = $db->prepare($query);

// Bind dos parâmetros de busca
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

if (!empty($area)) {
    $stmt->bindValue(':area', $area, PDO::PARAM_STR);
}

if (!empty($ano)) {
    $stmt->bindValue(':ano', $ano, PDO::PARAM_INT);
}

// Bind dos parâmetros de paginação (importante especificar o tipo como INT)
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obter áreas distintas para filtro
$areasQuery = "SELECT DISTINCT area FROM monografias WHERE area IS NOT NULL ORDER BY area";
$areasStmt = $db->query($areasQuery);
$areas = $areasStmt->fetchAll(PDO::FETCH_COLUMN);

// Obter anos distintos para filtro
$anosQuery = "SELECT DISTINCT YEAR(data_publicacao) as ano FROM monografias 
              WHERE data_publicacao IS NOT NULL ORDER BY ano DESC";
$anosStmt = $db->query($anosQuery);
$anos = $anosStmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Monografias - SIBAM UNILUANDA</title>
    <style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #3498db;
        --accent-color: #e74c3c;
        --light-color: #ecf0f1;
        --dark-color: #2c3e50;
        --gold-color: #f1c40f;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        color: var(--dark-color);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    .navbar {
        background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 0.8rem 1rem;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--gold-color) !important;
        transition: all 0.3s ease;
    }
    
    .navbar-brand:hover {
        color: var(--light-color) !important;
        transform: scale(1.02);
    }
    
    .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        margin: 0 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover {
        color: white !important;
        background-color: rgba(107, 101, 12, 0.2);
        transform: translateY(-2px);
    }
    
    .nav-link i {
        margin-right: 8px;
    }
    
    .hero-section {
        background: linear-gradient(rgba(44, 62, 80, 0.9), rgba(44, 62, 80, 0.9)), url('assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 5rem 0;
        margin-bottom: 3rem;
    }
    
    .hero-title {
        font-weight: 700;
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .hero-lead {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }
    
    .btn-hero {
        padding: 0.8rem 2rem;
        font-weight: 600;
        border-radius: 50px;
        margin: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-hero:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }
    
    .feature-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        height: 100%;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        color: var(--secondary-color);
    }
    
    footer {
        background: var(--dark-color);
        color: var(--gold-color) !important;
        margin-top: auto;
    }
    
    .footer-links a {
        color: var(--gold-color) !important;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
        color: var(--light-color) !important;
        padding-left: 5px;
    }
    
    .social-icon {
        color: var(--gold-color) !important;
        font-size: 1.5rem;
        margin: 0 10px;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        color: var(--light-color) !important;
        transform: scale(1.2);
    }
    
    .user-avatar {
        border: 2px solid white;
        transition: all 0.3s ease;
    }
    
    .user-avatar:hover {
        transform: scale(1.1);
        border-color: var(--gold-color);
    }
    
    .dropdown-menu {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .dropdown-item {
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: var(--secondary-color);
        color: white;
        padding-left: 2rem;
    }
    
    .animate-delay-1 {
        animation-delay: 0.2s;
    }
    
    .animate-delay-2 {
        animation-delay: 0.4s;
    }
    
    .animate-delay-3 {
        animation-delay: 0.6s;
    }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 3px;
            border: none;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: none;
            border-radius: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            border-radius: 5px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
        }
        
        .card-title {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .card-text {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <h1 class="text-center mb-5">Acervo de Monografias</h1>
        
        <!-- Filtros -->
        <div class="card mb-4 filter-card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Busca</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Buscar por título, resumo ou autor..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="area">
                            <option value="">Todas as áreas</option>
                            <?php foreach ($areas as $areaOption): ?>
                                <option value="<?php echo $areaOption; ?>" <?php echo $area == $areaOption ? 'selected' : ''; ?>>
                                    <?php echo $areaOption; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="ano">
                            <option value="">Todos os anos</option>
                            <?php foreach ($anos as $anoOption): ?>
                                <option value="<?php echo $anoOption; ?>" <?php echo $ano == $anoOption ? 'selected' : ''; ?>>
                                    <?php echo $anoOption; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Buscar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="mb-4">
            <p class="text-muted">Encontradas <?php echo $totalRecords; ?> monografia(s)</p>
        </div>
        
        <!-- Lista de Monografias -->
        <?php if (count($monografias) > 0): ?>
            <div class="row">
                <?php foreach ($monografias as $monografia): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($monografia['titulo']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">Por: <?php echo htmlspecialchars($monografia['autor']); ?></h6>
                                <p class="card-text"><?php echo substr(htmlspecialchars($monografia['resumo']), 0, 150); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i><?php echo formatDate($monografia['data_publicacao']); ?>
                                        <i class="fas fa-tag ms-3 me-1"></i><?php echo htmlspecialchars($monografia['area']); ?>
                                    </small>
                                    <a href="detalhes_monografia.php?id=<?php echo $monografia['id']; ?>" class="btn btn-sm btn-outline-primary">Ver Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Anterior</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Próxima</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Nenhuma monografia encontrada</h4>
                <p class="text-muted">Tente ajustar os filtros de busca</p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>