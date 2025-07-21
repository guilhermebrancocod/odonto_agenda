<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Agendamento</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-input { background-image: none !important; }
    </style>
</head>
<body class="bg-light">

    @include('components.navbar')

    <div class="container mt-5">
        <div class="bg-white p-4 rounded shadow-sm">
            <h2 class="mb-4 text-center">Editar Agendamento</h2>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('agendamento.update', $agendamento->ID_AGENDAMENTO) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Paciente</label>
                    <input type="text" class="form-control" value="{{ $agendamento->paciente->NOME_COMPL_PACIENTE ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Serviço</label>
                    <input type="text" class="form-control" value="{{ $agendamento->servico->SERVICO_CLINICA_DESC ?? '-' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data</label>
                    <input type="text" id="date" name="date" class="form-control" value="{{ $agendamento->DT_AGEND }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Hora Início</label>
                    <input type="text" id="start_time" name="start_time" class="form-control" value="{{ $agendamento->HR_AGEND_INI }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Hora Fim</label>
                    <input type="text" id="end_time" name="end_time" class="form-control" value="{{ $agendamento->HR_AGEND_FIN }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Agendado" {{ $agendamento->STATUS_AGEND == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                        <option value="Em atendimento" {{ $agendamento->STATUS_AGEND == 'Em atendimento' ? 'selected' : '' }}>Em atendimento</option>
                        <option value="Finalizado" {{ $agendamento->STATUS_AGEND == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            locale: "pt",
        });

        flatpickr("#start_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i"
        });

        flatpickr("#end_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15,
            altInput: true,
            altFormat: "H:i"
        });
    </script>
</body>
</html>
