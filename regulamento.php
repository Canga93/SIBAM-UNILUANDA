<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Regulamento - SIBAM UNILUANDA</title>
</head>
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
</style>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="mb-3">Regulamento do SIBAM</h1>
                    <p class="lead">Normas e diretrizes para uso do sistema</p>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">1. Disposições Gerais</h3>
                        <p>O Sistema Integrado de Busca e Acesso a Monografias (SIBAM) da UNILUANDA tem como objetivo principal disponibilizar e preservar a produção científica da instituição, garantindo acesso aberto ao conhecimento.</p>
                        <p>O uso do SIBAM implica na aceitação integral deste regulamento e compromete o usuário ao seu cumprimento.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">2. Dos Direitos Autorais</h3>
                        <p>2.1. Os autores mantêm os direitos autorais sobre suas obras, cedendo à UNILUANDA o direito de publicação não exclusiva no SIBAM.</p>
                        <p>2.2. As monografias publicadas estão licenciadas sob Creative Commons Attribution-NonCommercial 4.0 International License.</p>
                        <p>2.3. É permitido o download e uso das obras para fins acadêmicos e de pesquisa, desde que citada a fonte.</p>
                        <p>2.4. É vedado o uso comercial das obras sem autorização expressa do autor.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">3. Das Responsabilidades dos Autores</h3>
                        <p>3.1. O autor é integralmente responsável pelo conteúdo da obra submetida.</p>
                        <p>3.2. O autor declara, sob pena de responsabilidade, que a obra é original e não infringe direitos autorais de terceiros.</p>
                        <p>3.3. O autor deve garantir que possui todas as autorizações necessárias para a publicação de imagens, gráficos e outros materiais incluídos na obra.</p>
                        <p>3.4. O autor deve informar corretamente todos os metadados solicitados no ato da submissão.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">4. Do Processo de Submissão</h3>
                        <p>4.1. A submissão de monografias é gratuita e restrita a estudantes e pesquisadores da UNILUANDA.</p>
                        <p>4.2. Todas as submissões passarão por processo de avaliação pela comissão editorial.</p>
                        <p>4.3. A comissão editorial poderá solicitar alterações, rejeitar ou aprovar as submissões.</p>
                        <p>4.4. O prazo para avaliação é de até 15 dias úteis, podendo ser prorrogado em casos excepcionais.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">5. Dos Critérios de Avaliação</h3>
                        <p>5.1. Serão avaliados: originalidade, relevância, rigor científico, clareza e formatação adequada.</p>
                        <p>5.2. As monografias devem seguir as normas da ABNT ou normas específicas do curso.</p>
                        <p>5.3. Serão rejeitadas obras que contenham plágio, dados fraudulentos ou conteúdo ofensivo.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">6. Do Acesso e Uso</h3>
                        <p>6.1. O acesso às monografias é livre e gratuito.</p>
                        <p>6.2. Os usuários podem consultar, baixar e imprimir as obras para uso pessoal e acadêmico.</p>
                        <p>6.3. É obrigatória a citação da fonte sempre que utilizar conteúdo das monografias.</p>
                        <p>6.4. É vedado o uso automatizado (crawlers, scrapers) sem autorização prévia.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h3 class="mb-4">7. Da Privacidade</h3>
                        <p>7.1. Os dados pessoais dos usuários são protegidos conforme a Lei de Proteção de Dados angolana.</p>
                        <p>7.2. As informações de contacto dos autores não serão divulgadas publicamente sem consentimento.</p>
                        <p>7.3. Os usuários podem solicitar a exclusão de sua conta e dados a qualquer momento.</p>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-4">8. Das Disposições Finais</h3>
                        <p>8.1. A UNILUANDA reserva-se o direito de alterar este regulamento a qualquer momento.</p>
                        <p>8.2. Casos omissos serão resolvidos pela comissão gestora do SIBAM.</p>
                        <p>8.3. Este regulamento entra em vigor na data de sua publicação.</p>
                        <p class="mb-0"><strong>Data da última atualização:</strong> <?php echo date('d/m/Y'); ?></p>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Aceitação do Regulamento</h5>
                        <p class="mb-0">Ao usar o SIBAM, você concorda com todos os termos deste regulamento.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>