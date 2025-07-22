<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon_faesa.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        html, body { height: 100%; margin: 0; }
        #content-wrapper {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        #servicos-list button {
            cursor: pointer;
        }

         #recorrenciaCampos.show {
            display: flex !important;
            animation: fadeInSlide 0.3s ease-in-out;
        }

        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #alert-success {
        animation: slideDownFadeOut 4s ease forwards;
        max-width: 90%;
    }

    @keyframes slideDownFadeOut {
        0% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        10% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
        90% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
    }

    #alert-error {
        animation: slideDownFadeOut 6s ease forwards;
    }

    @keyframes slideDownFadeOut {
        0% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
        10% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
        85% {
            transform: translate(-50%, 0);
            opacity: 1;
        }
        100% {
            transform: translate(-50%, -100%);
            opacity: 0;
        }
    }
    #info-observacao:hover {
        color: #0a58ca;
    }
    </style>
</head>
<body>

<!-- COMPONENT NAVBAR -->
@include('components.navbar')

@if ($errors->any())
    <div id="alert-error" class="alert alert-danger shadow text-center position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; max-width: 90%;">
        <strong>Ops!</strong> Corrija os itens abaixo:
        <ul class="mb-0 mt-1 list-unstyled">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle me-1"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div id="alert-success" class="alert alert-success text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;">
        {{ session('success') }}
    </div>
@endif

<div id="content-wrapper" class="bg-light">
    <div class="bg-white p-4 rounded shadow-sm w-80 w-md-75 w-lg-50">

        <!-- TÍTULO -->
        <div class="text-center mb-3">
            <h2 class="fs-4 mb-0">Agendamento</h2>
        </div>

        <!-- FORM DE BUSCA DE PACIENTE -->
        <form id="search-form" class="d-flex mb-3" role="search">
            <div class="input-group">
                <input id="search-input" name="search" type="search" class="form-control" placeholder="Pesquisar paciente">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
            </div>
        </form>

        <!-- LISTA DE PACIENTES ENCONTRADOS PARA AGENDAMENTO -->
        <div id="pacientes-list" class="mb-3"></div>

        <!-- PACIENTE SELECIONADO -->
        <div id="paciente-selecionado" class="mb-3"></div>

        <!-- FORM DE AGENDAMENTO -->
        <form action="{{ route('criarAgendamento-Psicologia') }}" method="POST" id="agendamento-form" class="w-100">
            @csrf

            <input type="hidden" name="paciente_id" id="paciente_id" />
            <input type="hidden" name="id_servico" id="id_servico" />
            <input type="hidden" name="recorrencia" id="recorrencia" />
            <input type="hidden" name="status_agend" value="Em aberto" />

            <!-- SUBTÍTULO -->
            <div class="mb-2">
                <h5 class="mb-0">Horário</h5>
                <hr class="mt-1">
            </div>

            <div class="row g-2">

                <!-- CHECKBOX TEM RECORRÊNCIA -->
                <div class="col-12 mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" value="1" id="temRecorrencia" name="tem_recorrencia">
                        <label class="form-check-label fw-semibold" for="temRecorrencia">
                            <i class="fas fa-redo-alt me-1 text-primary"></i> Ativar recorrência
                            <span id="recorrenciaBadge" class="badge bg-success ms-2 d-none">Ativa</span>
                        </label>
                    </div>
                </div>

                <!-- SERVIÇO -->
                <div class="col-sm-6 col-md-3 position-relative" style="position: relative;">
                    <label for="servico" class="form-label">
                        Serviço
                        <span id="info-observacao">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    </label>
                    <input type="text" id="servico" name="servico" class="form-control" autocomplete="off" value="">
                    
                    <div id="servicos-list" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                </div>

                <!-- DIA -->
                <div class="col-sm-6 col-md-3">
                    <label for="data" class="form-label">Dia</label>
                    <input type="date" id="data" name="dia_agend" class="form-control" value="{{ old('dia_agend') }}">
                </div>

                <!-- HORÁRIO INÍCIO -->
                <div class="col-sm-6 col-md-3">
                    <label for="hr_ini" class="form-label">Horário Início</label>
                    <input type="time" id="hr_ini" name="hr_ini" class="form-control" value="{{ old('hr_ini') }}">
                </div>

                <!-- HORÁRIO FIM -->
                <div class="col-sm-6 col-md-3">
                    <label for="hr_fim" class="form-label">Horário Fim</label>
                    <input type="time" id="hr_fim" name="hr_fim" class="form-control" value="{{ old('hr_fim') }}">
                </div>

                <!-- CAMPOS DE RECORRÊNCIA -->
                <div id="recorrenciaCampos" class="col-12 mt-2 d-none">
                    <div class="card border-primary">
                        <div class="card-body">

                            <!-- TÍTULO DA CONFIGURAÇÃO DE RECORRÊNCIA -->
                            <h6 class="card-title text-primary mb-3">
                                <i class="fas fa-calendar-alt me-1"></i> Configuração de Recorrência
                            </h6>


                            <div class="row g-2">
                                <!-- DIAS DA SEMANA (NOVA SELEÇÃO) -->
                                <div class="col-md-8">
                                    <label class="form-label">Dias da Semana</label>
                                    <div id="diasSemanaBtns" class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="0">Dom</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="1">Seg</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="2">Ter</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="3">Qua</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="4">Qui</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="5">Sex</button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-dia="6">Sáb</button>
                                    </div>
                                    <small class="text-muted">Clique nos dias desejados para selecionar.</small>
                                </div>

                                
                                <!-- DATA FIM DA RECORRÊNCIA -->
                                <div class="col-md-4">
                                    <label for="data_fim_recorrencia" class="form-label">Data Fim</label>
                                    <input type="date" id="data_fim_recorrencia" name="data_fim_recorrencia" class="form-control">
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                    <!-- VALOR -->
                    <div class="col-sm-6 col-md-3 mt-2">
                        <label for="valor_agend" class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" name="valor_agend" id="valor_agend" class="form-control" placeholder="0,00" value="{{ old('valor_agend') }}">
                        </div>
                    </div>

                <!-- OBSERVAÇÕES -->
                <div class="col-12 mt-2">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Observações..." rows="3">{{ old('observacoes') }}</textarea>
                </div>

                <!-- BOTÃO SUBMIT -->
                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Agendar
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Flatpckr pt-br -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<!-- OBJETOS DA APLICAÇÃO -->
<script>
    // FORMULÁRIO DE BUSCA DE PACIENTES
    const searchForm = document.getElementById('search-form');
    // INPUT DO NOME/CPF DO PACIENTE
    const searchInput = document.getElementById('search-input');
    // LISTA DE PACIENTES ENCONTRADOS PARA AGENDAMENTO
    const pacientesList = document.getElementById('pacientes-list');
    // PACIENTE SELECIONADO APÓS BUSCA
    const pacienteSelecionadoDiv = document.getElementById('paciente-selecionado');
    // ID PACIENTE SELECIONADO
    const pacienteIdInput = document.getElementById('paciente_id');
