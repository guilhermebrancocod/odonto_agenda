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
    <!-- TOM SELECT -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
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

<div class="container ms-3 mw-100">

    <!-- COMPONENT DE HEADER DE CADA PÁGINA DAS VIEWS -->
    <x-page-title>
    </x-page-title>

        <div class="col-12 shadow-lg shadow-dark p-4 bg-body-tertiary rounded">

            <!-- FORM DE AGENDAMENTO -->
            <form action="{{ route('criarAgendamento-Psicologo') }}" method="POST" id="agendamento-form" class="w-100" validate>
                @csrf

                <!-- VALORES PASSADOS NO FORMATO HIDDEN | USUÁRIO NÃO SELECIONA DIRETAMENTE -->
                
                <input type="hidden" name="paciente_id" id="paciente_id" value="{{ old('paciente_id') }}"/>

                <input type="hidden" name="id_servico" id="id_servico" value="{{ old('id_servico') }}" />

                <input type="hidden" name="recorrencia" id="recorrencia"/>

                <input type="hidden" name="status_agend" value="Em aberto"/>

                <input type="hidden" name="id_psicologo" id="id_psicologo" value="{{ session('psicologo')[1] }}"/>

                <div id="session-data"
                   data-psicologo='@json(session('psicologo'))'
                ></div>

                {{-- CAMPO DE PESQUISA POR PACIENTES --}}
                 <div class="mb-3 position-relative">
                    <label for="search-input" class="form-label">Paciente</label>
                    <select id="search-input" name="paciente_id" placeholder="Pesquisar paciente por nome ou CPF..." autocomplete="off"></select>
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
                        <label for="disciplina" class="form-label"></label>
                        <select name="disciplina" id="disciplina" placeholder="Disciplina do Atendimento" autocomplete="off"></select>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="data" class="form-label">Dia</label>
                        <input type="text" id="data" name="dia_agend" class="form-control" value="{{ old('dia_agend') }}" placeholder="Selecione o Dia">
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="hr_ini" class="form-label">Horário Início</label>
                        <input type="text" id="hr_ini" name="hr_ini" class="form-control" value="{{ old('hr_ini') }}" disabled>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="hr_fim" class="form-label">Horário Fim</label>
                        <input type="text" id="hr_fim" name="hr_fim" class="form-control" value="{{ old('hr_fim') }}" disabled>
                    </div>

                    <!-- Mensagem que aparece quando ativa recorrência -->
                    <div id="msg-recorrencia" class="alert alert-info mt-2 d-none">
                        Caso não selecione dia da semana e/ou data fim, serão gerados agendamentos por 1 mês por padrão.
                    </div>

                    <!-- LOCAL -->
                    <input type="hidden" name="id_sala_clinica" id="id_sala_clinica">
                    <div class="col-sm-6 col-md-3 mt-2 position-relative">
                        <label for="local_agend" class="form-label">Local</label>
                        <label for="local_agend" class="form-label"></label>
                        <select name="local_agend" id="local_agend" placeholder="Selecione uma sala"></select>
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
<!-- TOM SELECT -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<!-- TOM SELECT PARA CAMPOS DE BUSCA -->
<script>

    // BUSCA DE PACIENTE
    const pacienteSelect = new TomSelect('#search-input', {
        valueField: 'ID_PACIENTE',
        labelField: 'NOME_COMPL_PACIENTE',
        searchField: ['NOME_COMPL_PACIENTE', 'CPF_PACIENTE'],

        render: {
            option: (data, escape) => {
                const cpf = data.CPF_PACIENTE || 'CPF não informado';
                return `<div>
                            <strong>${escape(data.NOME_COMPL_PACIENTE)}</strong>
                            <small class="d-block text-muted">${escape(cpf)}</small>
                        </div>`;
            },
            item: (data, escape) => {
                return `<div>${escape(data.NOME_COMPL_PACIENTE)}</div>`;
            },
            // Mensagem quando não há resultados
            no_results: (data, escape) => {
                return `<div class="no-results">Nenhum paciente encontrado para "<strong>${escape(data.input)}</strong>".</div>`;
            },
        },
        // Carrega dados da sua API
        load: (query, callback) => {
            if (query.length < 2) return callback();
            const url = `/psicologo/consultar-paciente/buscar?search=${encodeURIComponent(query)}`;
            fetch(url)
            .then(response => response.json())
                .then(json => callback(json))
                .catch(() => callback());
        },
    });

    // BUSCA DE DISCIPLINAS
    const disciplinaSelect = new TomSelect('#disciplina', {
        valueField: 'ID_SERVICO_CLINICA',
        labelField: 'SERVICO_CLINICA_DESC',
        searchField: ['SERVICO_CLINICA_DESC'],
        createFilter: (input) => input.length > 0,
        load: (query, callback) => {
            const url = `/psicologo/pesquisar-disciplina?search=${encodeURIComponent(query)}`;
            fetch(url)
                .then(r => r.json())
                .then(j => callback(j))
                .catch(() => callback());
        },
    });

    // BUSCA DE LOCAL
    const localSelect = new TomSelect('#local_agend', {
        valueField: 'ID_SALA_CLINICA',
        labelField: 'DESCRICAO',
        searchField: ['DESCRICAO'],
        createFilter: (input) => input.length > 0,
        load: (query, callback) => {
            const url = `/psicologia/pesquisar-local?search=${encodeURIComponent(query)}`;
            fetch(url)
                .then(r => r.json())
                .then(j => callback(j))
                .catch(() => callback());
        },
    });
