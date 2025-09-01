<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Agendamento</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }
        .form-label {
            font-weight: 600;
        }
        .flatpickr-input {
            background-image: none !important;
        }
        .container {
            overflow-y: auto;
        }
    </style>

    <style>
        .shadow-dark {
            box-shadow: 0 0.75rem 1.25rem rgba(0,0,0,0.4) !important;
        }
        /* Animação para alertas */
        @keyframes slideDownFadeOut {
            0%   { transform: translate(-50%, -100%); opacity: 0; }
            10%  { transform: translate(-50%, 0); opacity: 1; }
            90%  { transform: translate(-50%, 0); opacity: 1; }
            100% { transform: translate(-50%, -100%); opacity: 0; }
        }
        .animate-alert {
            animation: slideDownFadeOut 5s ease forwards;
            z-index: 1050;
        }
        .required-field {
            color: #dc3545; /* Cor de perigo do Bootstrap */
        }
    </style>
</head>
<body>

    @include('components.navbar')

    <div class="container mt-4" style="max-width: 600px;">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Editar Agendamento</h2>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3 animate-alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('agendamento.update', $agendamento->ID_AGENDAMENTO) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="id_agendamento" value="{{ old('id_agendamento', $agendamento->ID_AGENDAMENTO) }}">

                    <input type="hidden" id="id_clinica" name="id_clinica" value="{{ old('id_clinica', $agendamento->ID_CLINICA) }}">

                    <input type="hidden" name="id_paciente" value="{{ old('id_paciente', $agendamento->ID_PACIENTE) }}">
                    
                    <!-- PACIENTE -->
                    <div class="mb-3">
                        <label for="paciente" class="form-label">Paciente</label>
                        <input type="text" id="paciente" class="form-control" value="{{ $agendamento->paciente->NOME_COMPL_PACIENTE ?? '-' }}" disabled>
                    </div>

                    <!-- SERVIÇO -->
                    <div class="mb-3">
                        <label for="servico" class="form-label">Serviço</label>
                        <input type="text" id="servico" class="form-control" value="{{ old('servico', $agendamento->servico->SERVICO_CLINICA_DESC ?? '-') }}">

                        <!-- LISTAGEM DE SERVIÇOS PÓS PESQUISA PARA SELEÇÃO -->
                        <input type="hidden" name="id_servico" id="id_servico" value="{{ old('id_servico', $agendamento->ID_SERVICO) }}">
                        <div id="servicos-list" class="list-group position-absolute w-75" style="z-index: 100"></div>
                    </div>

                    <!-- LOCAL - SALA -->
                    <div class="mb-3">
                        <label for="local" class="form-label">Local <span class="text-danger">*</span></label>
                        <input type="text" id="local" name="local" class="form-control @error('local') is-invalid @enderror" 
                               value="{{ old('local', $agendamento->LOCAL) }}" placeholder="Digite o local" required>
                        @error('local')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- LISTAGEM DE SALAS PÓS PESQUISA PARA SELEÇÃO -->
                        <div id="locais-list" class="list-group position-absolute w-75" style="z-index: 100"></div>
                    </div>

                    <!-- DATA -->
                    <div class="mb-3">
                        <label for="date" class="form-label">Data <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                                   value="{{ old('date', $agendamento->DT_AGEND) }}" placeholder="Escolha a data" required>
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            @error('date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- HORA INÍCIO -->
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Hora Início <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="start_time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                   value="{{ old('start_time', $agendamento->HR_AGEND_INI) }}" placeholder="HH:mm" required>
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            @error('start_time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- HORA FIM -->
                    <div class="mb-3">
                        <label for="end_time" class="form-label">Hora Fim <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="end_time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                   value="{{ old('end_time', $agendamento->HR_AGEND_FIN) }}" placeholder="HH:mm" required>
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            @error('end_time')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- STATUS -->
                    <div class="mb-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Agendado" {{ old('status', $agendamento->STATUS_AGEND) == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                            <option value="Em atendimento" {{ old('status', $agendamento->STATUS_AGEND) == 'Em atendimento' ? 'selected' : '' }}>Em atendimento</option>
                            <option value="Cancelado" {{ old('status', $agendamento->STATUS_AGEND) == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                            <option value="Finalizado" {{ old('status', $agendamento->STATUS_AGEND) == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- VALOR DO AGENDAMENTO -->
                    <div class="mb-4">
                        <label for="" class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control" name="valor_agend" id="valor_agend" value="{{ old('valor_agend', $agendamento->VALOR_AGEND) }}">
                        </div>
                    </div>

                    <!-- OBSERVAÇÕES -->
                    <div class="mb-4">
                        <label for="OBSERVACOES" class="form-label">Observações</label>
                        <textarea name="observacoes" id="observacoes" class="form-control" placeholder="">{{ old('observacoes', $agendamento->OBSERVACOES) }}</textarea>
                    </div>

                    <input type="hidden" id="motivo_cancelamento" name="mensagem">

                    <!-- BOTÕES -->
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-success flex-grow-1 fw-bold" id="btnSalvarAlteracoes">
                            <i class="bi bi-save me-2"></i> Salvar Alterações
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-outline-secondary flex-grow-1 fw-bold" 
                            onclick="window.history.back();"
                        >
                            <i class="bi bi-x-circle me-2"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE MENSAGEM DE CANCELAMENTO -->
    <div class="modal fade" id="motivoCancelamento" tabindex="-1">
        <div class="modal-dialog">

            <div class="modal-content">
                
                <div class="modal-header">
                    <h5 class="modal-title">Motivo do Cancelamento</h5>
                </div>

                <div class="modal-body">

                <div class="mb-3">
                    <label for="text-cancelamento" class="form-label">Motivo: </label>
                    <input type="text" class="form-control" id="text-cancelamento">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="btnMensagemCancelamento">Salvar</button>
                </div>
                
            </div>
            </div>
        </div>
    </div>
    
</body>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Flatpickr pt-br -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <!-- SCRIPT FLATPICKR -->
    <script>
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "pt",
            minDate: "today",
        });

        flatpickr("#start_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i",
        });

        flatpickr("#end_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i",
        });
    </script>

    <!-- SCRIPT DE BUSCA DE SERVIÇOS -->
    <script>
        const servicoInput = document.getElementById('servico');
        const servicosList = document.getElementById('servicos-list');

        let timeout = null;

        servicoInput.addEventListener('input', () => {
            clearTimeout(timeout);

            timeout = setTimeout(() => {
            const query = servicoInput.value.trim();

            // SEM VALORES PASSADOS PARA A QUERY
            if (!query) {
                servicosList.innerHTML = '';
                document.getElementById('id_servico').value = '';
                return;
            }

            fetch(`/psicologia/pesquisar-servico?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(servicos => {

                    // ZERA VALOR DA LISTA DE SERVIÇOS PARA INCLUSÃO
                    servicosList.innerHTML = '';

                    // CASO NENHUM SERVIÇO ENCONTRADO
                    if (servicos.length === 0) {
                        servicosList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhum serviço encontrado</button>`;
                        document.getElementById('id_servico').value = '';
                        return;
                    }

                    const servicoExato = servicos.find(s => s.SERVICO_CLINICA_DESC.toLowerCase() === query.toLowerCase());

                    // CASO O SERVIÇO EXATO SEJA DIGITADO
                    if (servicoExato) {
                        aoSelecionarServico(servicoExato);
                        servicosList.innerHTML = '';
                        return;
                    }

                    document.getElementById('id_servico').value = '';

                    servicos.forEach(servico => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.textContent = servico.SERVICO_CLINICA_DESC;
                        item.addEventListener('click', () => {
                            aoSelecionarServico(servico);
                            servicosList.innerHTML = '';
                        });
                        servicosList.appendChild(item);
                    });
                })
                .catch(error => {
                    console.error(error);
                    servicosList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger">Erro ao buscar serviços</button>`;
                });
        }, 300);
        })

        // FECHA A LISTA DE SERVIÇOS AO CLICAR FORA
        document.addEventListener('click', (e) => {
            if (!servicoInput.contains(e.target) && !servicosList.contains(e.target)) {
                servicosList.innerHTML = '';
            }
        });

        // FUNÇÃO CHAMADA AO SELECIONAR SERVIÇO
        function aoSelecionarServico(servico) {
            document.getElementById('servico').value = servico.SERVICO_CLINICA_DESC;
            document.getElementById('id_servico').value = servico.ID_SERVICO_CLINICA;
        }

    </script>

    <!-- SCRIPT BUSCA DE SALAS -->
    <script>
        const localInput = document.getElementById('local');
        const localList = document.getElementById('locais-list');

        localInput.addEventListener('input', (event) => {
            timeout = setTimeout(() => {
                const query = localInput.value.trim();

                if(!query) {
                    localList.innerHTML = '';
                    document.getElementById('id_servico').value = '';
                    return;
                }
            
                fetch(`/psicologia/pesquisar-local?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(locais => {
                        localList.innerHTML = '';

                        if (locais.length === 0) {
                            localList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhum local encontrado</button>`;
                            return;
                        }
                        
                    const localExato = locais.find(l => l.DESCRICAO.toLowerCase() === query.toLowerCase());

                    if (localExato) {
                        aoSelecionarLocal(localExato);
                        localList.innerHTML = '';
                        return;
                    }

                    locais.forEach(local => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-local-option', 'list-group-item-action');
                        item.textContent = local.DESCRICAO;
                        item.addEventListener('click', () => {
                            aoSelecionarLocal(local);
                            localList.innerHTML = '';
                        });
                        localList.appendChild(item);
                    });
                    })
                    .catch(error => {
                    console.error(error);
                    localList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger">Erro ao buscar locais</button>`;
                });
            }, 300)


        });

        function aoSelecionarLocal(local) {
            document.getElementById('local').value = local.DESCRICAO;
        }
    </script>

    <!-- SCRIPT CONFIRMAÇÃO DE ALTERAÇÕES -->
    <script>
        document.getElementById('btnSalvarAlteracoes').addEventListener('click', function (event) {
            const novoStatus = document.getElementById('status').value;

            if (novoStatus == "Cancelado") {
            event.preventDefault(); // Impede o envio imediato do form
                const modalCancelamento = new bootstrap.Modal(document.getElementById('motivoCancelamento'));
                modalCancelamento.show();
            }
        });

        document.getElementById('btnMensagemCancelamento').addEventListener('click', function() {
            const motivoCancelamento = document.getElementById('motivo_cancelamento').value = document.getElementById('text-cancelamento').value;
            document.querySelector('form').submit();
        })

    </script>

</html>