 <!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Serviço</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
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
            width: 78vw;
            height: 100h;
            margin: auto;
            margin-top: 10px;
            margin-bottom: 10px;
            display: flex;
            gap: 12px;
            overflow: hidden;
            align-items: stretch;
            flex-direction: row; /* mantém lado a lado em telas grandes */
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

        /* Para telas menores que 992px */
        @media (max-width: 991.98px) {
            #content-wrapper {
                flex-direction: column; /* empilha os main */
            }

            main {
                width: 100%; /* ocupa toda a largura */
                height: calc(50% - 12px); /* divide igualmente a altura considerando o gap */
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
        #servicos-lista {
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
                width: 90vw;
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
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showAlert("{{ session('success') }}", 'success');
                });
            </script>
        @endif

        <!-- FORMULÁRIO DE CRIAÇÃO DE SERVIÇO -->
        <form class="needs-validation" action="{{ route('criarServico-Psicologia') }}" method="POST" novalidate id="form-criar-servico">

            @csrf

            <!-- ID DA CLINICA - NO CASO PSICOLOGIA -->
            <input type="hidden" name="ID_CLINICA" value="1">

            <!-- TÍTULO -->
            <h5 class="mt-3">Dados do Serviço</h5>

            <hr>

            <!-- NOME DO SERVIÇO -->
            <div class="mb-3">
                <label for="nome-servico" class="form-label text-muted" style="font-size: 14px;">
                    Nome do Serviço
                </label>
                <input type="text"
                       id="nome-servico"
                       name="SERVICO_CLINICA_DESC"
                       class="form-control"
                       value="{{ old('SERVICO_CLINICA_DESC') }}"
                       required>
            </div>

            <!-- CÓDIGO INTERNO DO SERVIÇO -->
            <div class="mb-3">
                <label for="cod-interno-servico" class="form-label text-muted" style="font-size: 14px;">
                    Código Interno do Serviço
                </label>
                <input type="number"
                       id="cod-interno-servico"
                       name="COD_INTERNO_SERVICO_CLINICA"
                       class="form-control"
                       value="{{ old('COD_INTERNO_SERVICO_CLINICA') }}">
            </div>

            <!-- DISCIPLINA PARA VINCULAR AO SERVIÇO -->
            <div class="mb-3" id="disciplina-container">
                <label for="disciplina-servico" class="form-label" style="font-size: 14px;">Disciplina</label>
                <select name="DISCIPLINA" id="disciplina-servico" class="form-select form-select-sm">
                    <option value=""></option>
                    <!-- Outras opções serão inseridas dinamicamente -->
                </select>
            </div>

            <!-- VALOR DO SERVIÇO -->
            <div class="mb-3">
                <label for="valor-servico" class="form-label">Valor do Serviço</label>
                <div class="input-group">
                    <span class="input-group-text">R$</span>
                    <input type="text" id="valor-servico" name="VALOR_SERVICO" class="form-control" placeholder="0,00" value="{{ old('VALOR_SERVICO') }}">
                </div>
            </div>

            <!-- TEMPO DE RECORRÊNCIA PADRÃO -->
            <div class="mb-3">
                <label for="tempo_recorrencia_meses" class="form-label">
                    Tempo de recorrência padrão (meses)
                </label>
                <input type="number" min="0" step="1" name="TEMPO_RECORRENCIA_MESES" id="edit-tempo_recorrencia_meses" class="form-control" placeholder="Ex: 6" value="{{ old('tempo_recorrencia_meses') }}">
                <small class="text-muted">Deixe em branco ou 0 se o serviço não tiver recorrência padrão.</small>
            </div>

            <!-- OBSERVACAO DO SERVIÇO -->
            <div class="mb-3">
                <label for="observacao-servico" class="form-label">Observações</label>
                <textarea id="observacao-servico" name="OBSERVACAO" class="form-control" value="{{ old('OBSERVACAO') }}"></textarea>
            </div>

            <!-- BOTÃO DE SALVAR | SUBMIT -->
            <div class="text-end">
                <button id="salvar" type="submit">Salvar</button>
            </div>
            
        </form>
    </main>

    <main style="overflow-y:auto; max-height: 90vh;">

        <!-- TITULO LISTAGEM DE SERVICOS -->
        <h2 class="text-center mb-4">Consulta e Edição de Serviços</h2>

        <!-- INPUT DE BUSCA -->
        <input type="text" id="search-servico" class="form-control mb-3" placeholder="Buscar serviço por nome..." />

        <div id="servicos-lista" style="max-height: 65vh; overflow-y:auto;">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Código Interno</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody id="servicos-tbody">
                <tr><td colspan="5" class="text-center">Carregando...</td></tr>
                </tbody>
            </table>
        </div>

        {{-- MODAL DE EDIÇÃO DE SERVIÇO --}}
        <div class="modal fade" id="editarServicoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div id="modal-alert-container"></div>
                    <form id="form-editar-servico">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar Serviço</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-servico-id" name="ID_SERVICO_CLINICA" />
                            <div class="mb-3">
                                <label class="form-label">Descrição</label>
                                <input type="text" id="edit-servico-desc" name="SERVICO_CLINICA_DESC" class="form-control" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Código Interno</label>
                                <input type="number" id="edit-servico-cod" name="COD_INTERNO_SERVICO_CLINICA" class="form-control"/>
                            </div>

                            <div class="mb-3" id="edit-disciplina-container">
                                <label class="form-label">Disciplina</label>
                                <select name="DISCIPLINA" id="edit-servico-disc" class="form-select form-select-sm">
                                    <option id="edit-servico-disc-selected"></option>
                                    <!-- Outras opções serão inseridas dinamicamente -->
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Valor Serviço</label>
                                <input type="text" id="edit-valor-servico" name="VALOR_SERVICO" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="" class="form-label">
                                    Tempo de recorrência padrão (meses)
                                </label>
                                <input type="number" min="0" step="1" id="edit-tempo-recorrencia-meses" class="form-control" placeholder="Ex: 6">
                            </div>                      
                            <div class="mb-3">
                                <label for="observacao-servico" class="form-label">Observações</label>
                                <textarea id="edit-observacao-servico" name="OBSERVACAO" class="form-control"></textarea>
                            </div>
                            <div class="d-none form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="edit-permite-simultaneo" name="PERMITE_ATENDIMENTO_SIMULTANEO">
                                <label class="form-check-label" for="edit-permite-simultaneo">
                                    Permite atendimento simultâneo
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="btn-deletar-servico">Excluir</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-criar-servico');
        form.addEventListener('keydown', function(e) {
            if(e.key === 'Enter') {
                e.preventDefault();
            }
        })
    });
