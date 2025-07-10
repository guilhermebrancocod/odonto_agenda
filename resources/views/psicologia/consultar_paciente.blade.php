<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Paciente</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        #content-wrapper {
            height: calc(100vh - 56px); /* altura padrão da navbar Bootstrap */
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f8f9fa;
        }

        /* ADICIONA SCROLL CASO EXCEDA O MÁXIMO DE ALTURA DEFINIDO */
        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 1100px;">
            <h2 class="mb-4 text-center">Consultar Paciente</h2>

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

    <!-- MODA DE CONFIRMAÇÃO DE EDIÇÃO DE PACIENTE -->
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

    <!-- MODAL DE EDIÇÃO DO PACIENTE -->
    <div class="modal fade" id="editPacienteModal" tabindex="-1" aria-labelledby="editPacienteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Editar Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                
                <div class="modal-body">
                    <form id="editPacienteForm">

                        <!-- NOME DO PACIENTE -->
                        <div class="mb-3">
                            <label for="editPacienteNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="editPacienteNome" name="nome">
                        </div>
                        
                        <!-- CPF DO PACIENTE -->
                        <div class="mb-3">
                            <label for="editPacienteCPF" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="editPacienteCPF" name="cpf">
                        </div>

                        <!-- DT NASCIMENTO PACIENTE -->
                        <div class="mb-3">
                            <label for="editPacienteDTNASC" class="form-label">Data Nascimento</label>
                            <input type="text" class="form-control" id="editPacienteDTNASC" name="dt_nasc">
                        </div>

                        <!-- SEXO PACIENTE -->
                        <div class="mb-3">
                            <label for="editPacienteSEXO" class="form-label">Sexo</label>
                            <select name="sexo" id="editPacienteSEXO" class="form-select">
                                <option value="" selected>Selecione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                                <option value="O">Outro</option>
                            </select>
                        </div>

                        <!-- ENDEREÇO  LOGRADOURO -->
                        <div class="mb-3">
                            <label for="editPacienteENDERECO" class="form-label">Rua</label>
                            <input type="text" class="form-control" id="editPacienteENDERECO" name="endereco">
                        </div>

                        <!-- END NUM -->
                        <div class="mb-3">
                            <label for="editPacienteNUM" class="form-label">Número</label>
                            <input type="integer" class="form-control" id="editPacienteNUM" name="numero">
                        </div>

                        <!-- COMPLEMENTO -->
                        <div class="mb-3">
                            <label for="editPacienteCOMPLEMENTO" class="form-label">Complemento</label>
                            <input type="text" class="form-control" id="editPacienteCOMPLEMENTO" name="complemento">
                        </div>

                        <!-- BAIRRO -->
                        <div class="mb-3">
                            <label for="editPacienteBAIRRO" class="form-label">Bairro</label>
                            <input type="text" class="form-control" id="editPacienteBAIRRO" name="bairro">
                        </div>

                        <!-- UF -->
                        <div class="mb-3">
                            <label for="editPacienteBAIRRO" class="form-label">UF</label>
                            <input type="text" class="form-control" id="editPacienteBAIRRO" name="uf">
                        </div>

                        <!-- CEP -->
                        <div class="mb-3">
                            <label for="editPacienteCEP" class="form-label">UF</label>
                            <input type="text" class="form-control" id="editPacienteCEP" name="cep">
                        </div>

                        <!-- FONE_PACIENTE -->
                        <div class="mb-3">
                            <label for="celular" class="form-label">Celular</label>
                            <input type="text" id="celular" name="CELULAR_PACIENTE" class="form-control" />
                        </div>

                        <!-- EMAIL_PACIENTE -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="E_MAIL_PACIENTE" class="form-control" />
                        </div>

                        <!-- MUNICIPIO_PACIENTE -->
                        <div class="mb-4">
                            <label for="cidade" class="form-label">Cidade</label>
                            <input type="text" id="cidade" name="END_COMPL" class="form-control" />
                        </div>

                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </form>
                </div>
            </div>
        </dib>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script>

        // FORMULÁRIO DE PESQUISA
        const searchForm = document.getElementById('search-form');

        // INPUT DE PESQUISA
        const searchInput = document.getElementById('search-input');

        // TABELA DE PACIENTES RETORNADOS
        const pacientesTbody = document.getElementById('pacientes-tbody');

        // VARIÁVEL DE PACIENTE SELECIONADO
        let selectedPaciente = null;

        // ENVIO DO FORMULÁRIO DE BUSCA
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
                                    data-nome="${paciente.NOME_COMPL_PACIENTE ?? 'Paciente'}"
                                    data-cpf="${paciente.CPF_PACIENTE ?? ''}">
                                    Editar
                                </button>
                            </td>
                        `;
                        // O PRÓPRIO BOTÃO ARMAZENA OS DADOS DO PACIENTE
                        pacientesTbody.appendChild(row);
                    });

                    document.querySelectorAll('.editar-btn').forEach(button => {
                        button.addEventListener('click', () => {

                            // DADOS DO PACIENTE SELECIONADO
                            selectedPaciente = {
                                id: button.getAttribute('data-id'),
                                nome: button.getAttribute('data-nome'),
                                cpf: button.getAttribute('data-cpf')
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

        // QUANDO USUARIO CLICA EM CONFIRMAR EDIÇÃO DE PACIENTE
        document.getElementById('confirm-edit-btn').addEventListener('click', () => {

            // INSERE OS VALORES DO PACIENTE SELECIONADO NO MODAL
            if(selectedPaciente) {
                document.getElementById('editPacienteNome').value = selectedPaciente.nome;
                document.getElementById('editPacienteCPF').value = selectedPaciente.cpf;
            }

            // ESCONDE MODAL DE CONFIRMAÇÃO DE EDIÇÃO
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmEditModal'));
            confirmModal.hide();

            // MOSTRA MODAL DE EDIÇÃO DE PACIENTE
            const editModal = new bootstrap.Modal(document.getElementById('editPacienteModal'));
            editModal.show();
        });

        // TRATAMENTO DO ENVIO DO FORMULÁRIO DE EDIÇÃO DE PACIENTE
        document.getElementById('editPacienteForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const nome = document.getElementById('editPacienteNome').value;
            const cpf = document.getElementById('editPacienteCpf').value;

            // Aqui você pode enviar os dados via fetch ou axios para atualizar o paciente no backend
            console.log('Nome:', nome, 'CPF:', cpf);

            // Após salvar, você pode fechar o modal:
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editPacienteModal'));
            editModal.hide();
        });
        
    </script>
</body>
</html>
