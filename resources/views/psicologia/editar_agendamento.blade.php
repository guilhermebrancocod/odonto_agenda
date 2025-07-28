<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Agendamento</title>
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

                <form action="{{ route('agendamento.update', $agendamento->ID_AGENDAMENTO) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="paciente" class="form-label">Paciente</label>
                        <input type="text" id="paciente" class="form-control" value="{{ $agendamento->paciente->NOME_COMPL_PACIENTE ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="servico" class="form-label">Serviço</label>
                        <input type="text" id="servico" class="form-control" value="{{ $agendamento->servico->SERVICO_CLINICA_DESC ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="clinica" class="form-label">Clínica</label>
                        <input type="text" id="clinica" class="form-control" value="{{ $agendamento->clinica->NOME_CLINICA ?? '-' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="local" class="form-label">Local <span class="text-danger">*</span></label>
                        <input type="text" id="local" name="local" class="form-control @error('local') is-invalid @enderror" 
                               value="{{ old('local', $agendamento->LOCAL ?? '') }}" placeholder="Digite o local" required>
                        @error('local')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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

                    <div class="mb-4">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Agendado" {{ old('status', $agendamento->STATUS_AGEND) == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                            <option value="Em atendimento" {{ old('status', $agendamento->STATUS_AGEND) == 'Em atendimento' ? 'selected' : '' }}>Em atendimento</option>
                            <option value="Finalizado" {{ old('status', $agendamento->STATUS_AGEND) == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-success flex-grow-1 fw-bold">
                            <i class="bi bi-save me-2"></i> Salvar Alterações
                        </button>
                        <button type="button" class="btn btn-outline-secondary flex-grow-1 fw-bold" onclick="history.back()">
                            <i class="bi bi-x-circle me-2"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Flatpickr pt-br -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            locale: "pt",
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
</body>
</html>