</script>

<script>
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
                    servicosTbody.innerHTML = `<tr><td colspan="5" class="text-center">Nenhum serviço encontrado.</td></tr>`;
                    return;
                }
                servicos.forEach(s => {
                    const valorFormatado = parseFloat(s.VALOR_SERVICO ?? 0).toFixed(2);
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${s.SERVICO_CLINICA_DESC}</td>
                        <td>${s.COD_INTERNO_SERVICO_CLINICA}</td>
                        <td>R$ ${valorFormatado}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-editar"
                                data-id="${s.ID_SERVICO_CLINICA}"
                                data-desc="${s.SERVICO_CLINICA_DESC}"
                                data-disc="${s.DISCIPLINA}"
                                data-cod="${s.COD_INTERNO_SERVICO_CLINICA}"
                                data-valor="${s.VALOR_SERVICO ?? 0}"
                                data-observacao="${s.OBSERVACAO ? s.OBSERVACAO : ''}"
                                data-tempo="${s.TEMPO_RECORRENCIA_MESES ?? ''}"
                            >
                                Editar
                            </button>
                        </td>
                    `;
                    servicosTbody.appendChild(tr);
                });

                document.querySelectorAll('.btn-editar').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.getElementById('edit-servico-id').value = btn.dataset.id;
                        document.getElementById('edit-servico-desc').value = btn.dataset.desc;

                        // Se for '--', envia vazio para o campo para não quebrar a validação
                        // VALORES ATUAIS PARA EDIÇÃO NO MODAL DE EDIÇÃO    
                        document.getElementById('edit-servico-cod').value = (btn.dataset.cod === '--') ? '' : btn.dataset.cod;
                        document.getElementById('edit-valor-servico').value = btn.dataset.valor;
                        document.getElementById('edit-permite-simultaneo').checked = (btn.dataset.permite === 'S');
                        document.getElementById('edit-observacao-servico').value = btn.dataset.observacao ?? '';
                        document.getElementById('edit-tempo-recorrencia-meses').value = btn.dataset.tempo ?? '';
                        document.getElementById('edit-servico-disc').value = btn.dataset.disc ?? '';
                        editarServicoModal.show();
                    });
                });
            })
            .catch(() => {
                servicosTbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Erro ao carregar serviços.</td></tr>`;
            });
    }

    carregarServicos();

    searchInput.addEventListener('input', () => {
        carregarServicos(searchInput.value);
    });

    formEditarServico.addEventListener('submit', e => {
        e.preventDefault();

        const id = document.getElementById('edit-servico-id').value;
        const desc = document.getElementById('edit-servico-desc').value;
        const disc = document.getElementById('edit-servico-disc').value;
        const cod = document.getElementById('edit-servico-cod').value;
        let valor = document.getElementById('edit-valor-servico').value.trim();
        const observacao = document.getElementById('edit-observacao-servico').value;
        const tempoRecorrencia = document.getElementById('edit-tempo-recorrencia-meses').value;

        valor = valor.replace(',', '.');
        valor = parseFloat(valor);
        if (isNaN(valor)) valor = 0;

        const permiteSimultaneo = document.getElementById('edit-permite-simultaneo').checked ? 'S' : 'N';

        fetch(`/psicologia/servicos/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                SERVICO_CLINICA_DESC: desc,
                COD_INTERNO_SERVICO_CLINICA: cod,
                VALOR_SERVICO: valor,
                DISCIPLINA: disc,
                PERMITE_ATENDIMENTO_SIMULTANEO: permiteSimultaneo,
                OBSERVACAO: observacao,
                TEMPO_RECORRENCIA_MESES: tempoRecorrencia
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Erro ao salvar');
            return res.json();
        })
        .then(() => {
            showAlert('Serviço atualizado com sucesso!', 'success');
            editarServicoModal.hide();
            carregarServicos(searchInput.value);
        })
        .catch(() => {
            showModalAlert('Erro ao atualizar serviço. Verifique os campos ou tente novamente.', 'danger');
        });
    });

    document.getElementById('btn-deletar-servico').addEventListener('click', () => {
        if (!confirm('Tem certeza que deseja excluir este serviço?')) return;
        const id = document.getElementById('edit-servico-id').value;

        fetch(`/psicologia/servicos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(async res => {
            if (!res.ok) {
                const data = await res.json();
                throw new Error(data.message || 'Erro ao excluir.');
            }
            return res.json();
        })
        .then(() => {
            showAlert('Serviço excluído com sucesso!', 'success');
            editarServicoModal.hide();
            carregarServicos(searchInput.value);
        })
        .catch(err => {
            showModalAlert(err.message, 'warning');
        });
    });
});
</script>

