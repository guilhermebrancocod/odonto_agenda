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

        /* Container com 70% da largura da tela e altura quase toda */
        #content-wrapper {
            width: 70vw;
            height: 90vh; /* quase toda altura */
            margin: auto; /* centralizar horizontalmente */
            display: flex;
            gap: 24px;
            overflow: hidden; /* evita scroll externo */
            align-items: stretch; /* cards com mesma altura */
        }

        /* Cada main ocupa metade do container */
        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            width: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Formulario e conteúdo crescem para preencher verticalmente */
        form {
            flex-grow: 1;
            overflow-y: auto; /* scroll se form ficar muito alto */
        }

        /* Ajusta o titulo */
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

        /* Lista de serviços com scroll vertical */
        #servicos-lista {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 100%;
        }

        /* Tabela cheia na largura */
        table {
            width: 100%;
        }

        @media (max-width: 768px) {
            #content-wrapper {
                flex-direction: column;
                width: 90vw;
                height: auto;
            }

            main {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <!-- Formulário de criação -->
        <main>
            <div class="text-center">
                <h2>Cadastro de Serviço</h2>
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
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form class="needs-validation" action="{{ route('criarServico-Psicologia') }}" method="POST" novalidate>
                @csrf

                <input type="hidden" name="ID_CLINICA" value="1">

                <h5>Dados do Serviço</h5>
                <hr>

                <!-- DESCRIÇÃO DO SERVICO - NOME -->
                <div class="mb-3">
                    <label for="nome-servico" class="form-label text-muted" style="font-size: 14px;">
                        Nome do Serviço
                    </label>
                    <input type="text"
                        id="nome-servico"
                        name="SERVICO_CLINICA_DESC"
                        class="form-control"
                        required>
                </div>

                <!-- CÓD INTERNO SERVICO CLINICA -->
                <div class="mb-3">
                    <label for="cod-interno-servico" class="form-label text-muted" style="font-size: 14px;">
                        Código Interno do Serviço
                    </label>
                    <input type="number"
                        id="cod-interno-servico"
                        name="COD_INTERNO_SERVICO_CLINICA"
                        class="form-control"
                        min="0"
                        value="0"
                        required>
                </div>

                <div class="text-end">
                    <button id="salvar" type="submit">
                        Salvar
                    </button>
                </div>
            </form>
        </main>

        <!-- Card de consulta e edição -->
        <main style="overflow-y:auto; max-height: 80vh;">
            <h2 class="text-center mb-4">Consulta e Edição de Serviços</h2>

            <input type="text" id="search-servico" class="form-control mb-3" placeholder="Buscar serviço por nome..." />

            <div id="servicos-lista" style="max-height: 65vh; overflow-y:auto;">
                <!-- Lista de serviços carregada via JS -->
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Código Interno</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="servicos-tbody">
                        <tr><td colspan="4" class="text-center">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal para edição -->
            <div class="modal fade" id="editarServicoModal" tabindex="-1" aria-labelledby="editarServicoModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="form-editar-servico">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editarServicoModalLabel">Editar Serviço</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" id="edit-servico-id" name="ID_SERVICO_CLINICA" />
                                <div class="mb-3">
                                    <label for="edit-servico-desc" class="form-label">Descrição</label>
                                    <input type="text" id="edit-servico-desc" name="SERVICO_CLINICA_DESC" class="form-control" required />
                                </div>
                                <div class="mb-3">
                                    <label for="edit-servico-cod" class="form-label">Código Interno</label>
                                    <input type="number" id="edit-servico-cod" name="COD_INTERNO_SERVICO_CLINICA" class="form-control" min="0" required />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> <!-- FECHOU O MODAL CORRETAMENTE -->
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const servicosTbody = document.getElementById('servicos-tbody');
            const searchInput = document.getElementById('search-servico');

            const editarServicoModal = new bootstrap.Modal(document.getElementById('editarServicoModal'));
            const formEditarServico = document.getElementById('form-editar-servico');

            function carregarServicos(search = '') {
                fetch(`/psicologia/servicos?search=${encodeURIComponent(search)}`)
                    .then(res => res.json())
                    .then(servicos => {
                        servicosTbody.innerHTML = '';
                        if (servicos.length === 0) {
                            servicosTbody.innerHTML = `<tr><td colspan="4" class="text-center">Nenhum serviço encontrado.</td></tr>`;
                            return;
                        }
                        servicos.forEach(s => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${s.ID_SERVICO_CLINICA}</td>
                                <td>${s.SERVICO_CLINICA_DESC}</td>
                                <td>${s.COD_INTERNO_SERVICO_CLINICA}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-editar" data-id="${s.ID_SERVICO_CLINICA}" data-desc="${s.SERVICO_CLINICA_DESC}" data-cod="${s.COD_INTERNO_SERVICO_CLINICA}">Editar</button>
                                </td>
                            `;
                            servicosTbody.appendChild(tr);
                        });

                        // Adiciona evento nos botões de editar
                        document.querySelectorAll('.btn-editar').forEach(btn => {
                            btn.addEventListener('click', () => {
                                document.getElementById('edit-servico-id').value = btn.dataset.id;
                                document.getElementById('edit-servico-desc').value = btn.dataset.desc;
                                document.getElementById('edit-servico-cod').value = btn.dataset.cod;
                                editarServicoModal.show();
                            });
                        });
                    })
                    .catch(() => {
                        servicosTbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Erro ao carregar serviços.</td></tr>`;
                    });
            }

            // Carregar lista ao iniciar
            carregarServicos();

            // Buscar ao digitar
            searchInput.addEventListener('input', () => {
                carregarServicos(searchInput.value);
            });

            // Submissão do formulário de edição
            formEditarServico.addEventListener('submit', e => {
                e.preventDefault();

                const id = document.getElementById('edit-servico-id').value;
                const desc = document.getElementById('edit-servico-desc').value;
                const cod = document.getElementById('edit-servico-cod').value;

                fetch(`/psicologia/servicos/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        SERVICO_CLINICA_DESC: desc,
                        COD_INTERNO_SERVICO_CLINICA: Number(cod)  // converte para número
                    })
                })
                .then(res => {
                    if (!res.ok) throw new Error('Erro ao salvar');
                    return res.json();
                })
                .then(() => {
                    alert('Serviço atualizado com sucesso!');
                    editarServicoModal.hide();
                    carregarServicos(searchInput.value);
                })
                .catch(() => {
                    alert('Erro ao atualizar serviço.');
                });
            });
        });

    </script>
</body>

</html>
