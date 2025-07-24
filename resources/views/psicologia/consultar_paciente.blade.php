<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Paciente</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
         #content-wrapper {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
            background-color: #f8f9fa;
        
        /* ADICIONA SCROLL CASO EXCEDA O MÁXIMO DE ALTURA DEFINIDO */
        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        
    </style>
</head>

<body>
    @include('components.navbar')

    <!-- CONTEUDO PRINCIPAL -->
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
            <h2 class="mb-4 text-center">Consultar Paciente</h2>

            <!-- FORMULÁIO DE PESQUISA -->
            <form id="search-form" class="w-100 mb-4">
                <div class="row g-3">

                    <!-- PESQUISA POR NOME DO PACIENTE OU CPF -->
                    <div class="col-md-3">
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

                <!-- DATA DE NASCIMENTO -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <input
                        id="DT_NASC_PACIENTE-input"
                        name="DT_NASC_PACIENTE"
                        type="text"
                        class="form-control"
                        placeholder="Data"
                        />
                    </div>
                </div>

                <!-- SEXO -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-arrow-down-circle"></i>
                        </span>
                        <select id="sexo" name="SEXO_PACIENTE" class="form-select form-select-sm">
                            <option value="">Sexo</option>
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                            <option value="O">Outro</option>
                        </select>
                    </div>
                </div>

                <!-- TELEFONE -->
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-telephone"></i>
                        </span>
                        <input
                            id="telefone-input"
                            name="FONE_PACIENTE"
                            type="text"
                            class="form-control"
                            placeholder="Telefone"
                        />
                    </div>
                </div>

                <!-- STATUS PACIENTE -->
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-check2-circle"></i>
                        </span>
                        <select id="status-input" name="STATUS" class="form-select form-select-sm">
                            <option value="">Status</option>
                            <option value="Em espera">Em espera</option>
                            <option value="Agendado">Agendado</option>
                            <option value="CanceladoO">Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- BOTÃO DE PESQUISA -->
                <div>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Pesquisar</button>
                </div>

            </form>


            <!-- RESULTADOS - TABELA -->
            <div class="w-100">

                <h5 class="mb-3">Resultados</h5>

                <div class="table-responsive overflow-auto" style="max-height: 460px;">
                    <table class="table table-hover table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Data de Nascimento</th>
                                <th>Sexo</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <!-- SEM PACIENTES -->
                        <tbody id="pacientes-tbody">
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma pesquisa realizada ainda.</td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                <!-- SELECAO DE TOTAL DE REGISTROS A SEREM MOSTRADOS -->
                <div class="d-flex justify-content-end align-items-center mt-2">
                    <label for="limite-visualizacao" class="form-label me-2 mb-0">Mostrar:</label>
                    <select id="limite-visualizacao" class="form-select form-select-sm w-auto">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
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

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE PACIENTE -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Excluir Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p id="modal-delete-nome">Deseja excluir este paciente?</p>
                    <p class="text-danger">Essa ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDIÇÃO DO PACIENTE -->
    <div class="modal fade" id="editPacienteModal" tabindex="-1" aria-labelledby="editPacienteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <!-- HEADER DO MODAL -->
                <div class="modal-header">
                    <h5 class="modal-title">Editar Paciente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">
                    <form id="editPacienteForm">

                        <!-- INFORMAÇÕES PESSOAIS -->
                        <h6>Informações Pessoais</h6>
                        <hr>

                        <div class="row g-3">

                            <!-- NOME -->
                            <div class="col-md-6 form-floating">
                                <input type="text" class="form-control" id="editPacienteNome" name="nome" placeholder="Nome">
                                <label for="editPacienteNome">Nome</label>
                            </div>

                            <!-- CPF -->
                            <div class="col-md-6 form-floating">
                                <input type="text" class="form-control" id="editPacienteCPF" name="cpf" placeholder="CPF">
                                <label for="editPacienteCPF">CPF</label>
                            </div>

                            <!-- DATA DE NASCIMENTO -->
                            <div class="col-md-6 form-floating">
                                <input type="date" class="form-control" id="editPacienteDTNASC" name="dt_nasc" placeholder="Data de Nascimento">
                                <label for="editPacienteDTNASC">Data de Nascimento</label>
                            </div>

                            <!-- SEXO -->
                            <div class="col-md-6 form-floating">
                                <select name="sexo" id="editPacienteSEXO" class="form-select" aria-label="Sexo">
                                    <option value="" selected>Selecione</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Feminino</option>
                                    <option value="O">Outro</option>
                                </select>
                                <label for="editPacienteSEXO">Sexo</label>
                            </div>

                        </div>

                        <!-- ENDEREÇO -->
                        <h6 class="mt-4">Endereço</h6>
                        <hr>

                        <div class="row g-3">

                            <!-- CEP -->
                            <div class="col-md-4 form-floating">
                                <input type="text" class="form-control" id="editPacienteCEP" name="cep" placeholder="CEP">
                                <label for="editPacienteCEP">CEP</label>
                            </div>

                            <!-- RUA - LOGRADOURO -->
                            <div class="col-md-8 form-floating">
                                <input type="text" class="form-control" id="editPacienteENDERECO" name="endereco" placeholder="Rua">
                                <label for="editPacienteENDERECO">Rua</label>
                            </div>

                            <!-- NÚMERO -->
                            <div class="col-md-4 form-floating">
                                <input type="text" class="form-control" id="editPacienteNUM" name="num" placeholder="Número">
                                <label for="editPacienteNUM">Número</label>
                            </div>

                            <!-- COMPLEMENTO -->
                            <div class="col-md-8 form-floating">
                                <input type="text" class="form-control" id="editPacienteCOMPLEMENTO" name="complemento" placeholder="Complemento">
                                <label for="editPacienteCOMPLEMENTO">Complemento</label>
                            </div>

                            <!-- BAIRRO -->
                            <div class="col-md-6 form-floating">
                                <input type="text" class="form-control" id="editPacienteBAIRRO" name="bairro" placeholder="Bairro">
                                <label for="editPacienteBAIRRO">Bairro</label>
                            </div>

                            <!-- MUNICÍPIO -->
                            <div class="col-md-6 form-floating">
                                <input type="text" id="editPacienteMUNICIPIO" name="municipio" class="form-control" placeholder="Município">
                                <label for="editPacienteMUNICIPIO">Município</label>
                            </div>

                            <!-- UNIDADE FEDERATIVA - ESTADO -->
                            <div class="col-md-2 form-floating">
                                <input type="text" class="form-control" id="editPacienteUF" name="uf" placeholder="UF">
                                <label for="editPacienteUF">UF</label>
                            </div>
                        </div>

                        <!-- CONTATO -->
                        <h6 class="mt-4">Contato</h6>
                        <hr>

                        <div class="row g-3">

                            <!-- CELULAR -->
                            <div class="col-md-6 form-floating">
                                <input type="text" id="editPacienteCELULAR" name="celular" class="form-control" placeholder="Celular" />
                                <label for="editPacienteCELULAR">Celular</label>
                            </div>

                            <!-- EMAIL -->
                            <div class="col-md-6 form-floating">
                                <input type="email" id="editPacienteEMAIL" name="email" class="form-control" placeholder="Email" />
                                <label for="editPacienteEMAIL">Email</label>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FLATPICKR -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

    <script>
        // === CONSTANTES E VARIÁVEIS GLOBAIS ===
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const dt_nasc_paciente_input = document.getElementById('DT_NASC_PACIENTE-input');
        const statusInput = document.getElementById('status-input');
        const sexoInput = document.getElementById('sexo');
        const telefoneInput = document.getElementById('telefone-input');
        const pacientesTbody = document.getElementById('pacientes-tbody');
        const limiteSelect = document.getElementById('limite-visualizacao');

        let selectedPaciente = null;
        let limiteRegistros = parseInt(limiteSelect.value) || 10;

        // === FUNÇÕES ===
        function formatarDataBR(dateStr) {
            if (!dateStr) return '-';
            const cleanedDate = dateStr.split('T')[0];
            const [year, month, day] = cleanedDate.split('-');
            return `${day}/${month}/${year}`;
        }

        // ATIVA EVENTOS DE EDIÇÃO DOS PACIENTES
        function ativarEventosEditar() {
            document.querySelectorAll('.editar-btn').forEach(button => {
                button.addEventListener('click', () => {
                    selectedPaciente = {
                        id: button.getAttribute('data-id'),
                        nome: button.getAttribute('data-nome'),
                        cpf: button.getAttribute('data-cpf'),
                        dt_nasc: button.getAttribute('data-dt_nasc'),
                        sexo: button.getAttribute('data-sexo'),
                        endereco: button.getAttribute('data-endereco'),
                        num: button.getAttribute('data-num'),
                        complemento: button.getAttribute('data-complemento'),
                        bairro: button.getAttribute('data-bairro'),
                        uf: button.getAttribute('data-uf'),
                        cep: button.getAttribute('data-cep'),
                        celular: button.getAttribute('data-celular'),
                        email: button.getAttribute('data-email'),
                        municipio: button.getAttribute('data-municipio'),
                    };
                    document.getElementById('modal-paciente-nome').textContent = `Deseja editar o paciente: ${selectedPaciente.nome}?`;
                    new bootstrap.Modal(document.getElementById('confirmEditModal')).show();
                });
            });
        }

        // ATIVA EVENTOS DE EXCLUSÃO DOS PACIENTES
        function ativarEventosDeletar() {
            document.querySelectorAll('.excluir-btn').forEach(button => {
                button.addEventListener('click', () => {
                    selectedPaciente = {
                        id: button.getAttribute('data-id'),
                        nome: button.getAttribute('data-nome'),
                    };
                    document.getElementById('modal-delete-nome').textContent = `Deseja excluir o paciente: ${selectedPaciente.nome}?`;
                    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
                });
            });
        }
        
        // ATUALIZA LIMITE DE VISUALIZAÇÃO
        function atualizarVisualizacaoLimite() {
            const linhas = pacientesTbody.querySelectorAll('tr');
            linhas.forEach((linha, idx) => {
                linha.style.display = idx < limiteRegistros ? '' : 'none';
            });
        }

        function montarQueryParams(params) {
            return Object.entries(params)
                .filter(([_, v]) => v !== null && v !== '')
                .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
                .join('&');
        }

        function buscarPacientes() {
            const filtros = {
                search: searchInput.value.trim(), // NOME OU CPF
                DT_NASC_PACIENTE: dt_nasc_paciente_input.value,
                STATUS: statusInput.value,
                SEXO_PACIENTE: sexoInput.value,
                FONE_PACIENTE: telefoneInput.value,
            };

            const queryString = montarQueryParams(filtros);

            fetch(`/psicologia/consultar-paciente/buscar?${queryString}`)
            .then(res => res.json())
            .then(pacientes => {
                pacientesTbody.innerHTML = '';
                if (pacientes.length === 0) {
                    pacientesTbody.innerHTML = `
                        <tr><td colspan="7" class="text-center">Nenhum paciente encontrado.</td></tr>
                    `;
                    return;
                }

                pacientes.forEach(paciente => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${paciente.NOME_COMPL_PACIENTE}</td>
                        <td>${paciente.CPF_PACIENTE}</td>
                        <td>${paciente.DT_NASC_PACIENTE ? formatarDataBR(paciente.DT_NASC_PACIENTE) : '-'}</td>
                        <td>${paciente.SEXO_PACIENTE ?? '-'}</td>
                        <td>${paciente.FONE_PACIENTE ?? '-'}</td>
                        <td>${paciente.E_MAIL_PACIENTE ?? '-'}</td>
                        <td>${paciente.STATUS ?? '-'}</td>
                        <td>
                            <button
                                type="button" class="btn btn-sm btn-warning editar-btn" 
                                data-id="${paciente.ID_PACIENTE}" 
                                data-nome="${paciente.NOME_COMPL_PACIENTE ?? 'Paciente'}"
                                data-cpf="${paciente.CPF_PACIENTE ?? ''}"
                                data-dt_nasc="${paciente.DT_NASC_PACIENTE ?? ''}"
                                data-sexo="${paciente.SEXO_PACIENTE ?? ''}"
                                data-endereco="${paciente.ENDERECO ?? ''}"
                                data-num="${paciente.END_NUM ?? ''}"
                                data-complemento="${paciente.COMPLEMENTO ?? ''}"
                                data-bairro="${paciente.BAIRRO ?? ''}"
                                data-uf="${paciente.UF ?? ''}"
                                data-cep="${paciente.CEP ?? ''}"
                                data-celular="${paciente.FONE_PACIENTE ?? ''}"
                                data-email="${paciente.E_MAIL_PACIENTE ?? ''}"
                                data-municipio="${paciente.MUNICIPIO ?? ''}">
                                Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-danger excluir-btn"
                                data-id="${paciente.ID_PACIENTE}"
                                data-nome="${paciente.NOME_COMPL_PACIENTE ?? 'Paciente'}">
                                Excluir
                            </button>
                        </td>
                    `;
                    pacientesTbody.appendChild(row);
                });

                ativarEventosEditar();
                ativarEventosDeletar();
                atualizarVisualizacaoLimite();
            })
            .catch(err => {
                console.error(err);
                pacientesTbody.innerHTML = `
                    <tr><td colspan="7" class="text-center text-danger">Erro ao buscar pacientes.</td></tr>
                `;
            });
        }

        // PREENCHE FORMULÁRIO DE EDIÇÃO COM DADOS DO PACIENTE SELECIONADO
        function abrirModalEdicao() {
            

            document.getElementById('editPacienteNome').value = selectedPaciente.nome;
            const cpfInput = document.getElementById('editPacienteCPF');
            cpfInput.value = selectedPaciente.cpf;
            cpfInput.readOnly = true;
            document.getElementById('editPacienteDTNASC').value = selectedPaciente.dt_nasc ? selectedPaciente.dt_nasc.split('T')[0] : '';
            document.getElementById('editPacienteSEXO').value = selectedPaciente.sexo || '';
            document.getElementById('editPacienteENDERECO').value = selectedPaciente.endereco || '';
            document.getElementById('editPacienteNUM').value = selectedPaciente.num || '';
            document.getElementById('editPacienteCOMPLEMENTO').value = selectedPaciente.complemento || '';
            document.getElementById('editPacienteBAIRRO').value = selectedPaciente.bairro || '';
            document.getElementById('editPacienteUF').value = selectedPaciente.uf || '';
            document.getElementById('editPacienteCEP').value = selectedPaciente.cep || '';
            document.getElementById('editPacienteCELULAR').value = selectedPaciente.celular || '';
            document.getElementById('editPacienteEMAIL').value = selectedPaciente.email || '';
            document.getElementById('editPacienteMUNICIPIO').value = selectedPaciente.municipio || '';

            bootstrap.Modal.getInstance(document.getElementById('confirmEditModal')).hide();
            new bootstrap.Modal(document.getElementById('editPacienteModal')).show();
        }

        function abrirModalExclusao() {
            if (!selectedPaciente) return;
            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
            new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
        }

        // Envia formulário de edição via fetch
        function enviarEdicao(e) {
            e.preventDefault();

            if (!selectedPaciente || !selectedPaciente.id) {
                alert('Paciente não selecionado.');
                return;
            }

            const dados = {
                nome: document.getElementById('editPacienteNome').value,
                cpf: document.getElementById('editPacienteCPF').value,
                dt_nasc: document.getElementById('editPacienteDTNASC').value,
                sexo: document.getElementById('editPacienteSEXO').value,
                endereco: document.getElementById('editPacienteENDERECO').value,
                num: document.getElementById('editPacienteNUM').value,
                complemento: document.getElementById('editPacienteCOMPLEMENTO').value,
                bairro: document.getElementById('editPacienteBAIRRO').value,
                uf: document.getElementById('editPacienteUF').value,
                cep: document.getElementById('editPacienteCEP').value,
                celular: document.getElementById('editPacienteCELULAR').value,
                email: document.getElementById('editPacienteEMAIL').value,
                municipio: document.getElementById('editPacienteMUNICIPIO').value,
            };

            fetch(`editar-paciente/${selectedPaciente.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(dados),
            })
                .then(response => {
                    if (!response.ok) throw new Error('Erro ao salvar dados');
                    return response.json();
                })
                .then(data => {
                    alert('Paciente atualizado com sucesso!');
                    bootstrap.Modal.getInstance(document.getElementById('editPacienteModal')).hide();
                    location.reload();
                })
                .catch(error => {
                    console.error(error);
                    alert('Erro ao atualizar paciente.');
                });
        }

        function enviarExclusao(e) {
            e.preventDefault();
            
            if (!selectedPaciente || !selectedPaciente.id) {
                alert('Paciente não selecionado.');
                return;
            }

            if (!confirm(`Tem certeza que deseja excluir o paciente: ${selectedPaciente.nome}?`)) {
                return;
            }

            fetch(`/psicologia/excluir-paciente/${selectedPaciente.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao excluir paciente.');
                return response.json();
            })
            .then(data => {
                alert('Paciente excluído com sucesso.');
                bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
                buscarPacientes(); // Recarrega tabela sem refresh
            })
            .catch(error => {
                console.error(error);
                alert('Erro ao excluir paciente.');
            });
        }

        // === EVENTOS ===

        // Submissão do formulário de busca
        searchForm.addEventListener('submit', e => {
            e.preventDefault();
            buscarPacientes();
        });

        // Mudança no select de limite
        limiteSelect.addEventListener('change', () => {
            limiteRegistros = parseInt(limiteSelect.value);
            atualizarVisualizacaoLimite();
        });

        // Clique no botão confirmar edição (modal)
        document.getElementById('confirm-edit-btn').addEventListener('click', abrirModalEdicao);

        // Submissão do formulário de edição
        document.getElementById('editPacienteForm').addEventListener('submit', enviarEdicao);

        // Submissão formulário de exclusão
        document.getElementById('confirm-delete-btn').addEventListener('click', enviarExclusao);

        // Busca inicial ao carregar página
        window.addEventListener('DOMContentLoaded', () => {
            buscarPacientes();
        });
    </script>

    <!-- SCRIPT DO FLATPICKR -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const initializeFlatpickr = () => {
                const inputEdit = document.querySelector("#editPacienteDTNASC");
                if (inputEdit._flatpickr) inputEdit._flatpickr.destroy();
                flatpickr(inputEdit, {
                    altInput: true,
                    altFormat: "d-m-Y",
                    allowInput: true,
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    locale: "pt",
                });
                const inputSearch = document.querySelector("#DT_NASC_PACIENTE-input");
                if (inputSearch._flatpickr) inputSearch._flatpickr.destroy();
                flatpickr(inputSearch, {
                    altInput: true,
                    altFormat: "d-m-Y",
                    allowInput: true,
                    dateFormat: "Y-m-d",
                    maxDate: "today",
                    locale: "pt",
                });
            };

            const editPacienteModal = document.getElementById('editPacienteModal');
            editPacienteModal.addEventListener('shown.bs.modal', initializeFlatpickr);

            initializeFlatpickr();
        });
    </script>

</body>
</html>
