<div class="modal fade" tabindex="-1" id="modal_edit_agenda">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Agendamento</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form_edit_agenda">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="mb-2" style="flex: 1 1 40%;">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" disabled>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" id="data" name="data" placeholder="data">
                    </div>
                    <div class="mb-2">
                        <label for="telefone" class="form-label">Hora início</label>
                        <input type="text" class="form-control" id="data" name="data" placeholder="hora inicio">
                    </div>
                    <div class="mb-2">
                        <label for="telefone" class="form-label">Hora fim</label>
                        <input type="text" class="form-control" id="data" name="data" placeholder="hora fim">
                    </div>
                    <div style="flex: 0.2;">
                        <label for="pagto" style="font-size: 14px; color: #666;">Status</label>
                        <select type="text" id="pagto" class="selectpicker" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <option value=""></option>
                            <option value="agendado">Agendado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="finalizado">Finalizado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Fechar</button>
                <button type="submit" form="form_edit_agenda" class="btn btn-primary">Adicionar</button>
            </div>
        </div>
    </div>
</div>