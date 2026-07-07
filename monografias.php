<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

/*
FUNÇÃO DE LIMPEZA
*/
function limpar($v){
    return trim(strip_tags($v ?? ''));
}

/*
PAGINAÇÃO
*/
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = 8;
$offset = ($page - 1) * $limit;

/*
FILTROS
*/
$search     = limpar($_GET['search'] ?? '');
$area       = limpar($_GET['area'] ?? '');
$curso      = limpar($_GET['curso'] ?? '');
$tipo       = limpar($_GET['tipo'] ?? '');
$orientador = limpar($_GET['orientador'] ?? '');
$ano_ini    = limpar($_GET['ano_ini'] ?? '');
$ano_fim    = limpar($_GET['ano_fim'] ?? '');
$ordenar    = limpar($_GET['ordenar'] ?? 'data');

/*
QUERY BASE - Incluímos o nome do tutor (orientador)
*/
$sql = "
SELECT m.*, u.nome AS autor
FROM monografias m
LEFT JOIN usuarios u ON u.id = m.autor_id
WHERE 1=1
";

$params = [];

/*
BUSCA AVANÇADA (FULLTEXT)
*/
if ($search) {
    $sql .= " AND MATCH(m.titulo, m.resumo, m.palavras_chave)
              AGAINST (:search IN BOOLEAN MODE)";
    $params[':search'] = $search;
}

/*
FILTROS FACETADOS
*/
if ($area) {
    $sql .= " AND m.area = :area";
    $params[':area'] = $area;
}

if ($curso) {
    $sql .= " AND m.curso = :curso";
    $params[':curso'] = $curso;
}

if ($tipo) {
    $sql .= " AND m.tipo_documento = :tipo";
    $params[':tipo'] = $tipo;
}

if ($orientador) {
    $sql .= " AND m.orientador LIKE :orientador";
    $params[':orientador'] = "%$orientador%";
}

if ($ano_ini && $ano_fim) {
    $sql .= " AND YEAR(m.data_publicacao) BETWEEN :ai AND :af";
    $params[':ai'] = $ano_ini;
    $params[':af'] = $ano_fim;
}

/*
ORDENAÇÃO
*/
$sql .= ($ordenar === 'titulo')
      ? " ORDER BY m.titulo ASC"
      : " ORDER BY m.data_publicacao DESC";

/*
PAGINAÇÃO
*/
$sql .= " LIMIT :limit OFFSET :offset";

/*
EXECUÇÃO
*/
$stmt = $db->prepare($sql);

foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$monografias = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
TOTAL REGISTROS
*/
$total = $db->query("SELECT COUNT(*) FROM monografias")->fetchColumn();
$totalPages = ceil($total / $limit);

/*
DADOS PARA FILTROS
*/
$areas  = $db->query("SELECT DISTINCT area FROM monografias")->fetchAll(PDO::FETCH_COLUMN);
$cursos = $db->query("SELECT DISTINCT curso FROM monografias")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>SIBAM-UNILUANDA | Busca Avançada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        background: linear-gradient(rgba(28, 39, 50, 0.9), rgba(39, 55, 72, 0.9)), url('assets/images/ipgest.png');
        background-size: cover;
        background-position: center;
        color: #fff;
        padding: 5rem 0;
        margin-bottom: 5rem;
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
    
        .card:hover { transform: translateY(-4px); transition:.2s; 
    }
    </style>

</head>
<body>
    <div class="container py-5">

        <h2 class="mb-4 text-center">📚 Acervo Académico | UNILUANDA-IPGEST</h2>
        <hr>

        <!--
        FILTROS AVANÇADOS
        -->
        <form method="GET" class="card p-4 shadow-sm mb-4">
            <div class="row g-3">

                    <div class="col-md-4">
                    <input name="search" class="form-control" placeholder="Palavra-chave (título, resumo, autor)">
                </div>

                <div class="col-md-2">
                    <select name="area" class="form-select">
                        <option value="">Área</option>
                        <?php foreach($areas as $a): ?>
                        <option value="<?= $a ?>"><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="curso" class="form-select">
                        <option value="">Curso</option>
                        <?php foreach($cursos as $c): ?>
                        <option value="<?= $c ?>"><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <input type="number" name="ano_ini" class="form-control" placeholder="De">
                </div>

                <div class="col-md-1">
                    <input type="number" name="ano_fim" class="form-control" placeholder="Até">
                </div>

                <div class="col-md-3">
                    <input name="orientador" class="form-control" placeholder="Orientador">
                </div>

                <div class="col-md-3">
                    <select name="ordenar" class="form-select">
                        <option value="data">Mais recentes</option>
                        <option value="titulo">Título (A-Z)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success w-100">🔍 Filtrar</button>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-warning w-100">🔍 Limpar</button>
                </div>

            </div>
        </form>

        <!--
        RESULTADOS
        -->
        <p class="text-muted">Total de resultados: <strong><?= $total ?></strong></p>

        <div class="row">
            <?php foreach($monografias as $m): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($m['titulo']) ?></h5>
                        <p class="text-muted mb-1">
                            <?= $m['autor'] ?> | <?= $m['curso'] ?>
                        </p>
                        <!-- Nome do tutor (orientador) -->
                        <p class="text-muted mb-2">
                            <strong>Tutor:</strong> <?= htmlspecialchars($m['orientador'] ?? 'Não informado') ?>
                        </p>
                        <p><?= substr(strip_tags($m['resumo']),0,150) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <?= $m['area'] ?> | <?= date('Y', strtotime($m['data_publicacao'])) ?>
                            </small>
                            <!-- Botão Ver detalhes -->
                            <a href="detalhes_monografia.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">Ver detalhes</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!--
        PAGINAÇÃO
        -->
          <hr>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for($i=1;$i<=$totalPages;$i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
            </li>
                <?php endfor; ?>
            </ul>
        </nav>
      

    </div>
</body>
</html>