<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento</title>

    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <style>
        /* Estilos mantidos do arquivo original e adaptados */
        #servicos-list button {
            cursor: pointer;
        }

        #recorrenciaCampos.show {
            display: flex !important;
            animation: fadeInSlide 0.3s ease-in-out;
        }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #alert-success, #alert-error {
            animation: slideDownFadeOut 5s ease forwards;
            max-width: 90%;
        }

        @keyframes slideDownFadeOut {
            0% { transform: translate(-50%, -100%); opacity: 0; }
            10% { transform: translate(-50%, 0); opacity: 1; }
            90% { transform: translate(-50%, 0); opacity: 1; }
            100% { transform: translate(-50%, -100%); opacity: 0; }
        }

        #info-observacao:hover {
            color: #0a58ca;
        }

        /* Estilos para limitar a altura das listas de busca */
        .list-local-option, .list-psicologo-option, #pacientes-list .list-group {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .shadow-dark {
            box-shadow: 0 0.75rem 1.25rem rgba(0,0,0,0.4) !important;
        }
    </style>
</head>
<body class="bg-body-secondary">

@include('components.navbar')

@if ($errors->any())
    <div id="alert-error" class="alert alert-danger shadow text-center position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; max-width: 90%;">
        <strong>Ops!</strong> Corrija os itens abaixo:
        <ul class="mb-0 mt-1 list-unstyled">
            @foreach ($errors->all() as $error)
                <li><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div id="alert-success" class="alert alert-success text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div id="alert-error" class="alert alert-danger text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; max-width: 90%;">
        {{ session('error') }}
    </div>
@endif

<div class="container ms-3 mw-100">
    <div class="row">
        <x-page-title>
        </x-page-title>
        
        <div class="col-12 shadow-lg shadow-dark p-4 bg-body-tertiary rounded">

            <form action="{{ route('criarAgendamento-Psicologia') }}" method="POST" id="agendamento-form" class="w-100" validate>
                @csrf

                <input type="hidden" name="paciente_id" id="paciente_id" value="{{ old('paciente_id') }}"/>
                <input type="hidden" name="id_servico" id="id_servico" value="{{ old('id_servico') }}" />
                <input type="hidden" name="recorrencia" id="recorrencia"/>
                <input type="hidden" name="status_agend" value="Em aberto"/>

                <div class="mb-3 position-relative">
                    <label for="search-input" class="form-label">Paciente</label>
                    <input id="search-input" name="search" class="form-control" placeholder="Pesquisar paciente por nome ou CPF" value="{{ old('search') }}">
                    <div id="pacientes-list" class="list-group position-absolute w-100" style="z-index: 1000; top: 100%"></div>
                </div>

                <div class="mb-2">
                    <h5 class="mb-0">Horário e Detalhes</h5>
                    <hr class="mt-1">
                </div>

                <div class="row g-3">

                    <div class="col-12 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" value="1" id="temRecorrencia" name="tem_recorrencia">
                            <label class="form-check-label fw-semibold" for="temRecorrencia">
                                <i class="bi bi-arrow-repeat me-1 text-primary"></i> Ativar recorrência
                            </label>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3 position-relative">
                        <label for="servico" class="form-label">
                            Serviço
                            <span id="info-observacao" style="display: none;">
                                <i class="bi bi-info-circle-fill"></i>
                            </span>
                        </label>
                        <input type="text" id="servico" name="servico" class="form-control" autocomplete="off" value="{{ old('servico') }}" placeholder="Serviço do Atendimento">
                        <div id="servicos-list" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="data" class="form-label">Dia</label>
                        <input type="text" id="data" name="dia_agend" class="form-control" value="{{ old('dia_agend') }}" placeholder="Selecione o dia">
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="hr_ini" class="form-label">Horário Início</label>
                        <input type="text" id="hr_ini" name="hr_ini" class="form-control" value="{{ old('hr_ini') }}">
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="hr_fim" class="form-label">Horário Fim</label>
                        <input type="text" id="hr_fim" name="hr_fim" class="form-control" value="{{ old('hr_fim') }}">
                    </div>

                    <div id="recorrenciaCampos" class="col-12 mt-2 d-none">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary mb-3">
                                    <i class="bi bi-calendar-week me-1"></i> Configuração de Recorrência
                                </h6>
                                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-around">
                                    <div class="flex-grow-1">
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
                                    <div id="duracaoMesesContainer" style="min-width: 200px;">
                                        <label for="duracao_meses_recorrencia" class="form-label">Duração (meses)</label>
                                        <select id="duracao_meses_recorrencia" name="duracao_meses_recorrencia" class="form-select form-select-sm">
                                            <option value="" selected>Selecione</option>
                                            @for ($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}">{{ $i }} mes{{ $i > 1 ? 'es' : '' }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div style="min-width: 200px;">
                                        <label for="data_fim_recorrencia" class="form-label">Data Fim</label>
                                        <input type="text" id="data_fim_recorrencia" name="data_fim_recorrencia" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="msg-recorrencia" class="alert alert-info mt-2 d-none">
                        Caso não selecione "dias da semana" e uma "duração" ou "data fim", serão gerados agendamentos semanais por 1 mês, no mesmo dia da semana do campo "Dia".
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="valor_agend" class="form-label">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" name="valor_agend" id="valor_agend" class="form-control" placeholder="0,00" value="{{ old('valor_agend') }}">
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-3 position-relative">
                        <input type="hidden" name="id_sala_clinica" id="id_sala_clinica">
                        <label for="local_agend" class="form-label">Local</label>
                        <input type="text" name="local_agend" id="local_agend" class="form-control" placeholder="Local do atendimento" value="{{ old('local_agend') }}">
                        <div id="local-list" class="list-group position-absolute w-100" style="z-index: 999; top: 100%"></div>
                    </div>

                    <div class="col-md-6 position-relative">
                         <input type="hidden" name="id_psicologo" id="id_psicologo">
                        <label for="psicologo_agend" class="form-label">Psicólogo</label>
                        <input type="text" name="psicologo_agend" id="psicologo_agend" class="form-control" placeholder="Psicólogo do Atendimento" value="{{ old('id_psicologo') }}" autocomplete="off">
                        <div id="psicologo_list" class="list-group position-absolute w-100" style="z-index: 998; top: 100%"></div>
                    </div>
                                        
                    <div class="col-12">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Observações..." rows="3">{{ old('observacoes') }}</textarea>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle-fill me-1"></i> Agendar
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('agendamento-form');
        form.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName.toLowerCase() !== 'textarea') {
                event.preventDefault();
            }
        });
    });
