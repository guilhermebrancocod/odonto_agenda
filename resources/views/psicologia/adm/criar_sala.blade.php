<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Sala</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Montserrat", sans-serif;
            background-color: #f8f9fa;
        }

        #content-wrapper {
            width: 100%;
            margin: auto;
            display: flex;
            gap: 12px;
            align-items: stretch;
            flex-direction: row;
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

        /* Telas menores */
        @media (max-width: 991.98px) {
            #content-wrapper {
                flex-direction: column;
            }
            main {
                width: 100%;
                height: auto;
            }
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

        #salas-lista {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 100%;
        }

        table {
            border-collapse: separate;
            border-spacing: 0 12px;
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

<body class="bg-body-secondary d-flex flex-column">
    @include('components.navbar')

    <div id="alert-container"></div>

    <!-- CONTAINER GERAL -->
    <div class="container px-4">

        <!-- HEADER -->
        <div class="d-flex flex-row justify-content-between align-items-center">
            <div>
                <p class="m-0 ms-2">
                    <i class="bi bi-building"></i>
                    |
                    <strong>Gerenciar Salas</strong>
                </p>
            </div>
            <div>
                <div class="profile-container">
                    <i class="bi bi-person-circle fs-2" id="profile"></i>
                </div>
            </div>
        </div>
        <!-- FIM HEADER -->

        <!-- CONTEÚDO PRINCIPAL -->
        <div id="content-wrapper" class="bg-body-secondary">

            <!-- FORMULÁRIO -->
            <main>

                @if ($errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            @foreach ($errors->all() as $erro)
                                showAlert("{{ $erro }}", 'danger');
                            @endforeach
                        });
                    </script>
                @endif

                @if(session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            showAlert("{{ session('success') }}", 'success');
                        });
                    </script>
                @endif

                <form class="needs-validation" action="{{ route('criarSala-Psicologia') }}" method="POST" novalidate>
                    @csrf
                    <input type="hidden" name="ID_CLINICA" value="1">

                    <h5 class="">Dados da Sala</h5>
                    <hr>

                    <div class="mb-3">
                        <label for="nome-sala" class="form-label text-muted" style="font-size: 14px;">
                            Descrição da Sala
                        </label>
                        <input type="text"
                               id="nome-sala"
                               name="DESCRICAO"
                               class="form-control"
                               value="{{ old('DESCRICAO', request('nome_local')) }}"
                               required>
                    </div>

                    <div class="mb-3" id="disciplina-container">
                        <label for="disciplina-sala" class="form-label" style="font-size: 14px;">Disciplina</label>
                        <select name="DISCIPLINA" id="disciplina-sala" class="form-select form-select-sm">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button id="salvar" type="submit">Salvar</button>
                    </div>
                </form>
            </main>

            <!-- LISTAGEM -->
            <main style="overflow-y:auto; max-height: 90vh;">
                <input type="text" id="search-sala" class="form-control mb-3" placeholder="Buscar sala por nome..." />

                <div id="salas-lista" style="max-height: 65vh; overflow-y:auto;">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Disciplina</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody id="salas-tbody">
                        <tr><td colspan="5" class="text-center">Carregando...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- MODAL -->
                <div class="modal fade" id="editarSalaModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div id="modal-alert-container"></div>
                            <form id="form-editar-sala">
                                <div class="modal-header">
                                    <h5 class="modal-title">Editar Sala</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit-sala-id" name="ID_SALA_CLINICA" />

                                    <div class="mb-3">
                                        <label class="form-label">Descrição</label>
                                        <input type="text" id="edit-sala-desc" name="DESCRICAO" class="form-control" required />
                                    </div>

                                    <div class="mb-3" id="edit-disciplina-container">
                                        <label class="form-label">Disciplina</label>
                                        <select name="DISCIPLINA" id="edit-sala-disc" class="form-select form-select-sm">
                                            <option id="edit-sala-disc-selected"></option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select id="edit-sala-status" name="ATIVO" class="form-select" required>
                                            <option value="S">Ativo</option>
                                            <option value="N">Inativo</option>
                                        </select>
                                    </div>
                                </div>
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
    </div>
</body>
</html>

<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

