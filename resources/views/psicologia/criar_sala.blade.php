<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Serviço</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Montserrat", sans-serif;
            background-color: #f8f9fa;
        }
        #content-wrapper {
            width: 80vw;
            height: 90vh;
            margin: auto;
            display: flex;
            gap: 24px;
            overflow: hidden;
            align-items: stretch;
        }
        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            width: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1.8px solid #dee2e6;
        }
        form {
            flex-grow: 1;
            overflow-y: auto;
        }
        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 16px;
        }
        h5 {
            margin-bottom: 12px;
            font-weight: 600;
        }
        #salvar {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        #salvar:hover {
            background-color: #0056b3;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        #servicos-lista {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 100%;
        }

        /* Estilo atualizado para a tabela ficar com linhas tipo card */
        table {
            border-collapse: separate;
            border-spacing: 0 12px; /* espaço vertical entre as linhas */
            width: 100%;
        }
        thead tr th:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        thead tr th:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        tbody tr {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-radius: 12px;
            transition: box-shadow 0.3s ease;
        }
        tbody tr:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        tbody tr td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        tbody tr td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        @media (max-width: 768px) {
            #content-wrapper {
                flex-direction: column;
                width: 95vw;
                height: auto;
            }
            main {
                width: 100%;
            }
        }
        /* ALERT FIXO NO TOPO */
        #alert-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1055;
            width: auto;
            max-width: 90%;
            pointer-events: none;
        }
    </style>
</head>

<body>
@include('components.navbar')

<div id="alert-container"></div>

<div id="content-wrapper">
    <main>
        <div class="text-center">
            <h2>Cadastro de Sala</h2>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showAlert("{{ session('success') }}", 'success');
                });
            </script>
        @endif

        <!-- FORMULÁRIO DE CRIAÇÃO DE SALA -->
        <form class="needs-validation" action="{{ route('criarSala-Psicologia') }}" method="POST" novalidate>

            @csrf

            <!-- ID DA CLINICA - NO CASO PSICOLOGIA -->
            <input type="hidden" name="ID_CLINICA" value="1">

            <!-- TÍTULO -->
            <h5 class="mt-3">Dados da Sala</h5>

            <hr>

            <!-- NOME DA SALA -->
            <div class="mb-3">
                <label for="nome-sala" class="form-label text-muted" style="font-size: 14px;">
                    Descrição da Sala
                </label>
                <input type="text"
                       id="nome-sala"
                       name="DESCRICAO"
                       class="form-control"
                       value="{{ old('DESCRICAO') }}"
                       required>
            </div>

            <!-- BOTÃO DE SALVAR | SUBMIT -->
            <div class="text-end">
                <button id="salvar" type="submit">Salvar</button>
            </div>
            
        </form>
    </main>

    <main style="overflow-y:auto; max-height: 90vh;">

        <!-- TITULO LISTAGEM DE SALAS -->
        <h2 class="text-center mb-4">Consulta e Edição de Salas</h2>

        <!-- INPUT DE BUSCA -->
        <input type="text" id="search-sala" class="form-control mb-3" placeholder="Buscar sala por nome..." />

        <!-- LISTAGEM DE SALAS -->
        <div id="salas-lista" style="max-height: 65vh; overflow-y:auto;">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody id="salas-tbody">
                <tr><td colspan="5" class="text-center">Carregando...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="modal fade" id="editarSalaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                    <div id="modal-alert-container"></div>
                    <form id="form-editar-sala">

                        <!-- HEADER DO MODAL -->
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Sala</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- CORPO DO MODAL -->
                        <div class="modal-body">

                            <input type="hidden" id="edit-sala-id" name="ID_SALA_CLINICA" />

                            <!-- EDIÇÃO DE DESCRIÇÃO DA SALA -->
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <input type="text" id="edit-sala-desc" name="DESCRICAO" class="form-control" required />
                            </div>
                            
                            <!-- EDIÇÃO DE STATUS DA SALA -->
                            <div>
                                <label class="form-label">Status</label>
                                <textarea id="edit-sala-status" name="ATIVO" class="form-control"></textarea>
                            </div>
                        </div>

                        <!-- RODAÉ DO MODAL -->
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="btn-deletar-sala">Excluir</button>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </main>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', () => {

        // === VARIÁVEIS GLOBAIS ===
        const salasTBody = document.getElementById('salas-tbody');
        const searchInput = document.getElementById('search-sala');
        const editarSalaModal = new bootstrap.Modal(document.getElementById('editarSalaModal'));
        const formEditarSala = document.getElementById('form-editar-sala');

        // === FUNÇÃO PARA CARREGAR SALAS ===
        function carregarSalas(search = '') {
                fetch(`/psicologia/salas/listar?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(salas => {
                    salasTBody.innerHTML = '';

                    // NENHUMA SALA ENCONTRADA
                    if (salas.length === 0) {
                        salasTBody.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center">Nenhuma sala encontrada</td>
                            </tr>
                        `;
                        return;
                    }

                    // CASO ENCONTRE SALAS
                    salas.forEach(sala => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${sala.DESCRICAO}</td>
                            <td>${sala.ATIVO ? 'Ativo' : 'Inativo'}</td>
                            <td>
                                <button class="btn btn-primary btn-sm btn-editar"
                                    data-id="${sala.ID_SALA_CLINICA}"
                                    data-desc="${sala.DESCRICAO}">
                                    Editar
                                </button>
                            </td>
                        `;
                        salasTBody.appendChild(tr);
                    });

                    document.querySelectorAll('.btn-editar').forEach(btn => {
                        btn.addEventListener('click', () => {
                            document.getElementById('edit-sala-id').value = btn.dataset.id;
                            document.getElementById('edit-sala-desc').value = btn.dataset.desc;
                            editarSalaModal.show();
                        });
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar salas:', error);
                    salasTBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger">Erro ao carregar salas</td>
                        </tr>
                    `;
                });
        }

        // === CHAMADA INICIAL ===
        carregarSalas();

        // === PESQUISA AO DIGITAR ===
        searchInput.addEventListener('input', () => {
            carregarSalas(searchInput.value);
        });

    });
</script>


</body>
</html>
