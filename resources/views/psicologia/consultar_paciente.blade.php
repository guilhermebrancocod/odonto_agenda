<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
</head>

<body>
    <div id="navbar-container">
        <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">Pesquisando</h2>
            </div>

            <div class="linha-com-titulo">
                <h5>Paciente</h5>
                <div class="linha-flex"></div>
            </div>

            <!-- FORMULÁRIO DE CONSULTA DE PACIENTE -->
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <form id="search-form" class="d-flex gap-3 align-items-end flex-wrap" style="margin:20px 0;">
                    <div style="flex: 1;">
                        <input
                            id="search-input"
                            name="search"
                            type="search"
                            class="form-control"
                            placeholder="Pesquisar paciente"
                            style="padding:8px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                        />
                    </div>
                    <div style="flex-shrink: 0;">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size:14px; border-radius:6px;">
                            Pesquisar
                        </button>
                    </div>
                </form>
            </div>

            <div class="linha-com-titulo">
                <h5>Resultado</h5>
                <div class="linha-flex"></div>
            </div>

            <!-- TABELA COM PACIENTES RETORNADOS -->
            <div class="datatable" style="margin-top:25px">
                <table class="table datatable-table">
                    <thead class="datatable-header">
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

    <!-- MODAL DE CONFIRMAÇÃO -->
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

    <!-- SCRIPTS -->
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
                            const modalElement = document.getElementById('confirmEditModal');
                            const modal = new bootstrap.Modal(modalElement);
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
                // const url = `/psicologia/editar-paciente/${selectedPaciente.id}?nome=${encodeURIComponent(selectedPaciente.nome)}`;
                const url = `/psicologia/editar-paciente`;
                window.location.href = url;
            }
        });
    </script>
</body>
</html>