</script>

<!-- BUSCA DE PACIENTES -->
<script>
    // PESQUISA PACIENTE - FUNCIONALIDADES
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault(); // EVITA RECARREGAMENTO DA PÁGINA
        // RESGATA O NOME DO PACIENTE | REMOVE OS ESPAÇOS EM BRANCO COM A FUNÇÃO TRIM()
        const nome = searchInput.value.trim();
        fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {

                    // AO BUSCAR, OS VALORES ABAIXO SÃO ZERADOS
                    pacientesList.innerHTML = '';
                    pacienteSelecionadoDiv.innerHTML = '';
                    pacienteIdInput.value = '';

                    // CASO NENHUM PACIENTE SEJA ENCONTRADO - MOSTRA MENSAGEM
                    if (pacientes.length === 0) {
                        pacientesList.innerHTML = `<div class="alert alert-warning">Nenhum paciente encontrado.</div>`;
                        return;
                    }

                    // CRIA ELEMENTO HTML
                    const listGroup = document.createElement('div');
                    listGroup.classList.add('list-group');

                    // SE QUANTIDADE DE PACIENTES É MAIOR QUE 5
                    if(pacientes.length > 5) {
                        listGroup.style.maxHeight = '250px';
                        listGroup.style.overflowY = 'auto';
                    }

                    pacientes.forEach(paciente => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.textContent = `${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE}) - ${new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR')}`;
                        item.addEventListener('click', () => {
                            pacienteSelecionadoDiv.innerHTML = 
                            `<div class="alert alert-success d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Paciente selecionado:</strong> ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE})
                                </div>
                                <button type="button" id="cancelar-paciente" class="btn btn-sm btn-outline-danger ms-2">Cancelar</button>
                            </div>
                            `;
                            
                            pacienteIdInput.value = paciente.ID_PACIENTE;
                            pacientesList.innerHTML = '';

                            // LISTENER DO BOTÃO DE CANCELAR
                            document.getElementById('cancelar-paciente').addEventListener('click', () => {
                                pacienteSelecionadoDiv.innerHTML = '';
                                pacienteIdInput.value = '';
                            })
                        });
                        listGroup.appendChild(item);
                    });

                    pacientesList.appendChild(listGroup);
                })
                .catch(error => {
                    pacientesList.innerHTML = `<div class="alert alert-danger">Erro ao buscar pacientes.</div>`;
                    console.error(error);
                });
        });
