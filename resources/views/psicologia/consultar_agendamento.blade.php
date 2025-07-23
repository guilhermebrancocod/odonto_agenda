<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Agendamento</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body { height: 100%; margin: 0; }
        #content-wrapper {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            background-color: #f8f9fa;
        }
        .modal-body { max-height: 60vh; overflow-y: auto; }
        /* Espaçamento para o container do limit select */
        #limit-container {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 8px;
        }
        #limit-container label {
            font-weight: 600;
        }
        .flatpickr-input {
            background-image: none !important;
        }
        .flatpickr-calendar-arrow {
            display: none !important;
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="content-wrapper">

        <!-- INFORMA ERROS DE VALIDAÇÃO DO BACKEND EM CASOS DE SUBMISSÃO DE FORMULÁRIO -->
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MOSTRA MENSAGEM DE SUCESSO AO USUARIO APÓS UMA AÇÃO BEM SUCEDIDA -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white p-4 rounded shadow-sm w-100">

            <!-- TITULO DA PÁGINA -->
            <h2 class="mb-4 text-center">Consultar Agendamento</h2>

            <!-- CAMPOS DE PESQUISA - FILTRO -->
            <form id="search-form" class="w-100 mb-4">
                <div class="row g-3">

                    <!-- PESQUISA POR NOME DO PACIENTE OU CPF -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input
                                id="search-input"
                                name="search"
                                type="search"
                                class="form-control"
                                placeholder="Nome ou CPF do paciente"
                            />
                        </div>
                    </div>

                    <!-- PESQUISA POR DATA -->
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input
                                id="date-input"
                                name="date"
                                type="date"
                                class="form-control"
                                placeholder="Data"
                            />
                        </div>
                    </div>

                    <!-- PESQUISA POR HORA DE INÍCIO  -->
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-clock"></i>
                            </span>
                            <input
                                id="start-time-input"
                                name="start_time"
                                type="time"
                                class="form-control"
                                placeholder="Hora Início"
                            />
                        </div>
                    </div>

                    <!-- PESQUISA POR HORA FINAL -->
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-clock"></i>
                            </span>
                            <input
                                id="end-time-input"
                                name="end_time"
                                type="time"
                                class="form-control"
                                placeholder="Hora Fim"
                            />
                        </div>
                    </div>

                    <!-- PESQUISA POR STATUS DO AGENDAMENTO -->
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-list-check"></i>
                            </span>
                            <select id="status-input" name="status" class="form-select">
                                <option value="">Status</option>
                                <option value="Agendado">Agendado</option>
                                <option value="Em atendimento">Em atendimento</option>
                                <option value="Finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>

                    <!-- PESQUISA POR SERVIÇO -->
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-briefcase"></i>
                            </span>
                            <input
                                id="service-input"
                                name="service"
                                type="text"
                                class="form-control"
                                placeholder="Serviço"
                            />
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Pesquisar</button>
                    </div>
                </div>
            </form>

            <div class="w-100">
                <h5 class="mb-3">Resultados</h5>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light" style="position: sticky; top: 0; background-color: white; z-index: 10;">
                            <tr>
                                <th>Paciente</th>
                                <th>Serviço</th>
                                <th>Data</th>
                                <th>Hora Início</th>
                                <th>Hora Fim</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="agendamentos-tbody">
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma pesquisa realizada ainda.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Seletor de limite de registros abaixo da tabela -->
                <div id="limit-container">
                    <label for="limit-select">Mostrar:</label>
                    <select id="limit-select" class="form-select" style="width: auto;">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        const agendamentosTbody = document.getElementById('agendamentos-tbody');
        const searchForm = document.getElementById('search-form');
        const limitSelect = document.getElementById('limit-select');

        // Função para coletar filtros atuais (do formulário) e o limite selecionado
        function getFilters() {
            return {
                search: document.getElementById('search-input').value.trim(),
                date: document.getElementById('date-input').value,
                start_time: document.getElementById('start-time-input').value,
                end_time: document.getElementById('end-time-input').value,
                status: document.getElementById('status-input').value,
                service: document.getElementById('service-input').value.trim(),
                limit: limitSelect.value,
            };
        }

        // Função que carrega agendamentos conforme filtros
        function carregarAgendamentos(params = {}) {
            const urlParams = new URLSearchParams(params).toString();
            const url = `/psicologia/get-agendamento` + (urlParams ? `?${urlParams}` : '');

            fetch(url)
                .then(response => response.json())
                .then(agendamentos => {
                    agendamentosTbody.innerHTML = '';
                    if (agendamentos.length === 0) {
                        agendamentosTbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">Nenhum agendamento encontrado.</td>
                            </tr>
                        `;
                        return;
                    }

                    agendamentos.forEach(ag => {
                        const paciente = ag.paciente ? ag.paciente.NOME_COMPL_PACIENTE : '-';
                        const servico = ag.servico ? ag.servico.SERVICO_CLINICA_DESC : '-';
                        const data = ag.DT_AGEND ? ag.DT_AGEND.substring(0, 10).split('-').reverse().join('/') : '-';
                        const horaIni = ag.HR_AGEND_INI ? ag.HR_AGEND_INI.substring(0, 5) : '-';
                        const horaFim = ag.HR_AGEND_FIN ? ag.HR_AGEND_FIN.substring(0, 5) : '-';
                        const status = ag.STATUS_AGEND ?? '-';

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${paciente}</td>
                            <td>${servico}</td>
                            <td>${data}</td>
                            <td>${horaIni}</td>
                            <td>${horaFim}</td>
                            <td>${status}</td>
                            <td>
                                <a href="/psicologia/agendamento/${ag.ID_AGENDAMENTO}" class="btn btn-sm btn-primary">Visualizar</a>
                                <a href="/psicologia/agendamento/${ag.ID_AGENDAMENTO}/editar" class="btn btn-sm btn-warning">Editar</a>
                                <form action="/psicologia/agendamento/${ag.ID_AGENDAMENTO}" method="POST" style="display:inline;" onsubmit="return confirm('Confirma a exclusão deste agendamento?');">
                                    <input type="hidden" name="_method" value="DELETE" />
                                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}" />
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        `;
                        agendamentosTbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error(error);
                    agendamentosTbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger">Erro ao buscar agendamentos.</td>
                        </tr>
                    `;
                });
        }

        // Ao enviar o formulário, carrega agendamentos com filtros atuais e limite selecionado
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            carregarAgendamentos(getFilters());
        });

        // Ao mudar o limite, recarrega automaticamente com filtros atuais
        limitSelect.addEventListener('change', () => {
            carregarAgendamentos(getFilters());
        });

        // Carrega a lista ao abrir a página com limite padrão 10
        document.addEventListener('DOMContentLoaded', () => {
            carregarAgendamentos(getFilters());
        });
    </script>


    <!-- FLATPICKR PARA MELHORAR VISUALIZAÇÃO DE DIAS E HORÁRIOS -->
    <script>
        flatpickr("#date-input", {
            dateFormat: "Y-m-d",   
            altInput: true,
            altFormat: "d-m-Y",
            locale: "pt",
            minDate: "today",
            allowInput: true,
        });

        flatpickr("#start-time-input", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i"
        });

        flatpickr("#end-time-input", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i"
        });

        flatpickr.localize(flatpickr.l10ns.pt);
    </script>

</body>
</html>