<!-- BUSCA DE DISCIPLINAS PARA VINCULAR AO SERVICO -->
<script>
    const select = document.getElementById('disciplina-servico');
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
    const editSelect = document.getElementById('edit-servico-disc');
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
                if (opt.value === "" || opt.id === "edit-servico-disc-selected") return;
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
                const selectedOption = document.getElementById('edit-servico-disc-selected');
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

{{-- CONTROLA INPUT DE VALOR PARA PERMITIR APENAS NÚMERO E VÍRGULA --}}
<script>
    const valorServicoInput = document.getElementById('valor-servico');

    valorServicoInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^\d,]/g, '');

        // Permitir apenas uma vírgula
        const parts = this.value.split(',');
        if (parts.length > 2) {
            this.value = parts[0] + ',' + parts[1];
        }
    });
</script>

{{-- CONTROLA INPUT DE VALOR PARA PERMITIR APENAS NÚMERO E VÍRGULA --}}
<script>
    const editValorServicoInput = document.getElementById('edit-valor-servico');

    editValorServicoInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^\d,]/g, '');

        // Permitir apenas uma vírgula
        const parts = this.value.split(',');
        if (parts.length > 2) {
            this.value = parts[0] + ',' + parts[1];
        }
    })
</script>

</body>
</html>
