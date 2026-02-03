<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loginModalLabel"><i class="fas fa-sign-in-alt me-2"></i>Entrar no Sistema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="loginForm" action="includes/login_process.php" method="POST">
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email: exemplo@uniluanda.edu.ao</label>
                        <input type="email" class="form-control" id="loginEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="loginPassword" name="password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">Lembrar-me</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Não tem conta? 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                            Registre-se aqui
                        </a>
                    </p>
                    <p>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">
                            Esqueceu sua senha?
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Recuperação de Senha -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="forgotPasswordModalLabel"><i class="fas fa-unlock-alt me-2"></i>Recuperar Senha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="includes/forgot_password_process.php" method="POST">
                    <div class="mb-3">
                        <label for="forgotEmail" class="form-label">Digite seu email cadastrado</label>
                        <input type="email" class="form-control" id="forgotEmail" name="email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning text-white">Enviar Link de Recuperação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