</script>

<script>
    const searchInput = document.getElementById('search-input');
    const pacientesList = document.getElementById('pacientes-list');
    const pacienteIdInput = document.getElementById('paciente_id');
</script>

<script>
    searchInput.addEventListener('input', function(e) {
        const nome = searchInput.value.trim();
        if (nome.length < 2) {
            pacientesList.innerHTML = '';
            return;
        }

        fetch(`/psicologia/consultar-paciente/buscar-nome-cpf?search=${encodeURIComponent(nome)}`)
            .then(response => response.json())
            .then(pacientes => {
                pacientesList.innerHTML = '';
                pacienteIdInput.value = '';

                if (pacientes.length === 0) {
                    pacientesList.innerHTML = `<div class="list-group-item text-muted">Nenhum paciente encontrado. <button type="button" class="btn btn-sm btn-outline-success ms-2 py-0" id="add-paciente">Adicionar Novo</button></div>`;
                    document.getElementById('add-paciente').addEventListener('click', () => {
                        const nome_compl_paciente = document.getElementById('search-input').value;
                        window.location.href = "{{ route('criarpaciente_psicologia') }}" + "?nome_compl_paciente=" + encodeURIComponent(nome_compl_paciente);
                    });
                    return;
                }

                const listGroup = document.createElement('div');
                listGroup.classList.add('list-group');

                pacientes.forEach(paciente => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.classList.add('list-group-item', 'list-group-item-action');
                    item.textContent = `${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE || 'CPF não informado'})`;
                    item.addEventListener('click', () => {
                        searchInput.value = `${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE || 'CPF não informado'})`;
                        pacienteIdInput.value = paciente.ID_PACIENTE;
                        pacientesList.innerHTML = '';
                    });
                    listGroup.appendChild(item);
                });
                pacientesList.appendChild(listGroup);
            })
            .catch(error => {
                pacientesList.innerHTML = `<div class="list-group-item text-danger">Erro ao buscar pacientes.</div>`;
                console.error(error);
            });
    });
</script>

