<?php include 'includes/submissao_process.php'; ?>

<!-- Modal Submissão de Monografia -->
<div class="modal fade" id="modalSubmissao" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <!-- Cabeçalho do Modal -->
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title">
          <i class="fas fa-upload me-2"></i>Submeter Monografia
        </h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Corpo do Modal -->
      <div class="modal-body">

        <div class="container-fluid">
          <div class="row justify-content-center">
            <div class="col-md-10">

              <!-- Card do Formulário -->
              <div class="card shadow">
                <div class="card-body">

                  <?php echo $message ?? ''; ?>

                  <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Título da Monografia *</label>
                          <input type="text" class="form-control" name="titulo"
                                 value="<?php echo $_POST['titulo'] ?? ''; ?>" required>
                          <div class="invalid-feedback">Informe o título.</div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Área de Conhecimento *</label>
                          <select class="form-select" name="area" required>
                            <option value="" disabled selected>Selecione uma área</option>
                            <option value="Engenharia Mecatronica" 
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Engenharia Mecatronica') ? 'selected' : ''; ?>>
                              Engenharia Mecatrônica
                            </option>
                            <option value="Engenharia dos Transportes"
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Engenharia dos Transportes') ? 'selected' : ''; ?>>
                              Engenharia dos Transportes
                            </option>
                            <option value="Engenharia Informatica"
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Engenharia Informatica') ? 'selected' : ''; ?>>
                              Engenharia Informática
                            </option>
                            <option value="Informatica de Gestao"
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Informatica de Gestao') ? 'selected' : ''; ?>>
                              Informática de Gestão
                            </option>
                            <option value="Gestao e Logistica"
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Gestao e Logistica') ? 'selected' : ''; ?>>
                              Gestão e Logística
                            </option>
                            <option value="Gestao Aeronautica"
                                    <?php echo (isset($_POST['area']) && $_POST['area'] == 'Gestao Aeronautica') ? 'selected' : ''; ?>>
                              Gestão Aeronáutica
                            </option>
                          </select>
                          <div class="invalid-feedback">Selecione a área de conhecimento.</div>
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Resumo *</label>
                      <textarea class="form-control" name="resumo" rows="5" required><?php echo $_POST['resumo'] ?? ''; ?></textarea>
                      <div class="invalid-feedback">Informe o resumo.</div>
                      <div class="form-text">Máximo de 500 palavras.</div>
                    </div>

                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Orientador</label>
                          <input type="text" class="form-control" name="orientador"
                                 value="<?php echo $_POST['orientador'] ?? ''; ?>">
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Data de Publicação</label>
                          <input type="date" class="form-control" name="data_publicacao"
                                 value="<?php echo $_POST['data_publicacao'] ?? ''; ?>">
                        </div>
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Palavras-chave</label>
                      <input type="text" class="form-control" name="palavras_chave"
                             value="<?php echo $_POST['palavras_chave'] ?? ''; ?>">
                      <div class="form-text">Separe por vírgula.</div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label">Arquivo da Monografia *</label>
                      <input type="file" class="form-control" name="arquivo"
                             accept=".pdf,.doc,.docx" required>
                      <div class="invalid-feedback">Selecione o arquivo.</div>
                      <div class="form-text">PDF, DOC ou DOCX • Máx: 10MB</div>
                    </div>

                    <div class="d-grid">
                      <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Submeter Monografia
                      </button>
                    </div>

                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
