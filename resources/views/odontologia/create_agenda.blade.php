<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" rel="stylesheet" />

    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Agendamento</legend>
        </fieldset>
        <form class="row g-3 needs-validation"
            action="{{ isset($agenda) ? route('updateAgenda', $agenda->ID_AGENDAMENTO) : route('createAgenda') }}"
            method="POST">
            @csrf
            @if(isset($agenda))
            @method('PUT')
            @endif
            <div class="linha-com-titulo">
                <h5>Paciente</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; margin: 15px 0;">
                <div style="flex: 1; min-width: 250px;">
                    <select id="selectPatient" name="ID_PACIENTE" class="form-control" style="width: 100%;">
                        {{-- A opção será carregada via AJAX --}}
                        @if(old('ID_PACIENTE') && old('NOME_PACIENTE'))
                        <option value="{{ old('ID_PACIENTE') }}" selected>{{ old('NOME_PACIENTE') }}</option>
                        @endif
                    </select>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="button" id="reload"
                        onclick="location.reload();"
                        style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;"
                        title="Limpar / Recarregar">
                        <iconify-icon icon="streamline:arrow-round-left-solid"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Horário</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row g-3" style="margin: 15px 0;">
                <!-- Data e horário -->
                <div class="col-md-3">
                    <label for="date" class="form-label">Dia Início</label>
                    <input type="text" id="date" name="date" class="form-control datepicker"
                        value="{{ old('date', isset($agenda->DT_AGEND) ? \Carbon\Carbon::parse($agenda->DT_AGEND)->format('d/m/Y') : '') }}">
                </div>

                <div class="col-md-3">
                    <label for="date_end" class="form-label">Dia Fim</label>
                    <input type="text" id="date_end" name="date_end" class="form-control datepicker"
                        value="{{ old('date_end', isset($agenda->DT_AGEND) ? \Carbon\Carbon::parse($agenda->DT_AGEND)->format('d/m/Y') : '') }}">
                </div>

                <div class="col-md-3">
                    <label for="hr_ini" class="form-label">Horário Início</label>
                    <input type="text" id="hr_ini" name="hr_ini" class="form-control timepicker"
                        value="{{ old('hr_ini', isset($agenda->HR_AGEND_INI) ? substr($agenda->HR_AGEND_INI, 0, 5) : '') }}">
                </div>

                <div class="col-md-3">
                    <label for="hr_fim" class="form-label">Horário Fim</label>
                    <input type="text" id="hr_fim" name="hr_fim" class="form-control timepicker"
                        value="{{ old('hr_fim', isset($agenda->HR_AGEND_FIN) ? substr($agenda->HR_AGEND_FIN, 0, 5) : '') }}">
                </div>

                <!-- Tipo de agendamento e dia da semana -->
                <div class="col-md-4">
                    <label for="recorrencia" class="form-label">Tipo de agendamento</label>
                    <select id="recorrencia" name="recorrencia" class="form-select">
                        <option value=""></option>
                        @foreach (['pontual', 'recorrencia'] as $opcao)
                        <option value="{{ $opcao }}" {{ old('recorrencia', trim($agenda->RECORRENCIA ?? '')) == $opcao ? 'selected' : '' }}>
                            {{ ucfirst($opcao) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="dia_semana" class="form-label">Dias da semana</label>
                    <select id="dia_semana" name="dia_semana[]" class="form-select" multiple>
                        @php
                        use Carbon\Carbon;

                        $diasMap = [
                        0 => 'domingo', 1 => 'segunda', 2 => 'terca', 3 => 'quarta',
                        4 => 'quinta', 5 => 'sexta', 6 => 'sabado',
                        ];

                        $recorrenciaAtual = old('recorrencia', trim($agenda->RECORRENCIA ?? ''));
                        $diasSelecionados = [];

                        if ($recorrenciaAtual === 'pontual' && isset($agenda->DT_AGEND)) {
                        $data = Carbon::parse($agenda->DT_AGEND);
                        $diasSelecionados = [$diasMap[$data->dayOfWeek]];
                        } else {
                        $diasSelecionados = old('dia_semana', isset($agenda) ? explode(',', $agenda->DIA_SEMANA ?? '') : []);
                        }
                        @endphp

                        @foreach (['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'] as $dia)
                            <option value="{{ $dia }}" {{ in_array($dia, $diasSelecionados) ? 'selected' : '' }}>
                                {{ ucfirst($dia) }}-feira
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pagamento -->
                @php
                $pagto = old('pagto');
                if (is_null($pagto) && isset($agenda)) {
                $pagto = $agenda->VALOR_AGEND !== null ? 'S' : 'N';
                }
                @endphp
                <div class="col-md-4">
                    <label for="pagto" class="form-label">Haverá Pagamento?</label>
                    <select id="pagto" name="pagto" class="form-select">
                        <option value=""></option>
                        <option value="S" {{ $pagto === 'S' ? 'selected' : '' }}>Sim</option>
                        <option value="N" {{ $pagto === 'N' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>

                <!-- Serviço e Valor -->
                <div class="col-md-3">
                    <label class="form-label">Serviço</label>
                    <select id="form-select" class="form-select" name="servico"></select>
                </div>

                @php
                $valorDisabled = $pagto === 'N' ? 'disabled' : '';
                @endphp
                <div class="col-md-3">
                    <label for="valor" class="form-label">Valor</label>
                    <input type="text" id="valor" name="valor" class="form-control"
                        value="{{ old('valor', $agenda->VALOR_AGEND ?? '') }}"
                        {{ $valorDisabled }}>
                </div>

                <!-- Status e Remarcado -->
                @php
                $status = old('status', $agenda->STATUS_AGEND ?? 'Agendado');
                $remarcado = old('remarcado', isset($agenda) ? ($agenda->UPDATED_AT ? '1' : '0') : '0');
                @endphp

                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="Agendado" {{ $status == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                        <option value="Presente" {{ $status == 'Presente' ? 'selected' : '' }}>Presente</option>
                        <option value="Cancelado" {{ $status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="remarcado" class="form-label">Remarcado?</label>
                    <select id="remarcado" name="remarcado" class="form-select" disabled>
                        <option value="0" {{ $remarcado == '0' ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ $remarcado == '1' ? 'selected' : '' }}>Sim</option>
                    </select>
                </div>

                <!-- Observações -->
                <div class="col-md-9">
                    <label for="obs" class="form-label">Observações</label>
                    <input type="text" id="obs" name="obs" class="form-control"
                        value="{{ old('obs', $agenda->OBSERVACOES ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Box de atendimento</label>
                    <select id="form-select-box" class="form-select" name="boxes"></select>
                </div>
            </div>
            <div style="text-align: right;flex:1">
                <button class="btn btn-primary btn-lg" id="btn-agendar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    <i class="bi bi-calendar-plus"></i> Agendar
                </button>
            </div>
        </form>
    </div>
    <script>
        window.agendaData = {
            pacienteId: "{{ $agenda->ID_PACIENTE ?? '' }}",
            nomePaciente: "{{ $agenda->NOME_COMPL_PACIENTE ?? '' }}"
        };
    </script>
    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
        });
    </script>
    @endif
    <!-- jQuery (PRIMEIRO e APENAS UMA VEZ) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle (inclui Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 principal + idioma português -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>

    <!-- Bootstrap Datepicker + idioma -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>

    <!-- Bootstrap Timepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/js/bootstrap-timepicker.min.js"></script>

    <!-- Máscara de input -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <!-- MDB UI Kit (se estiver usando componentes dele) -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

    <!-- Iconify (opcional, para ícones) -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>


    <!-- Seu script -->
    <script type="module" src="/js/odontologia/create_agenda.js"></script>
</body>

</html>