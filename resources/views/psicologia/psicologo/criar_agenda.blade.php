<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        html, body { height: 100%; margin: 0; }
        #content-wrapper {
            width: 85vw;
            height: 100vh;
            margin: auto;
            display: column;
            gap: 24px;
            overflow-y: auto;
            align-items: stretch;
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
    main {
        background-color: #ffffff;
        padding: 18px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        flex-direction: column;
        overflow-y: auto;
        border: 1.8px solid #dee2e6;
    }

    .list-local-option {
        max-width: 350px;
        max-height: 50px;
        overflow-y: auto;
    }

    .list-psicologo-option {
        max-width: 700px;
        max-height: 50px;
        overflow-y: auto;
    }
    </style>
</head>
<body>

<!-- COMPONENT NAVBAR -->
@include('components.psicologo_navbar')

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

@if(session('error'))
    <div id="alert-error" class="alert alert-danger text-center shadow position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050; max-width: 90%;">
        {{ session('error') }}
    </div>
@endif

<div id="content-wrapper" class="bg-light">
    <main>
        <div class="bg-white p-4 rounded shadow-sm w-80 w-md-75 w-lg-50">

            <!-- TÍTULO -->
            <div class="text-center mb-5">
                <h2 class="fs-4 mb-0">Criação de Agendamento</h2>
            </div>

            <!-- FORM DE AGENDAMENTO -->
            <form action="{{ route('criarAgendamento-Psicologo') }}" method="POST" id="agendamento-form" class="w-100" validate>
                @csrf

                <!-- VALORES PASSADOS NO FORMATO HIDDEN | USUÁRIO NÃO SELECIONA DIRETAMENTE -->
                
                <input type="hidden" name="paciente_id" id="paciente_id" value="{{ old('paciente_id') }}"/>

                <input type="hidden" name="id_servico" id="id_servico" value="{{ old('id_servico') }}" />

                <input type="hidden" name="recorrencia" id="recorrencia"/>

                <input type="hidden" name="status_agend" value="Em aberto"/>

                <input type="hidden" name="id_psicologo" id="id_psicologo" value="{{ session('psicologo')[1] }}"/>

                {{-- CAMPO DE PESQUISA POR PACIENTES --}}
                <div class="mb-3 position-relative">
                    <input id="search-input" name="search" class="form-control" placeholder="Pesquisar paciente (CPF)" value="{{ old('search') }}">

                    <div id="pacientes-list" class="list-group position-absolute w-100" style="z-index: 1000; top: 100%"></div>
                </div>

                <!-- SUBTÍTULO -->
                <div class="mb-2">
                    <h5 class="mb-0">Horário</h5>
                    <hr class="mt-1">
                </div>

                <div class="row g-2">

                    <!-- DISCIPLINAS -->
                    <div class="col-sm-6 col-md-3 position-relative" style="position: relative;">
                        <label for="disciplina" class="form-label">
                            Disciplina
                        </label>
                        <input type="text" id="disciplina" name="disciplina" class="form-control" autocomplete="off" value="{{ old('disciplina') }}" placeholder="Disciplina do Atendimento">
                        
                        <div id="disciplinas-list" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                    </div>

                    <!-- DIA -->
                    <div class="col-sm-6 col-md-3">
                        <label for="data" class="form-label">Dia</label>
                        <input type="date" id="data" name="dia_agend" class="form-control" value="{{ old('dia_agend') }}" placeholder="Dia do Atendimento">
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

                    <!-- Mensagem que aparece quando ativa recorrência -->
                    <div id="msg-recorrencia" class="alert alert-info mt-2 d-none">
                        Caso não selecione dia da semana e/ou data fim, serão gerados agendamentos por 1 mês por padrão.
                    </div>

                    <!-- LOCAL -->
                    <input type="hidden" name="id_sala_clinica" id="id_sala_clinica">
                    <div class="col-sm-6 col-md-3 mt-2 position-relative">
                        <label for="local_agend" class="form-label">Local</label>
                        <input type="text" name="local_agend" id="local_agend" class="form-control" placeholder="Local do atendimento" value="{{ old('local_agend') }}">

                        <div id="local-list" class="list-group position-absolute w-100" style="z-index: 1000; top: 100%"></div>
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
    </main>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Flatpckr pt-br -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

