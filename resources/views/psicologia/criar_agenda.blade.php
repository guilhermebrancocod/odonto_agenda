<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
    <div id="navbar-container">
        <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

            <!-- TÍTULO -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">Agendamento</h2>
            </div>

            <!-- FORMULÁRIO DE PESQUISA (GET) -->
            <div style="margin-bottom: 20px;">
                <form action="{{ route('getPaciente') }}" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <input id="search-input" name="search" type="search" class="form-control" placeholder="Pesquisar paciente"
                            value="{{ request('search') }}"
                            style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" />
                    </div>
                    <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                        Pesquisar
                    </button>
                </form>
            </div>

            <!-- PACIENTES ENCONTRADOS -->
            @if(request()->has('search'))
                @if($pacientes->count() > 0)
                    <div style="margin-bottom: 20px;">
                        <h5 style="font-size: 16px; color: #333;">Pacientes encontrados:</h5>
                        <ul style="list-style: none; padding: 0;">
                            @foreach($pacientes as $paciente)
                                <li style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        {{ $paciente->NOME_COMPL_PACIENTE }} ({{ $paciente->CPF_PACIENTE }})
                                        - {{ \Carbon\Carbon::parse($paciente->DT_NASC_PACIENTE)->format('d/m/Y') }}
                                    </span>
                                    <a href="{{ route('getPaciente', ['selected_paciente' => $paciente->ID_PACIENTE]) }}"
                                       class="btn btn-sm btn-primary">Selecionar</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div style="margin-top: 20px; color: red;">
                        Nenhum paciente encontrado com o nome "{{ request('search') }}".
                    </div>
                @endif
            @endif

            <!-- FORMULÁRIO DE AGENDAMENTO (POST) -->
            <form action="" method="POST">
                @csrf

                @if(request()->has('selected_paciente'))
                    @php
                        $pacienteSelecionado = \App\Models\FaesaClinicaPaciente::find(request('selected_paciente'));
                    @endphp
                    @if($pacienteSelecionado)
                        <div class="alert alert-success">
                            Paciente selecionado: {{ $pacienteSelecionado->NOME_COMPL_PACIENTE }} ({{ $pacienteSelecionado->CPF_PACIENTE }})
                        </div>
                        <input type="hidden" name="paciente_id" value="{{ $pacienteSelecionado->ID_PACIENTE }}">
                    @endif
                @endif

                <div class="linha-com-titulo">
                    <h5>Horário</h5>
                    <div class="linha-flex"></div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin: 20px 0;">
                    <div style="flex: 0.2">
                        <label for="data" style="font-size: 14px; color: #666;">Dia</label>
                        <input type="date" id="data" name="data" class="form-control" required>
                    </div>
                    <div style="flex: 0.2">
                        <label for="hr_ini" style="font-size: 14px; color: #666;">Horário Início</label>
                        <input type="time" id="hr_ini" name="hr_ini" class="form-control" required>
                    </div>
                    <div style="flex: 0.2">
                        <label for="hr_fim" style="font-size: 14px; color: #666;">Horário Fim</label>
                        <input type="time" id="hr_fim" name="hr_fim" class="form-control" required>
                    </div>
                    <div style="flex: 0.2">
                        <label for="tipo" style="font-size: 14px; color: #666;">Tipo</label>
                        <input type="text" id="tipo" name="tipo" class="form-control" placeholder="Ex: Psicoterapia" required>
                    </div>
                    <div style="flex: 0.2; text-align: right;">
                        <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                            Agendar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>
</html>
