<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <?php include 'includes/head.php'; ?>
    <title>Ajuda - SIBAM UNILUANDA</title>
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
</style>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="text-center mb-5">
                    <h1 class="mb-3">Centro de Ajuda</h1>
                    <p class="lead">Encontre respostas para suas dúvidas sobre o SIBAM</p>
                </div>
                
                <div class="accordion" id="helpAccordion">
                    <!-- FAQ 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Como posso submeter minha monografia?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Para submeter sua monografia:</p>
                                <ol>
                                    <li>Faça login em sua conta (ou crie uma se necessário)</li>
                                    <li>Acesse a página "Submeter Monografia" no menu principal</li>
                                    <li>Preencha todos os campos obrigatórios do formulário</li>
                                    <li>Faça upload do arquivo da monografia (PDF, DOC ou DOCX)</li>
                                    <li>Clique em "Submeter Monografia"</li>
                                </ol>
                                <p>Após a submissão, sua monografia será revisada pela equipe administrativa antes de ser publicada.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Quais são os requisitos para o arquivo da monografia?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>O arquivo da monografia deve atender aos seguintes requisitos:</p>
                                <ul>
                                    <li>Formatos aceitos: PDF, DOC ou DOCX</li>
                                    <li>Tamanho máximo: 10MB</li>
                                    <li>Devem conter: Capa, folha de rosto, resumo, abstract, desenvolvimento, conclusão e referências</li>
                                    <li>O resumo deve ter entre 150 e 500 palavras</li>
                                    <li>Devem ser incluídas pelo menos 3 palavras-chave</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Quanto tempo leva para a monografia ser aprovada?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>O processo de aprovação geralmente leva de 3 a 7 dias úteis. Você receberá uma notificação por email quando sua monografia for aprovada ou se precisar de alterações.</p>
                                <p>Você pode acompanhar o status de sua submissão através de sua conta no sistema.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 4 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                Como faço para baixar uma monografia?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Para baixar uma monografia:</p>
                                <ol>
                                    <li>Navegue até a página "Monografias"</li>
                                    <li>Use os filtros para encontrar o trabalho desejado</li>
                                    <li>Clique no título da monografia para ver os detalhes</li>
                                    <li>Na página de detalhes, clique no botão "Baixar Monografia"</li>
                                </ol>
                                <p>Algumas monografias podem exigir login para download, dependendo das configurações definidas pelo autor.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 5 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                Esqueci minha password. Como posso recuperá-la?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Se você esqueceu sua password:</p>
                                <ol>
                                    <li>Na página de login, clique em "Esqueci minha password"</li>
                                    <li>Informe o email associado à sua conta</li>
                                    <li>Você receberá um email com instruções para redefinir sua password</li>
                                    <li>Siga as instruções no email para criar uma nova password</li>
                                </ol>
                                <p>Se você não receber o email, verifique sua pasta de spam ou entre em contacto com o suporte.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- FAQ 6 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix">
                                Como entro em contacto com o autor de uma monografia?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#helpAccordion">
                            <div class="accordion-body">
                                <p>Para entrar em contacto com o autor de uma monografia:</p>
                                <ol>
                                    <li>Acesse a página de detalhes da monografia desejada</li>
                                    <li>Na seção lateral, você encontrará o botão "Enviar Email"</li>
                                    <li>Clique no botão para abrir seu cliente de email padrão</li>
                                </ol>
                                <p>Nota: A disponibilidade desta funcionalidade depende das configurações de privacidade do autor.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-5">
                    <div class="card-body text-center">
                        <h4 class="card-title mb-3">Não encontrou o que procurava?</h4>
                        <p class="card-text mb-4">Entre em contacto com nossa equipe de suporte para assistência personalizada.</p>
                        <a href="contactos.php" class="btn btn-primary">Contactar Suporte</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>