<script>
    const servicoInput = document.getElementById("servico");
    const servicosList = document.getElementById("servicos-list");
    const localInput = document.getElementById("local_agend");
    const localList = document.getElementById("local-list");
    const psicologoInput = document.getElementById('psicologo_agend');
    const psicologoList = document.getElementById('psicologo_list')
    let timeout = null;

    function setupAutocomplete(inputElement, listElement, url, processResults, onSelect) {
        inputElement.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const query = inputElement.value.trim();
                if (!query) {
                    listElement.innerHTML = '';
                    return;
                }
                fetch(`${url}?search=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => processResults(data, query, listElement, onSelect))
                    .catch(error => {
                        console.error('Erro na busca:', error);
                        listElement.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger">Erro na busca</button>`;
                    });
            }, 300);
        });
    }

    function aoSelecionarServico(servico) {
        document.getElementById('servico').value = servico.SERVICO_CLINICA_DESC;
        document.getElementById('id_servico').value = servico.ID_SERVICO_CLINICA;
        const infoObs = document.getElementById('info-observacao');
        const inputValor = document.getElementById('valor_agend');
        if (servico.OBSERVACAO && servico.OBSERVACAO.trim() !== '') {
            infoObs.style.display = 'inline';
            infoObs.title = servico.OBSERVACAO;
        } else {
            infoObs.style.display = 'none';
            infoObs.title = '';
        }
        if (servico.VALOR_SERVICO) {
            let valor = parseFloat(servico.VALOR_SERVICO).toFixed(2).replace('.', ',');
            inputValor.value = valor;
        } else {
            inputValor.value = '';
        }
    }

    function aoSelecionarLocal(local) {
        document.getElementById('local_agend').value = local.DESCRICAO;
        document.getElementById('id_sala_clinica').value = local.ID_SALA_CLINICA;
    }

    function aoSelecionarPsicologo(psicologo) {
        document.getElementById('psicologo_agend').value = psicologo.NOME_COMPL;
        document.getElementById('id_psicologo').value = psicologo.ALUNO;
    }

    // Processadores de resultados
    const processServicos = (servicos, query, listEl, onSelect) => {
        listEl.innerHTML = '';
        if (servicos.length === 0) {
            listEl.innerHTML = `<div class="list-group-item text-muted">Nenhum serviço encontrado. <a href="{{ route('criarservico_psicologia') }}?nome_servico=${encodeURIComponent(query)}" class="btn btn-sm btn-outline-success py-0 ms-2">Adicionar</a></div>`;
            return;
        }
        servicos.forEach(servico => {
            const item = document.createElement('button');
            item.type = 'button';
            item.classList.add('list-group-item', 'list-group-item-action');
            item.textContent = servico.SERVICO_CLINICA_DESC;
            item.addEventListener('click', () => {
                onSelect(servico);
                listEl.innerHTML = '';
            });
            listEl.appendChild(item);
        });
    };

    const processLocais = (locais, query, listEl, onSelect) => {
        listEl.innerHTML = '';
        if (locais.length === 0) {
            listEl.innerHTML = `<div class="list-group-item text-muted">Nenhum local encontrado. <a href="{{ route('salas_psicologia') }}?nome_local=${encodeURIComponent(query)}" class="btn btn-sm btn-outline-success py-0 ms-2">Adicionar</a></div>`;
            return;
        }
        locais.forEach(local => {
            const item = document.createElement('button');
            item.type = 'button';
            item.classList.add('list-group-item', 'list-group-item-action', 'list-local-option');
            item.textContent = local.DESCRICAO;
            item.addEventListener('click', () => {
                onSelect(local);
                listEl.innerHTML = '';
            });
            listEl.appendChild(item);
        });
    };

    const processPsicologos = (psicologos, query, listEl, onSelect) => {
        listEl.innerHTML = '';
        if (psicologos.length === 0) {
            listEl.innerHTML = `<div class="list-group-item text-muted">Nenhum psicólogo encontrado.</div>`;
            return;
        }
        psicologos.forEach(psicologo => {
            const item = document.createElement('button');
            item.type = 'button';
            item.classList.add('list-group-item', 'list-group-item-action', 'list-psicologo-option');
            item.textContent = `${psicologo.NOME_COMPL} - ${psicologo.ALUNO}`;
            item.addEventListener('click', () => {
                onSelect(psicologo);
                listEl.innerHTML = '';
            });
            listEl.appendChild(item);
        });
    };

    // Inicialização
    setupAutocomplete(servicoInput, servicosList, '/psicologia/pesquisar-servico', processServicos, aoSelecionarServico);
    setupAutocomplete(localInput, localList, '/psicologia/pesquisar-local', processLocais, aoSelecionarLocal);
    setupAutocomplete(psicologoInput, psicologoList, '/psicologia/listar-psicologos', processPsicologos, aoSelecionarPsicologo);

    // Fecha as listas ao clicar fora
    document.addEventListener('click', (e) => {
        if (!servicoInput.contains(e.target)) servicosList.innerHTML = '';
        if (!localInput.contains(e.target)) localList.innerHTML = '';
        if (!psicologoInput.contains(e.target)) psicologoList.innerHTML = '';
        if (!searchInput.contains(e.target)) pacientesList.innerHTML = '';
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const temRecorrenciaCheckbox = document.getElementById('temRecorrencia');
    const recorrenciaCampos = document.getElementById('recorrenciaCampos');
    const msgRecorrencia = document.getElementById('msg-recorrencia');
    const recorrenciaInput = document.getElementById('recorrencia');
    const diasSemanaBtns = document.querySelectorAll('#diasSemanaBtns button');
    let container = document.getElementById('diasSemanaContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'diasSemanaContainer';
        container.style.display = 'none';
        document.getElementById('agendamento-form').appendChild(container);
    }
    
    function atualizarDiasSelecionados() {
        container.innerHTML = '';
        const diasSelecionados = Array.from(diasSemanaBtns)
            .filter(btn => btn.classList.contains('active'))
            .map(btn => btn.getAttribute('data-dia'));
        diasSelecionados.forEach(dia => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dias_semana[]';
            input.value = dia;
            container.appendChild(input);
        });
    }

    diasSemanaBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('active');
            this.classList.toggle('btn-primary');
            this.classList.toggle('btn-outline-primary');
            atualizarDiasSelecionados();
        });
    });

    temRecorrenciaCheckbox.addEventListener('change', function() {
        if (this.checked) {
            recorrenciaCampos.classList.remove('d-none');
            msgRecorrencia.classList.remove('d-none');
            recorrenciaInput.value = crypto.randomUUID ? crypto.randomUUID() : 'uuid-fallback-' + Date.now();
        } else {
            recorrenciaCampos.classList.add('d-none');
            msgRecorrencia.classList.add('d-none');
            recorrenciaInput.value = '';
            diasSemanaBtns.forEach(btn => {
                btn.classList.remove('active', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            container.innerHTML = '';
            document.getElementById('data_fim_recorrencia').value = '';
            document.getElementById('duracao_meses_recorrencia').value = '';
        }
    });

    const selectDuracao = document.getElementById('duracao_meses_recorrencia');
    const inputDataFim = document.getElementById('data_fim_recorrencia');

    function atualizarCamposRecorrencia() {
        inputDataFim.disabled = selectDuracao.value !== '';
        selectDuracao.disabled = inputDataFim.value !== '';
    }

    selectDuracao.addEventListener('change', atualizarCamposRecorrencia);
    inputDataFim.addEventListener('input', atualizarCamposRecorrencia); // use input for flatpickr
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr.localize(flatpickr.l10ns.pt);
        const commonDateConfig = {
            dateFormat: "Y-m-d", 
            altInput: true,
            altFormat: "d/m/Y",
            locale: "pt",
            allowInput: true,
        };
        const commonTimeConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            allowInput: true,
        };
        flatpickr("#data", {...commonDateConfig, minDate: "today"});
        flatpickr("#hr_ini", commonTimeConfig);
        flatpickr("#hr_fim", commonTimeConfig);
        flatpickr("#data_fim_recorrencia", {...commonDateConfig, minDate: "today"});
    });
</script>

<script>
    document.getElementById('valor_agend').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        e.target.value = value;
    });

    document.addEventListener('DOMContentLoaded', function() {
        const alertSuccess = document.getElementById('alert-success');
        if (alertSuccess) setTimeout(() => alertSuccess.remove(), 4000);
        
        const alertError = document.getElementById('alert-error');
        if (alertError) setTimeout(() => alertError.remove(), 6000);
    });
</script>

</body>
</html>