</script>

<!-- BUSCA DE SERVIÇOS -->
<script>
    // INPUT DO SERVIÇO A SER SELECIONADO
    const servicoInput = document.getElementById("servico");

    // LIST DE SERVIÇÕS APÓS PESQUISA
    const servicosList = document.getElementById("servicos-list");

    // SETA O TIMER PARA NULO
    let timeout = null;

    servicoInput.addEventListener('input', () => {

        clearTimeout(timeout); // Cancela o temporizador anterior, caso exista.

        timeout = setTimeout(() => {

            // RESGATA O VALOR DE BUSCA E TIRA ESPAÇOS EM BRANCO
            const query = servicoInput.value.trim();

            const infoObs = document.getElementById('info-observacao');

            // Se campo está vazio, limpa lista, id_servico e esconde ícone info
            if (!query) {
                servicosList.innerHTML = '';
                document.getElementById('id_servico').value = '';
                infoObs.style.display = 'none';
                infoObs.title = '';
                return;
            }

            // CODIFICA O VALOR DA QUERY POR QUESTÕES DE SEGURANÇA
            fetch(`/psicologia/pesquisar-servico?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(servicos => {
                    servicosList.innerHTML = '';

                    if (servicos.length === 0) {
                        servicosList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhum serviço encontrado</button>`;
                        document.getElementById('id_servico').value = ''; // limpa id_servico
                        infoObs.style.display = 'none';
                        infoObs.title = '';
                        return;
                    }

                    // Tenta encontrar serviço com nome exatamente igual ao digitado (case insensitive)
                    const servicoExato = servicos.find(s => s.SERVICO_CLINICA_DESC.toLowerCase() === query.toLowerCase());

                    if (servicoExato) {
                        // Preenche automaticamente se achou serviço exato
                        aoSelecionarServico(servicoExato);
                        servicosList.innerHTML = ''; // fecha lista porque já selecionou
                        return;
                    } else {
                        // Não achou exato, limpa ícone info
                        infoObs.style.display = 'none';
                        infoObs.title = '';
                    }

                    // Caso não tenha encontrado exato, limpa id_servico para forçar seleção
                    document.getElementById('id_servico').value = '';

                    // MOSTRA OPÇÕES NA LISTA PARA SELEÇÃO MANUAL
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
    });
    // FECHA A LISTA AO CLICAR FORA
    // FUNÇÃO ACIONADA QUANDO O USUÁRIO CLICA EM QUALQUER PARTE DA PÁGINA(document)
    document.addEventListener('click', (e) => {

        //  VERIFICA SE O CLIQUE FOI FORA DOS DOIS ELEMENTOS ESPECÍFICOS
        if (!servicoInput.contains(e.target) && !servicosList.contains(e.target)) {
            servicosList.innerHTML = '';
        }
    });
</script>

<!-- CAMPOS DE RECORRÊNCIA -->
<script>
    // EXIBE OU OCULTA CAMPOS DE RECORRÊNCIA AO MARCAR O CHECKBOX
    const temRecorrenciaCheckbox = document.getElementById('temRecorrencia');
    const recorrenciaCampos = document.getElementById('recorrenciaCampos');
    temRecorrenciaCheckbox.addEventListener('change', function() {
        if (this.checked) {
            recorrenciaCampos.classList.add('show');
        } else {
            recorrenciaCampos.classList.remove('show');
            document.getElementById('dias_semana').selectedIndex = -1;
            document.getElementById('data_fim_recorrencia').value = '';
        }
    });


    document.addEventListener('DOMContentLoaded', function() {
        // GERA HASH AO MARCAR RECORRÊNCIA
        const temRecorrenciaCheckbox = document.getElementById('temRecorrencia');
        const recorrenciaInput = document.getElementById('recorrencia');

        temRecorrenciaCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recorrenciaInput.value = crypto.randomUUID();
            } else {
                recorrenciaInput.value = '';
            }
        });
    });

    // SELEÇÃO DE DIAS DA SEMANA
    document.addEventListener('DOMContentLoaded', function() {
        const diasSemanaBtns = document.querySelectorAll('#diasSemanaBtns button');
        const diasSemanaContainer = document.getElementById('diasSemanaContainer'); // Container para os inputs hidden

        // Se não existir, vamos criar esse container escondido para os inputs hidden
        if (!diasSemanaContainer) {
            const container = document.createElement('div');
            container.id = 'diasSemanaContainer';
            container.style.display = 'none';
            document.getElementById('agendamento-form').appendChild(container);
        }

        const container = document.getElementById('diasSemanaContainer');

        diasSemanaBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                this.classList.toggle('active');
                this.classList.toggle('btn-primary');
                this.classList.toggle('btn-outline-primary');

                // Remove todos os inputs antigos
                container.innerHTML = '';

                // Pega os dias selecionados
                const diasSelecionados = Array.from(diasSemanaBtns)
                    .filter(b => b.classList.contains('active'))
                    .map(b => b.getAttribute('data-dia'));

                // Para cada dia selecionado, cria um input hidden com name dias_semana[]
                diasSelecionados.forEach(dia => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'dias_semana[]';
                    input.value = dia;
                    container.appendChild(input);
                });
            });
        });

        // Limpa seleção e inputs ao desativar recorrência
        document.getElementById('temRecorrencia').addEventListener('change', function() {
            if (!this.checked) {
                diasSemanaBtns.forEach(btn => {
                    btn.classList.remove('active', 'btn-primary');
                    btn.classList.add('btn-outline-primary');
                });
                container.innerHTML = '';
            }
        });
    });
