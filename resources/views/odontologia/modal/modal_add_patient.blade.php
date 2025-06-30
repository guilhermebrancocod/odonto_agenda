<div class="modal fade" tabindex="-1" id="modal_add_patient">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cadastro Simplificado</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_add_patient">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="mb-2" style="flex: 1 1 40%;">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo">
                        </div>
                        <div class="mb-3" style="flex: 1 1 5%;">
                            <label for="nome" class="form-label">Sexo</label>
                            <select type="text" id="sexo" class="selectpicker" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value="">M</option>
                                <option value="">F</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" class="form-control" id="cpf" name="cpf" placeholder="000.000.000-00">
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="mb-2" style="flex: 1 1 20%;">
                            <label for="cep" class="form-label">CEP</label>
                            <input type="text" class="form-control" id="cep" name="cep" placeholder="00000-000">
                        </div>
                        <div class="mb-2" style="flex: 1 1 40%;">
                            <label for="rua" class="form-label">Rua</label>
                            <input type="text" class="form-control" id="rua" name="rua" placeholder="Nome da rua">
                        </div>
                        <div class="mb-2" style="flex: 1 1 15%;">
                            <label for="numero" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero" name="numero" placeholder="Nº">
                        </div>
                        <div class="mb-1" style="flex: 1 1 25%;">
                            <label for="bairro" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="bairro" name="bairro" placeholder="Bairro">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Fechar</button>
                <button type="submit" form="form_add_patient" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>
</div>