<script>

    // FUNÇÃO PARA EXIBIR ALERTAS
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alert.role = 'alert';
        alert.style.pointerEvents = 'auto';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.classList.remove('show');
            alert.classList.add('hide');
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    }

    function showModalAlert(message, type = 'danger') {
        const modalAlertContainer = document.getElementById('modal-alert-container');
        modalAlertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show m-3 mb-0">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

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
                            <td>${sala.DISCIPLINA ? sala.DISCIPLINA : '-'}</td>
                            <td>${sala.ATIVO === 'S' ? 'Ativo' : 'Inativo'}</td>
                            <td>
                                <button class="btn btn-primary btn-sm btn-editar"
                                    data-id="${sala.ID_SALA_CLINICA}"
                                    data-desc="${sala.DESCRICAO}"
                                    data-disc="${sala.DISCIPLINA}"
                                    data-status="${sala.ATIVO}">
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
                            document.getElementById('edit-sala-disc').value = btn.dataset.disc;
                            document.getElementById('edit-sala-status').value = btn.dataset.status;
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

        // === FUNÇÃO PARA EDITAR SALAS ===
        formEditarSala.addEventListener('submit', e => {
            e.preventDefault();

            const desc = document.getElementById('edit-sala-desc').value;
            const disc = document.getElementById('edit-sala-disc').value;
            const status = document.getElementById('edit-sala-status').value;
            const id = document.getElementById('edit-sala-id').value;

            fetch(`{{ route('atualizarSala-Psicologia', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', id), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    DESCRICAO: desc,
                    DISCIPLINA: disc,
                    ATIVO: status
                })
            })
            .then(res => {
                if (!res.ok) throw new Error('Erro ao salvar');
                return res.json();
            })
            .then(() => {
                showAlert('Sala atualizada com sucesso!', 'success');
                editarSalaModal.hide();
                carregarSalas(searchInput.value);
            })
            .catch(error => {
                console.error('Erro ao atualizar sala:', error);
                showModalAlert('Erro ao atualizar sala. Tente novamente.', 'danger');
            });
        }); 
    });
</script>

<!-- BUSCA DE DISCIPLINAS PARA VINCULAR AO SERVICO -->
<script>
    const select = document.getElementById('disciplina-sala');
    const container = document.getElementById('disciplina-container');

    let disciplinasCarregadas = false;
    let searchBox = document.getElementById('search-disciplina');

    // Cria o searchBox apenas uma vez
    if (!searchBox) {
        searchBox = document.createElement('input');
        searchBox.type = 'search';
        searchBox.placeholder = 'Pesquise pela Disciplina';
        searchBox.classList.add('form-control', 'mb-2');
        searchBox.id = 'search-disciplina';

        container.insertBefore(searchBox, select);

        // Filtra opções em tempo real
        searchBox.addEventListener('input', function() {
            const termo = this.value.toLowerCase();
            Array.from(select.options).forEach(opt => {
                if (opt.value === "") return;
                opt.style.display = opt.textContent.toLowerCase().includes(termo) ? '' : 'none';
            });
        });

        // Enter foca no select
        searchBox.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {

                // Informa ao User Agent que a ação padrão não será realizada
                e.preventDefault();

                select.focus();
                // opcional: abre as opções como um "dropdown"
                select.size = select.options.length; 
            }
        });
    }

    // Carrega disciplinas apenas uma vez
    if (!disciplinasCarregadas) {
        fetch('/psicologia/disciplinas-psicologia')
            .then(response => {
                if (!response.ok) throw new Error('Erro ao buscar disciplinas');
                return response.json();
            })
            .then(disciplinas => {
                select.innerHTML = '<option value=""></option>';
                disciplinas.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.DISCIPLINA;
                    option.textContent = d.DISCIPLINA + " - " + d.NOME;
                    select.appendChild(option);
                });
                disciplinasCarregadas = true;
            })
            .catch(err => {
                console.error(err);
                select.innerHTML = '<option value="">Erro ao carregar disciplinas</option>';
            });
    }

    // Inicialmente foca no searchBox
    searchBox.focus();

    // Quando o usuário seleciona uma opção, fecha o "dropdown"
    select.addEventListener('change', function() {
        select.size = 1;
    });
</script>

<!-- BUSCA DE DISCIPLINAS PARA EDITAR O SERVIÇO -->
<script>
    const editSelect = document.getElementById('edit-sala-disc');
    const editContainer = document.getElementById('edit-disciplina-container');

    let editDisciplinasCarregadas = false;
    let editSearchBox = document.getElementById('edit-search-disciplina');

    // Cria o searchBox apenas uma vez
    if (!editSearchBox) {
        editSearchBox = document.createElement('input');
        editSearchBox.type = 'search';
        editSearchBox.placeholder = 'Pesquise pela Disciplina';
        editSearchBox.classList.add('form-control', 'mb-2');
        editSearchBox.id = 'edit-search-disciplina';

        editContainer.insertBefore(editSearchBox, editSelect);

        // Filtra opções em tempo real
        editSearchBox.addEventListener('input', function() {
            const termo = this.value.toLowerCase();
            Array.from(editSelect.options).forEach(opt => {
                if (opt.value === "" || opt.id === "edit-sala-disc-selected") return;
                opt.style.display = opt.textContent.toLowerCase().includes(termo) ? '' : 'none';
            });
        });

        // Enter foca no select
        editSearchBox.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                editSelect.focus();
                editSelect.size = editSelect.options.length;
            }
        });
    }

    // Carrega disciplinas apenas uma vez
    if (!editDisciplinasCarregadas) {
        fetch('/psicologia/disciplinas-psicologia')
            .then(response => {
                if (!response.ok) throw new Error('Erro ao buscar disciplinas');
                return response.json();
            })
            .then(disciplinas => {
                // Mantém a primeira option (selected do serviço)
                const selectedOption = document.getElementById('edit-sala-disc-selected');
                editSelect.innerHTML = '';
                if (selectedOption) editSelect.appendChild(selectedOption);

                disciplinas.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.DISCIPLINA;
                    option.textContent = d.DISCIPLINA + " - " + d.NOME;
                    editSelect.appendChild(option);
                });
                editDisciplinasCarregadas = true;
            })
            .catch(err => {
                console.error(err);
                editSelect.innerHTML = '<option value="">Erro ao carregar disciplinas</option>';
            });
    }

    // Inicialmente foca no searchBox
    editSearchBox.focus();

    // Quando o usuário seleciona uma opção, fecha o "dropdown"
    editSelect.addEventListener('change', function() {
        editSelect.size = 1;
    });
</script>

@php
    // Pega os dados do usuário da sessão
    $usuario = session('usuario');
@endphp

<script>
    // Converte para objeto JS
    const usuario = @json($usuario->map(function($u) {
        return [
            'id_usuario_clinica' => $u->ID_USUARIO_CLINICA,
            'id_clinica' => $u->ID_CLINICA,
            'sit_usuario' => $u->SIT_USUARIO
        ];
    }));
</script>

</body>
</html>