</script>

<!-- CONTROLE DE INSERÇÃO DE INFORMAÇÃO -->
<!-- <script>
    // FUNÇÃO DE MENSAGENS DE ERRO CASO USUÁRIO NÃO INFORME ALGUM DOS CAMPOS OBRIGATÓRIOS
    function showError(input, message) {
        // Verifica se já existe mensagem, remove para não duplicar
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        // Cria o elemento de mensagem
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message text-danger small mb-1';
        errorDiv.textContent = message;
        // Insere antes do input dentro do mesmo container
        input.parentNode.insertBefore(errorDiv, input);
    }

    // Remove todas as mensagens de erro antes da validação
    function clearErrors(form) {
        const errors = form.querySelectorAll('.error-message');
        errors.forEach(err => err.remove());
    }

    document.getElementById('agendamento-form').addEventListener('submit', function(e) {
        clearErrors(this); // limpa mensagens anteriores

        let isValid = true;

        // Validar paciente selecionado (paciente_id hidden)
        const pacienteId = document.getElementById('paciente_id').value.trim();
        if (!pacienteId) {
            showError(document.getElementById('search-input'), 'Selecione um paciente antes de continuar.');
            isValid = false;
        }

        // Validar serviço selecionado (id_servico hidden)
        const idServico = document.getElementById('id_servico').value.trim();
        if (!idServico) {
            showError(document.getElementById('servico'), 'Selecione um serviço válido antes de continuar.');
            isValid = false;
        }

        // Validar data
        const data = document.getElementById('data').value.trim();
        if (!data) {
            showError(document.getElementById('data'), 'Selecione uma data.');
            isValid = false;
        }

        // Validar horário início
        const hrIni = document.getElementById('hr_ini').value.trim();
        if (!hrIni) {
            showError(document.getElementById('hr_ini'), 'Informe o horário de início.');
            isValid = false;
        }

        // Validar horário fim
        const hrFim = document.getElementById('hr_fim').value.trim();
        if (!hrFim) {
            showError(document.getElementById('hr_fim'), 'Informe o horário de término.');
            isValid = false;
        }

        // Validação simples para horário fim ser maior que início
        if (hrIni && hrFim && hrFim <= hrIni) {
            showError(document.getElementById('hr_fim'), 'O horário fim deve ser maior que o início.');
            isValid = false;
        }

        // Validação da recorrência: se checkbox está marcado, data fim é obrigatória
        const temRecorrenciaChecked = document.getElementById('temRecorrencia').checked;
        const dataFimRecorrencia = document.getElementById('data_fim_recorrencia').value.trim();

        if (temRecorrenciaChecked && !dataFimRecorrencia) {
            showError(document.getElementById('data_fim_recorrencia'), 'Informe a data final da recorrência.');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault(); // impede o envio do form
            const firstError = this.querySelector('.error-message');
            if (firstError) {
                const inputErro = firstError.nextElementSibling;
                if (inputErro) inputErro.focus();
            }
        }
    });

    // Validar valor (opcional ou > 0)
    const valorAgend = valorAgendInput.value.trim();
    if (valorAgend) {
        const valorNumerico = parseFloat(valorAgend.replace('.', '').replace(',', '.'));
        if (isNaN(valorNumerico) || valorNumerico <= 0) {
            showError(valorAgendInput, 'Informe um valor válido maior que zero.');
            isValid = false;
        }
    }