</script>

<!-- FLATPICKR PARA MELHORAR VISUALIZAÇÃO DE DIAS E HORÁRIOS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
    flatpickr.localize(flatpickr.l10ns.pt);

    const dataInput = document.getElementById('data');
    const hrInicialInput = document.getElementById('hr_ini');
    const hrFinalInput = document.getElementById('hr_fim');

    // --- REGRA DE NEGÓCIO PRINCIPAL ---
    // Calcula a data e hora mínimas permitidas para o agendamento (agora + 8 horas).
    const dataMinimaAgendamento = new Date();
    dataMinimaAgendamento.setHours(dataMinimaAgendamento.getHours() + 8);

    // Para consistência com minuteIncrement, arredondamos os minutos para o próximo incremento de 15.
    const minutos = dataMinimaAgendamento.getMinutes();
    const minutosArredondados = Math.ceil(minutos / 15) * 15;
    dataMinimaAgendamento.setMinutes(minutosArredondados);
    dataMinimaAgendamento.setSeconds(0);
    // ------------------------------------


    // --- Instâncias do Flatpickr ---

    const hrFinalFP = flatpickr(hrFinalInput, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });

    const hrInicialFP = flatpickr(hrInicialInput, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        minuteIncrement: 15,
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                const dataInicio = selectedDates[0];
                const dataFim = new Date(dataInicio.getTime());
                dataFim.setHours(dataFim.getHours() + 1);
                hrFinalFP.setDate(dataFim, true);
            }
        }
    });

    const dataFP = flatpickr(dataInput, {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        locale: "pt",
        // A data mínima agora é a data calculada com 8 horas a mais.
        // O Flatpickr vai desabilitar hoje se a hora mínima já passou para o dia seguinte.
        minDate: dataMinimaAgendamento,
        maxDate: new Date().fp_incr(7),
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                hrInicialFP.clear();
                hrFinalFP.clear();
                hrInicialInput.removeAttribute('disabled');
                
                // A função agora usa a data mínima global para se guiar.
                atualizarMinHora(selectedDates[0]);
            } else {
                hrInicialInput.setAttribute('disabled', 'disabled');
            }
        }
    });

    // --- Funções Auxiliares ---
    function atualizarMinHora(dataSelecionada) {
        let minTime = "00:00";
        
        // Normalizamos as datas para comparar apenas o dia (ignorando horas/minutos).
        const diaMinimoPermitido = new Date(dataMinimaAgendamento).setHours(0, 0, 0, 0);
        const diaSelecionado = new Date(dataSelecionada).setHours(0, 0, 0, 0);

        // Se o usuário selecionou o primeiro dia disponível...
        if (diaSelecionado === diaMinimoPermitido) {
            // ... a hora mínima é a hora da nossa data mínima calculada.
            const h = String(dataMinimaAgendamento.getHours()).padStart(2, '0');
            const m = String(dataMinimaAgendamento.getMinutes()).padStart(2, '0');
            minTime = `${h}:${m}`;
        }
        // Se for qualquer outro dia no futuro, a hora mínima é 00:00.
        
        hrInicialFP.set('minTime', minTime);
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