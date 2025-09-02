<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Agendamento</title>

    

    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        #limit-container {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        #limitador-registros {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .flatpickr-input {
            background-color: #fff;
        }
        .shadow-dark {
            box-shadow: 0 0.75rem 1.25rem rgba(0,0,0,0.4) !important;
        }
        @keyframes slideDownFadeOut {
            0%   { transform: translate(-50%, -100%); opacity: 0; }
            10%  { transform: translate(-50%, 0); opacity: 1; }
            90%  { transform: translate(-50%, 0); opacity: 1; }
            100% { transform: translate(-50%, -100%); opacity: 0; }
        }
        .animate-alert {
            animation: slideDownFadeOut 5s ease forwards;
            z-index: 1050;
        }
        .agendamento-actions .btn:hover {
            filter: brightness(85%);
            transition: filter 0.2s ease-in-out;
        }
    </style>
</head>

<body class="bg-body-secondary">
    @include('components.navbar')

    @if(session('success'))
        <div class="alert alert-success text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3 animate-alert">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger shadow text-center position-fixed top-0 start-50 translate-middle-x mt-3 animate-alert" style="max-width: 90%;">
            <strong>Ops!</strong> Corrija os itens abaixo:
            <ul class="mb-0 mt-1 list-unstyled">
                @foreach($errors->all() as $error)
                    <li><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container ms-3 mw-100">
        <div class="row">
            <x-page-title>
                <p onclick="window.location.href = '/psicologia/criar-agendamento'" class="btn btn-success p-2 me-3" style="font-size: 15px;" >
                    <span>Novo Agendamento</span>
                </p>
            </x-page-title>

            <div class="col-12 shadow-lg shadow-dark p-4 bg-body-tertiary rounded">
                
                <form id="search-form" class="w-100 mb-4">
                    <div class="row g-3">
                         <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input id="search-input" name="search" type="search" class="form-control" placeholder="Nome ou CPF do paciente" />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person-workspace"></i></span>
                                <input id="psicologo-input" name="psicologo" type="search" class="form-control" placeholder="Nome/Matrícula do Psicólogo" />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input id="date-input" name="date" type="text" class="form-control" placeholder="Data" />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                                <input id="start-time-input" name="start_time" type="text" class="form-control" placeholder="Hora Início" />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <input id="end-time-input" name="end_time" type="text" class="form-control" placeholder="Hora Fim" />
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-list-check"></i></span>
                                <select id="status-input" name="status" class="form-select">
                                    <option value="" selected>Todos os Status</option>
                                    <option value="Agendado">Agendado</option>
                                    <option value="Presente">Presente</option>
                                    <option value="Remarcado">Reagendado</option>
                                    <option value="Cancelado">Cancelado</option>
                                    <option value="Finalizado">Finalizado</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Pesquisar</button>
                            <button type="button" class="btn btn-outline-secondary" id="btnClearFilters">Limpar</button>
                        </div>
                    </div>
                </form>

                <hr>

                <div class="w-100">
                    <h5 class="mb-3">Resultados</h5>
                    <div class="table-responsive border rounded" style="max-height: 55vh; overflow-y: auto;">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th>Paciente</th>
                                    <th>Psicólogo</th>
                                    <th>Serviço</th>
                                    <th>Data</th>
                                    <th>Início</th>
                                    <th>Fim</th>
                                    <th>Local</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="agendamentos-tbody">
                                <tr><td colspan="9" class="text-center">Nenhuma pesquisa realizada ainda.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="limit-container">
                        <div id="contador-registros"><span class="text-muted">Total de registros: 0</span></div>
                        <div id="limitador-registros">
                            <label for="limit-select" class="form-label mb-0">Mostrar</label>
                            <select id="limit-select" class="form-select form-select-sm" style="width: auto;">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="modal fade" id="editAgendamentoModal" tabindex="-1" aria-labelledby="editAgendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAgendamentoModalLabel">Editar Agendamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <form id="editAgendamentoForm" action="" method="POST" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id_agendamento" id="edit_id_agendamento">
                    <input type="hidden" name="id_clinica" id="edit_id_clinica">
                    <input type="hidden" name="id_paciente" id="edit_id_paciente">

                    <div class="mb-3">
                        <label for="edit_paciente_nome" class="form-label">Paciente</label>
                        <input type="text" id="edit_paciente_nome" class="form-control" disabled>
                    </div>
                    
                    <h6>Detalhes do Agendamento</h6><hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_servico" class="form-label">Serviço</label>
                            <select id="edit_servico" name="id_servico" placeholder="Digite para pesquisar..."></select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_local" class="form-label">Local <span class="text-danger">*</span></label>
                            <select id="edit_local" name="local" placeholder="Digite para pesquisar..." required></select>
                        </div>
                        <div class="col-md-4">
                             <label for="edit_date" class="form-label">Data <span class="text-danger">*</span></label>
                            <input type="text" id="edit_date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_start_time" class="form-label">Hora Início <span class="text-danger">*</span></label>
                            <input type="text" id="edit_start_time" name="start_time" class="form-control" placeholder="HH:mm" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_end_time" class="form-label">Hora Fim <span class="text-danger">*</span></label>
                            <input type="text" id="edit_end_time" name="end_time" class="form-control" placeholder="HH:mm" required>
                        </div>
                    </div>
                    
                    <h6 class="mt-4">Status e Valores</h6><hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="Agendado">Agendado</option>
                                <option value="Em atendimento">Em atendimento</option>
                                <option value="Cancelado">Cancelado</option>
                                <option value="Finalizado">Finalizado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_valor_agend" class="form-label">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="text" class="form-control" name="valor_agend" id="edit_valor_agend" placeholder="0,00">
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4">Observações</h6><hr>
                    <div class="mb-3">
                        <label for="edit_observacoes" class="form-label">Observações</label>
                        <textarea name="observacoes" id="edit_observacoes" class="form-control" style="height: 100px"></textarea>
                    </div>
                    <input type="hidden" id="edit_motivo_cancelamento" name="mensagem">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <div class="modal fade" id="motivoCancelamento" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Motivo do Cancelamento</h5></div>
                <div class="modal-body">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="text-cancelamento" placeholder="Motivo" required>
                        <label for="text-cancelamento">Motivo <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    <button type="button" class="btn btn-primary" id="btnMensagemCancelamento">Confirmar Cancelamento</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const agendamentosTbody = document.getElementById('agendamentos-tbody');
    const searchForm = document.getElementById('search-form');
    const limitSelect = document.getElementById('limit-select');
    const contadorRegistros = document.getElementById('contador-registros');
    const btnClearFilters = document.getElementById('btnClearFilters');

    // --- FUNÇÕES DA PÁGINA PRINCIPAL (sem alterações) ---
    function getFilters() {
        const formData = new FormData(searchForm);
        const params = new URLSearchParams(formData);
        params.append('limit', limitSelect.value);
        return params;
    }

    function carregarAgendamentos() {
        const params = getFilters();
        const url = `/psicologia/get-agendamento?${params.toString()}`;
        agendamentosTbody.innerHTML = `<tr><td colspan="9" class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Carregando...</td></tr>`;

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Erro na resposta da rede.');
                return response.json();
            })
            .then(agendamentos => {
                agendamentosTbody.innerHTML = '';
                contadorRegistros.innerHTML = `<span class="text-muted">Total de registros: ${agendamentos.length}</span>`;

                if (agendamentos.length === 0) {
                    agendamentosTbody.innerHTML = `<tr><td colspan="9" class="text-center">Nenhum agendamento encontrado.</td></tr>`;
                    return;
                }

                agendamentos.forEach(ag => {
                    const paciente = ag.paciente ? ag.paciente.NOME_COMPL_PACIENTE : '-';
                    const psicologo = ag.psicologo ? ag.psicologo.NOME_COMPL : (ag.ID_PSICOLOGO || '-');
                    const servicoDesc = ag.servico ? ag.servico.SERVICO_CLINICA_DESC : '';
                    const dataF = ag.DT_AGEND ? new Date(ag.DT_AGEND).toLocaleDateString('pt-BR', {timeZone: 'UTC'}) : '-';
                    const horaIni = ag.HR_AGEND_INI ? ag.HR_AGEND_INI.substring(0, 5) : '-';
                    const horaFim = ag.HR_AGEND_FIN ? ag.HR_AGEND_FIN.substring(0, 5) : '-';
                    const statusMap = { 'Agendado': { c: 'text-success', i: 'bi-calendar-check' }, 'Cancelado': { c: 'text-danger', i: 'bi-calendar-x' }, 'Presente': { c: 'text-primary', i: 'bi-check2-circle' }, 'Finalizado': { c: 'text-secondary', i: 'bi-calendar2-check-fill' }, 'Remarcado': { c: 'text-warning', i: 'bi-arrow-repeat' } };
                    const statusInfo = statusMap[ag.STATUS_AGEND] || { c: 'text-muted', i: 'bi-question-circle' };
                    
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${paciente}</td>
                        <td>${psicologo}</td>
                        <td>${servicoDesc || '-'}</td>
                        <td>${dataF}</td>
                        <td>${horaIni}</td>
                        <td>${horaFim}</td>
                        <td>${ag.LOCAL || '-'}</td>
                        <td class="fw-bold ${statusInfo.c}"><i class="bi ${statusInfo.i} me-1"></i>${ag.STATUS_AGEND || '-'}</td>
                        <td class="d-flex flex-nowrap gap-1 agendamento-actions">
                            <button type="button" class="btn btn-sm btn-warning editar-agendamento-btn" title="Editar"
                                data-id-agendamento="${ag.ID_AGENDAMENTO}"
                                data-id-clinica="${ag.ID_CLINICA}"
                                data-id-paciente="${ag.ID_PACIENTE}"
                                data-nome-paciente="${paciente}"
                                data-id-servico="${ag.ID_SERVICO || ''}"
                                data-desc-servico="${servicoDesc}"
                                data-local="${ag.LOCAL || ''}"
                                data-dt-agend="${ag.DT_AGEND || ''}"
                                data-hr-ini="${ag.HR_AGEND_INI || ''}"
                                data-hr-fin="${ag.HR_AGEND_FIN || ''}"
                                data-status="${ag.STATUS_AGEND || ''}"
                                data-valor="${ag.VALOR_AGEND || ''}"
                                data-observacoes="${ag.OBSERVACOES || ''}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/psicologia/agendamento/${ag.ID_AGENDAMENTO}" method="POST" onsubmit="return confirm('Confirma a exclusão deste agendamento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Excluir"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    `;
                    agendamentosTbody.appendChild(row);
                });
                
                inicializarBotoesEditarAgendamento();
            })
            .catch(error => {
                console.error('Erro ao buscar agendamentos:', error);
                agendamentosTbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Falha ao carregar os dados.</td></tr>`;
            });
    }
    
    searchForm.addEventListener('submit', (e) => { e.preventDefault(); carregarAgendamentos(); });
    limitSelect.addEventListener('change', carregarAgendamentos);
    btnClearFilters.addEventListener('click', () => { 
        searchForm.reset();
        flatpickrInstances.forEach(instance => instance.clear()); 
        carregarAgendamentos(); 
    });

    // Carga inicial
    carregarAgendamentos();

    // --- LÓGICA DA MODAL DE EDIÇÃO ---
    const editModal = new bootstrap.Modal(document.getElementById('editAgendamentoModal'));
    const editForm = document.getElementById('editAgendamentoForm');
    const cancelaModal = new bootstrap.Modal(document.getElementById('motivoCancelamento'));
    
    const editDatePicker = flatpickr("#edit_date", { dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", locale: "pt" });
    const editStartTimePicker = flatpickr("#edit_start_time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, minuteIncrement: 15 });
    const editEndTimePicker = flatpickr("#edit_end_time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, minuteIncrement: 15 });
    
    // --- NOVA INICIALIZAÇÃO COM TOM-SELECT ---
    const tomStatus = new TomSelect("#edit_status", {
        create: false,
    });

    const tomServico = new TomSelect("#edit_servico", {
        valueField: 'ID_SERVICO_CLINICA',
        labelField: 'SERVICO_CLINICA_DESC',
        searchField: 'SERVICO_CLINICA_DESC',
        create: false,
        allowEmptyOption: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`/psicologia/pesquisar-servico?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        }
    });

    const tomLocal = new TomSelect("#edit_local", {
        valueField: 'DESCRICAO',
        labelField: 'DESCRICAO',
        searchField: 'DESCRICAO',
        create: true, // Permite adicionar um local novo que não está na lista
        allowEmptyOption: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            fetch(`/psicologia/pesquisar-local?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        }
    });
    
    // --- FUNÇÃO DE PREENCHIMENTO DA MODAL ATUALIZADA ---
    function inicializarBotoesEditarAgendamento() {
        document.querySelectorAll('.editar-agendamento-btn').forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;

                // Preenche campos normais
                editForm.querySelector('#edit_id_agendamento').value = data.idAgendamento;
                editForm.querySelector('#edit_id_clinica').value = data.idClinica;
                editForm.querySelector('#edit_id_paciente').value = data.idPaciente;
                editForm.querySelector('#edit_paciente_nome').value = data.nomePaciente;
                editForm.querySelector('#edit_valor_agend').value = data.valor;
                editForm.querySelector('#edit_observacoes').value = data.observacoes;

                // Limpa e define valores para os campos TomSelect
                tomStatus.setValue(data.status);
                
                tomServico.clear();
                tomServico.clearOptions(); // Garante que opções antigas sejam removidas
                if (data.idServico && data.descServico) {
                    tomServico.addOption({ ID_SERVICO_CLINICA: data.idServico, SERVICO_CLINICA_DESC: data.descServico });
                    tomServico.setValue(data.idServico);
                }

                tomLocal.clear();
                tomLocal.clearOptions();
                if (data.local) {
                    tomLocal.addOption({ DESCRICAO: data.local });
                    tomLocal.setValue(data.local);
                }
                
                // Define valores para os campos Flatpickr
                editDatePicker.setDate(data.dtAgend, true);
                editStartTimePicker.setDate(data.hrIni, true);
                editEndTimePicker.setDate(data.hrFin, true);

                // Define a action do formulário
                editForm.action = `/psicologia/agendamento/${data.idAgendamento}`;
                
                editModal.show();
            });
        });
    }

    // --- LÓGICA DA MODAL DE CANCELAMENTO (sem alterações) ---
    editForm.addEventListener('submit', function (event) {
        if (document.getElementById('edit_status').value === 'Cancelado') {
            event.preventDefault();
            cancelaModal.show();
        }
    });

    document.getElementById('btnMensagemCancelamento').addEventListener('click', function() {
        const motivoInput = document.getElementById('text-cancelamento');
        if (motivoInput.value.trim() === '') {
            alert('O motivo do cancelamento é obrigatório.');
            motivoInput.focus();
            return;
        }
        document.getElementById('edit_motivo_cancelamento').value = motivoInput.value;
        editForm.submit();
    });

    // A função setupAutocomplete() foi removida pois não é mais necessária.

    // --- FLATPICKR PARA OS FILTROS DA PÁGINA (sem alterações) ---
    flatpickr.localize(flatpickr.l10ns.pt);
    const datePicker = flatpickr("#date-input", { dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", allowInput: true });
    const startTimePicker = flatpickr("#start-time-input", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
    const endTimePicker = flatpickr("#end-time-input", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
    const flatpickrInstances = [datePicker, startTimePicker, endTimePicker];
});
</script>
</body>
</html>