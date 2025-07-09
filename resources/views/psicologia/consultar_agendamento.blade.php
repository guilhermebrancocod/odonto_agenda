<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consultar Agendamento</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        #content-wrapper {
            height: calc(100vh - 56px); /* altura padrão navbar */
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 1100px;">
            <h2 class="mb-4 text-center">Consultar Agendamento</h2>

            <!-- Formulário de pesquisa -->
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

            <!-- Resultados -->
            <div class="w-100">
                <h5 class="mb-3">Resultados</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cod</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Data de Nascimento</th>
                                <th>Sexo</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="pacientes-tbody">
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma pesquisa realizada ainda.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div class="modal fade" id="confirmEditModal" tabindex="-1" aria-labelledby="confirmEditModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmEditModalLabel">Editar Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p id="modal-paciente-nome">Deseja editar este paciente?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirm-edit-btn">Confirmar</button>
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
        const pacientesTbody = document.getElementById('pacientes-tbody');
        let selectedPaciente = null;

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nome = searchInput.value.trim();

            fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {
                    pacientesTbody.innerHTML = '';
                    if (pacientes.length === 0) {
                        pacientesTbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">Nenhum paciente encontrado.</td>
                            </tr>
                        `;
                        return;
                    }

                    pacientes.forEach(paciente => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${paciente.ID_PACIENTE ?? '-'}</td>
                            <td>${paciente.NOME_COMPL_PACIENTE ?? '-'}</td>
                            <td>${paciente.CPF_PACIENTE ?? '-'}</td>
                            <td>${paciente.DT_NASC_PACIENTE ? new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR') : '-'}</td>
                            <td>${paciente.SEXO_PACIENTE ?? '-'}</td>
                            <td>${paciente.FONE_PACIENTE ?? '-'}</td>
                            <td>${paciente.E_MAIL_PACIENTE ?? '-'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary editar-btn" 
                                    data-id="${paciente.ID_PACIENTE}" 
                                    data-nome="${paciente.NOME_COMPL_PACIENTE ?? 'Paciente'}">
                                    Editar
                                </button>
                            </td>
                        `;
                        pacientesTbody.appendChild(row);
                    });

                    document.querySelectorAll('.editar-btn').forEach(button => {
                        button.addEventListener('click', () => {
                            selectedPaciente = {
                                id: button.getAttribute('data-id'),
                                nome: button.getAttribute('data-nome')
                            };
                            document.getElementById('modal-paciente-nome').textContent = `Deseja editar o paciente: ${selectedPaciente.nome}?`;
                            const modal = new bootstrap.Modal(document.getElementById('confirmEditModal'));
                            modal.show();
                        });
                    });
                })
                .catch(error => {
                    console.error(error);
                    pacientesTbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger">Erro ao buscar pacientes.</td>
                        </tr>
                    `;
                });
        });

        document.getElementById('confirm-edit-btn').addEventListener('click', () => {
            if (selectedPaciente) {
                const url = `/psicologia/editar-paciente/${selectedPaciente.id}`;
                window.location.href = url;
            }
        });
    </script>
</body>
</html>
