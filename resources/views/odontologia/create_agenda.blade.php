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
    <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; color: #333;">Agendamento</h2>
        </div>
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
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <div class="input-group" style="flex: 1; flex-direction: column;">
                    <div class="form-outline">
                        <select id="selectPatient" name="ID_PACIENTE"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </select>
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='reload' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Limpar">
                        <iconify-icon icon="streamline:arrow-round-left-solid"></iconify-icon>
                    </button>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='add' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Adicionar paciente">
                        <iconify-icon icon="ix:add-circle-filled"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Horário</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row g-3" style="margin: 20px 0;">
                <!-- Dia Início -->
                <div class="col-md-3">
                    <label for="date" class="form-label">Dia Início</label>
                    <input type="text" id="date" name="date" class="form-control datepicker"
                        value="{{ old('date', $agenda->DT_AGEND ?? '') }}">
                </div>

                <!-- Dia Fim (caso queira usar futuramente) -->
                <div class="col-md-3">
                    <label for="date_end" class="form-label">Dia Fim</label>
                    <input type="text" id="date_end" name="date_end" class="form-control datepicker"
                        value="{{ old('date_end', $agenda->DT_AGEND_FIM ?? '') }}">
                </div>

                <!-- Horário Início -->
                <div class="col-md-3">
                    <label for="hr_ini" class="form-label">Horário Início</label>
                    <input type="text" id="hr_ini" name="hr_ini" class="form-control timepicker"
                        value="{{ old('hr_ini', isset($agenda->HR_AGEND_INI) ? substr($agenda->HR_AGEND_INI, 0, 5) : '') }}">
                </div>

                <!-- Horário Fim -->
                <div class="col-md-3">
                    <label for="hr_fim" class="form-label">Horário Fim</label>
                    <input type="text" id="hr_fim" name="hr_fim" class="form-control timepicker"
                        value="{{ old('hr_fim', isset($agenda->HR_AGEND_FIN) ? substr($agenda->HR_AGEND_FIN, 0, 5) : '') }}">
                </div>

                <!-- Recorrência -->
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

                <!-- Tipo -->
                <div class="col-md-4">
                    <label for="dia_semana" class="form-label">Dias da semana</label>
                    <select id="dia_semana" name="dia_semana[]" class="form-select" multiple>
                        @foreach (['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'] as $dia)
                        <option value="{{ $dia }}"
                            @if (in_array($dia, old('dia_semana', isset($agenda) ? explode(',', $agenda->DIA_SEMANA ?? '') : [])))
                            selected
                            @endif>
                            {{ ucfirst($dia) }}-feira
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Haverá Pagamento -->
                <div class="col-md-4">
                    <label for="pagto" class="form-label">Haverá Pagamento?</label>
                    <select id="pagto" name="pagto" class="form-select">
                        <option value=""></option>
                        <option value="S">Sim</option>
                        <option value="N">Não</option>
                    </select>
                </div>

                <!-- Serviço -->
                <div class="col-md-4">
                    <label for="servico" class="form-label">Serviço</label>
                    <select id="servico" name="servico" class="form-select">
                        <option value=""></option>
                        <option value="1" {{ old('servico', $agenda->ID_SERVICO ?? '') == '1' ? 'selected' : '' }}>Limpeza</option>
                        <option value="2" {{ old('servico', $agenda->ID_SERVICO ?? '') == '2' ? 'selected' : '' }}>Retirada canal</option>
                    </select>
                </div>

                <!-- Valor -->
                <div class="col-md-4">
                    <label for="valor" class="form-label">Valor</label>
                    <input type="text" id="valor" class="form-control"
                        value="{{ old('valor', $agenda->VALOR_AGEND ?? '') }}" {{ old('pagto', $agenda->HAVERA_PAGAMENTO ?? '') != 'S' ? 'disabled' : '' }}>
                </div>

                <!-- Observação (apenas edição) -->
                @if(isset($agenda))
                <div class="col-md-8">
                    <label for="obs" class="form-label">Observações</label>
                    <input type="text" id="obs" name="obs" class="form-control"
                        value="{{ old('obs', $agenda->OBSERVACOES ?? '') }}">
                </div>
                @endif

                <!-- Status e Remarcado (apenas edição) -->
                @if(isset($agenda))
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="0" {{ $agenda->STATUS_AGEND == 0 ? 'selected' : '' }}>Agendado</option>
                        <option value="1" {{ $agenda->STATUS_AGEND == 1 ? 'selected' : '' }}>Cancelado</option>
                        <option value="2" {{ $agenda->STATUS_AGEND == 2 ? 'selected' : '' }}>Finalizado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="remarcado" class="form-label">Remarcado?</label>
                    <select id="remarcado" name="remarcado" class="form-select">
                        <option value="0" {{ $agenda->ID_AGEND_REMARCADO == 0 ? 'selected' : '' }}>Não</option>
                        <option value="1" {{ $agenda->ID_AGEND_REMARCADO > 0 ? 'selected' : '' }}>Sim</option>
                    </select>
                </div>
                @endif
            </div>

            <div style="text-align: right;flex:1">
                <button id="btn-agendar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Agendar
                </button>
            </div>
            <div class="linha-com-titulo">
                <h5></h5>
                <div class="linha-flex"></div>
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