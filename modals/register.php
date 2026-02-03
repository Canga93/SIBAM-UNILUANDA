<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="registerModalLabel"><i class="fas fa-user-plus me-2"></i>Criar Nova Conta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerForm" action="includes/register_process.php" method="POST">
                    <div class="mb-3">
                        <label for="registerName" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="registerName" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email * : exemplo@uniluanda.edu.ao</label>
                        <input type="email" class="form-control" id="registerEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="registerPassword" name="password" required>
                        <div class="form-text">Mínimo de 6 caracteres.</div>
                    </div>
                    <div class="mb-3">
                        <label for="registerConfirmPassword" class="form-label">Confirmar Password *</label>
                        <input type="password" class="form-control" id="registerConfirmPassword" name="confirm_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="registerType" class="form-label">Tipo de Usuário *</label>
                        <select class="form-select" id="registerType" name="tipo" required>
                            <option value="estudante">Estudante</option>
                
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="registerTerms" name="terms" required>
                        <label class="form-check-label" for="registerTerms">
                            Aceito os <a href="regulamento.php" target="_blank">termos e condições</a>
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">Criar Conta</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Já tem conta? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Faça login aqui</a></p>
                </div>
            </div>
        </div>
    </div>
</div>