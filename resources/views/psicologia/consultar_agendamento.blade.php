<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Agendamento</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body { height: 100%; margin: 0; }
        #content-wrapper {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f8f9fa;
        }
        .modal-body { max-height: 60vh; overflow-y: auto; }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 1100px;">
            <h2 class="mb-4 text-center">Consultar Agendamento</h2>

            <form id="search-form" class="w-100 mb-4 d-flex flex-column flex-md-row gap-3 align-items-stretch align-items-md-end">
                <div class="flex-fill">
                    <input
                        id="search-input"
                        name="search"
                        type="search"
                        class="form-control form-control-lg"
                        placeholder="Digite o nome ou CPF do paciente"
                    />
                </div>
                <div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Pesquisar</button>
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
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script>
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const agendamentosTbody = document.getElementById('agendamentos-tbody');

        function carregarAgendamentos(search = '') {
            const url = search
                ? `/psicologia/get-agendamento?search=${encodeURIComponent(search)}`
                : `/psicologia/get-agendamento`;

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
                        const data = ag.DT_AGEND ? new Date(ag.DT_AGEND).toLocaleDateString('pt-BR') : '-';
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

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const search = searchInput.value.trim();
            carregarAgendamentos(search);
        });

        // Carrega os últimos 5 agendamentos quando a página for carregada
        document.addEventListener('DOMContentLoaded', () => {
            carregarAgendamentos();
        });
    </script>
</body>
</html>