</script> -->

<!-- FLATPICKR PARA MELHORAR VISUALIZAÇÃO DE DIAS E HORÁRIOS -->
<script>
    // Inicializa o flatpickr para o campo de data
    flatpickr("#data", {
        dateFormat: "d-m-Y", // <-- altera para D-M-Y visualmente
        altInput: true,      // mostra formatado mas envia no formato do banco
        altFormat: "d-m-Y",  // formato visível ao usuário
        locale: "pt",
        minDate: "today"
    });

    // Inicializa o flatpickr para os campos de hora (início e fim)
    flatpickr("#hr_ini", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15,
    });

    // FLATPICKR CAMPO DE HORA FINAL
    flatpickr("#hr_fim", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15,
    });

    flatpickr("#data_fim_recorrencia", {
        dateFormat: "Y-m-d",
        locale: "pt",
        minDate: "today"
    });

    flatpickr.localize(flatpickr.l10ns.pt);
</script>

<!-- PERMISSÃO DE ESCRITA EM INPUT DE VALOR -->
<script>
    const valorAgendInput = document.getElementById('valor_agend');

    valorAgendInput.addEventListener('input', function () {
        // Remove tudo que não for dígito ou vírgula
        this.value = this.value.replace(/[^\d,]/g, '');

        // Permitir apenas uma vírgula
        const parts = this.value.split(',');
        if (parts.length > 2) {
            this.value = parts[0] + ',' + parts[1];
        }
    });
</script>

<!-- SCRIPT REMOÇÃO DE ANIMAÇÃO DE AGENDAMENTO CRIADO COM SUCESSO OU MENSAGEM DE ERRO -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alert = document.getElementById('alert-success');
        if (alert) {
            setTimeout(() => {
                alert.remove();
            }, 4000); // após 4s remove
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alertError = document.getElementById('alert-error');
        if (alertError) {
            setTimeout(() => {
                alertError.remove();
            }, 6000);
        }
    });
</script>

<!-- MOSTRA OBSERVACAO DE SERVICO AO PASSAR MOUSE NO INFO -->
<script>
    function aoSelecionarServico(servico) {
        const inputServico = document.getElementById('servico');
        const infoObs = document.getElementById('info-observacao');
        const inputValor = document.getElementById('valor_agend');

        inputServico.value = servico.SERVICO_CLINICA_DESC || '';
        document.getElementById('id_servico').value = servico.ID_SERVICO_CLINICA || '';

        if (servico.OBSERVACAO && servico.OBSERVACAO.trim() !== '') {
            infoObs.style.display = 'inline';
            infoObs.title = servico.OBSERVACAO;
        } else {
            infoObs.style.display = 'none';
            infoObs.title = '';
        }

        // Preenche o valor, formatando para padrão brasileiro
        if (servico.VALOR_SERVICO) {
            // Se já vem como número:
            let valor = parseFloat(servico.VALOR_SERVICO).toFixed(2).replace('.', ',');
            inputValor.value = valor;

            // Caso venha como string e precise tratar:
            // let valorNum = parseFloat(servico.VALOR_SERVICO.replace(',', '.'));
            // inputValor.value = valorNum.toFixed(2).replace('.', ',');
        } else {
            inputValor.value = '';
        }
    }
</script>

</body>

</html>