<!-- BUSCA DE PACIENTES -->
<script>
    const searchInput = document.getElementById('search-input');
    const pacientesList = document.getElementById('pacientes-list');
    const pacienteIdInput = document.getElementById('paciente_id');

    // PESQUISA PACIENTE - FUNCIONALIDADES
    searchInput.addEventListener('input', function(e) {
        const nome = searchInput.value.trim();
        fetch(`/psicologo/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {

                    console.log(pacientes);

                    // AO BUSCAR, OS VALORES ABAIXO SÃO ZERADOS
                    pacientesList.innerHTML = '';
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
                        listGroup.style.maxHeight = '200px';
                        listGroup.style.overflowY = 'auto';
                    }

                    pacientes.forEach(paciente => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-group-item-action', 'border');
                        item.textContent = `${paciente.CPF_PACIENTE}`;
                        item.addEventListener('click', () => {
                            searchInput.value = `${paciente.CPF_PACIENTE}`;                            
                            pacienteIdInput.value = paciente.ID_PACIENTE;
                            pacientesList.innerHTML = '';
                        });
                        listGroup.appendChild(item);
                    });

                    pacientesList.appendChild(listGroup);
                })
                .catch(error => {
                    pacientesList.innerHTML = `<div class="alert alert-danger">Erro ao buscar pacientes.</div>`;
                    console.error(error);
                });

            function formatarDataBR(dateStr) {
                if (!dateStr) return '-';
                const cleanedDate = dateStr.split('T')[0];
                const [year, month, day] = cleanedDate.split('-');
                return `${day}/${month}/${year}`;
            }
        });
</script>

<!-- BUSCA DE DISCIPLINAS -->
<script>
    const disciplinaInput = document.getElementById("disciplina");
    const disciplinasList = document.getElementById("disciplinas-list");

    const localInput = document.getElementById("local_agend");
    const localList = document.getElementById("local-list");

    let timeout = null;

    // =========================
    // BUSCA DE DISCIPLINA
    // =========================
    disciplinaInput.addEventListener('input', () => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            const query = disciplinaInput.value.trim();

            if (!query) {
                disciplinasList.innerHTML = '';
                document.getElementById('id_servico').value = '';
                return;
            }

            fetch(`/psicologo/pesquisar-disciplina?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(disciplinas => {

                    console.log(disciplinas);

                    disciplinasList.innerHTML = '';

                    if (disciplinas.length === 0) {
                        disciplinasList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhuma disciplina encontrado</button>`;
                        document.getElementById('id_servico').value = '';
                        return;
                    }

                    const disciplinaExata = disciplinas.find(s => s.DISCIPLINA.toLowerCase() === query.toLowerCase());

                    if (disciplinaExata) {
                        aoSelecionarDisciplina(disciplinaExata);
                        disciplinasList.innerHTML = '';
                        return;
                    }

                    document.getElementById('id_servico').value = '';

                    disciplinas.forEach(disciplina => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.textContent = disciplina.DISCIPLINA + '-' + disciplina.SERVICO_CLINICA_DESC;
                        item.addEventListener('click', () => {
                            aoSelecionarDisciplina(disciplina);
                            disciplinasList.innerHTML = '';
                        });
                        disciplinasList.appendChild(item);
                    });
                })
                .catch(error => {
                    console.error(error);
                    disciplinasList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger">Erro ao buscar Disciplinas</button>`;
                });
        }, 300);
    });

    // Fecha a lista de serviços ao clicar fora
    document.addEventListener('click', (e) => {
        if (!disciplinaInput.contains(e.target) && !disciplinasList.contains(e.target)) {
            disciplinasList.innerHTML = '';
        }
    });

    // =========================
    // BUSCA DE LOCAL
    // =========================
    localInput.addEventListener('input', () => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            const query = localInput.value.trim();

            if (!query) {
                localList.innerHTML = '';
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
        }, 300);
    });

    // Fecha a lista de locais ao clicar fora
    document.addEventListener('click', (e) => {
        if (!localInput.contains(e.target) && !localList.contains(e.target)) {
            localList.innerHTML = '';
        }
    });

    // =========================
    // FUNÇÕES DE SELEÇÃO
    // =========================
    function aoSelecionarDisciplina(disciplina) {
        document.getElementById('disciplina').value = disciplina.DISCIPLINA;
        document.getElementById('id_servico').value = disciplina.ID_SERVICO_CLINICA;
    }

    function aoSelecionarLocal(local) {
        document.getElementById('local_agend').value = local.DESCRICAO;
        document.getElementById('id_sala_clinica').value = local.ID_SALA_CLINICA;
    }

    function aoSelecionarPsicologo(psicologo) {
        document.getElementById('psicologo_agend').value = psicologo.NOME_COMPL;
        document.getElementById('id_psicologo').value = psicologo.ALUNO;
    }
</script>

<!-- FLATPICKR PARA MELHORAR VISUALIZAÇÃO DE DIAS E HORÁRIOS -->
<script>
    // Inicializa o flatpickr para o campo de data
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#data", {
            dateFormat: "d-m-Y", 
            altInput: true,
            altFormat: "d-m-Y",
            locale: "pt",
            minDate: "today",
            allowInput: true,
        });

        // Inicializa o flatpickr para os campos de hora (início e fim)
        flatpickr("#hr_ini", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            allowInput: true,
        });

        // FLATPICKR CAMPO DE HORA FINAL
        flatpickr("#hr_fim", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            allowInput: true,
        });

        flatpickr("#data_fim_recorrencia", {
            dateFormat: "d-m-Y",
            altFormat: "d-m-Y",
            locale: "pt",
            minDate: "today",
            allowInput: true,
        });

        flatpickr.localize(flatpickr.l10ns.pt);
    });
</script>

<!-- SCRIPT REMOÇÃO DE ANIMAÇÃO DE AGENDAMENTO CRIADO COM SUCESSO OU MENSAGEM DE ERRO -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const alert = document.getElementById('alert-success');
        if (alert) {
            setTimeout(() => {
                alert.remove();
            }, 4000);
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

</body>

</html>