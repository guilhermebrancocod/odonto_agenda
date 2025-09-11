<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-timepicker/0.5.2/css/bootstrap-timepicker.min.css" rel="stylesheet" />

    <link href="/css/style.css" rel="stylesheet">
</head>
<style>
    /* Layout das duas colunas */
    .agendamento-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        align-items: start;
    }

    /* Campo padrão */
    .field {
        margin-bottom: 12px;
    }

    .field label {
        font-size: 14px;
        color: #666;
        margin-bottom: 6px;
    }

    .field .form-select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    /* Grade de horários como "cards" */
    .horarios-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        /* 4 por linha */
        gap: 6px;
    }

    /* Item */
    .time-item {
        position: relative;
    }

    /* Input escondido (usa a label como botão) */
    .time-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    /* Card do horário */
    .time-card {
        display: inline-block;
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        line-height: 1;
        text-align: center;
        background: #fff;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease;
    }

    /* Hover / foco */
    .time-card:hover {
        border-color: #bbb;
        transform: translateY(-1px);
    }

    .time-input:focus+.time-card,
    .time-input:focus-visible+.time-card {
        outline: 2px solid #8dbbfd;
        outline-offset: 2px;
    }

    /* Selecionado */
    .time-input:checked+.time-card {
        background: #e8f3ff;
        border-color: #8dbbfd;
        box-shadow: inset 0 0 0 1px #8dbbfd;
        font-weight: 600;
    }

    /* Desabilitado (se usar disabled no input) */
    .time-input:disabled+.time-card {
        background: #f8f9fa;
        color: #adb5bd;
        border-color: #e9ecef;
        cursor: not-allowed;
        transform: none;
    }

    /* Responsivo opcional */
    @media (max-width: 992px) {
        .horarios-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .horarios-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<body>
    @include('components.sidebar')
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);width: 100%;">
        <fieldset class="border p-1 rounded mb-3">
            <legend class="w-auto px-2">Novo agendamento</legend>
        </fieldset>
        <form class="row g-3 needs-validation" onsubmit="return validarFormulario()"
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
                        <option
                            value="{{ old('ID_PACIENTE', isset($agenda->ID_PACIENTE) ? $agenda->ID_PACIENTE : '') }}"
                            selected>
                            {{ isset($agenda->NOME_COMPL_PACIENTE) ? $agenda->NOME_COMPL_PACIENTE : 'Selecione um paciente' }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Datalhes</h5>
                <div class="linha-flex"></div>
                @php
                $status = old('status', $agenda->STATUS_AGEND ?? 'Agendado');
                @endphp
                <div class="col-12 col-md-4 col-lg-2">
                    <div class="form-floating">
                        <select id="status" name="status" class="form-select">
                            <option value="Agendado" {{ $status == 'Agendado' ? 'selected' : '' }}>Agendado</option>
                            <option value="Presente" {{ $status == 'Presente' ? 'selected' : '' }}>Presente</option>
                            <option value="Cancelado" {{ $status == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        <label for="status">Status</label>
                    </div>
                </div>
                <!-- Linha: Data e Hora (floating reduz a altura) -->
                <div class="col-6 col-md-2">
                    <div class="form-floating">
                        <input
                            type="text"
                            id="date"
                            name="date"
                            class="form-control datepicker"
                            placeholder="dd/mm/aaaa"
                            inputmode="numeric"
                            value="{{ old('date', isset($agenda->DT_AGEND) ? \Carbon\Carbon::parse($agenda->DT_AGEND)->format('d/m/Y') : '') }}">
                        <label for="date">Dia Início</label>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="form-floating">
                        <input
                            type="text"
                            id="date_end"
                            name="date_end"
                            class="form-control datepicker"
                            placeholder="dd/mm/aaaa"
                            inputmode="numeric"
                            value="{{ old('date_end', isset($agenda->DT_AGEND_FINAL) ? \Carbon\Carbon::parse($agenda->DT_AGEND_FINAL)->format('d/m/Y') : '') }}">
                        <label for="date_end">Dia Fim</label>
                    </div>
                </div>
            </div>
            <div class="row g-4 align-items-start my-1">
                <!-- Coluna principal (mais larga) -->
                <div class="col-12 col-lg-12">
                    <div class="row g-3">
                        <!-- Linha: Disciplina / Box / Procedimento -->
                        @php
                        // Define defaults
                        $valor = old('disciplina', isset($agenda) ? ($agenda->DISCIPLINA ?? '') : '');
                        $rotulo = '';

                        // Monta rótulo apenas se houver agenda/valor
                        if ($valor !== '' && isset($agenda)) {
                        $rotulo = ($agenda->DISCIPLINA ?? '') . '-' . ($agenda->DISCIPLINA_NOME ?? '');
                        }
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="form-floating">
                                <select id="form-select-discipline" name="disciplina" class="form-select">
                                    <option value="" {{ $valor === '' ? 'selected' : '' }}>Disciplina</option>
                                    @if($valor !== '')
                                    <option value="{{ $valor }}" selected>{{ $rotulo !== '' ? $rotulo : $valor }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-2">
                            <div class="form-floating">
                                <select id="form-select-box" name="ID_BOX" class="form-select">
                                    <option value="" {{ old('ID_BOX', $agenda->ID_BOX ?? '') == '' ? 'selected' : '' }}>
                                        Box
                                    </option>
                                    @if(isset($agenda) && $agenda->ID_BOX)
                                    <option value="{{ old('ID_BOX', $agenda->ID_BOX ?? '') }}" selected>
                                        {{ $agenda->DESCRICAO ?? '' }}
                                    </option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="form-floating">
                                <select id="form-select-turma" name="turma" class="form-select">
                                    <option value="" {{ old('TURMA', $agenda->TURMA ?? '') == '' ? 'selected' : '' }}>
                                        Turma
                                    </option>
                                    @if(isset($agenda) && $agenda->ID_BOX)
                                    <option value="{{ old('ID_SERVICO', isset($agenda->ID_SERVICO) ? $agenda->ID_SERVICO : '') }}" selected>
                                        {{ isset($agenda->TURMA) ? $agenda->TURMA: '' }}
                                    </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="form-floating">
                                <select id="form-select-proc" name="procedimento" class="form-select">
                                    <option value="" {{ old('SERVICO_CLINICA_DESC', $agenda->SERVICO_CLINICA_DESC ?? '') == '' ? 'selected' : '' }}>
                                        Procedimento
                                    </option>
                                    @if(isset($agenda) && $agenda->ID_SERVICO)
                                    <option value="{{ old('ID_SERVICO', isset($agenda->ID_SERVICO) ? $agenda->ID_SERVICO : '') }}" selected>
                                        {{ isset($agenda->SERVICO_CLINICA_DESC) ? $agenda->SERVICO_CLINICA_DESC : '' }}
                                    </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        @php
                        $hrIniRaw = old('hr_ini', $agenda->HR_AGEND_INI ?? null);
                        $hrFimRaw = old('hr_fim', $agenda->HR_AGEND_FIN ?? null);
                        $hrIni = $hrIniRaw ? \Carbon\Carbon::parse($hrIniRaw)->format('H:i') : '';
                        $hrFim = $hrFimRaw ? \Carbon\Carbon::parse($hrFimRaw)->format('H:i') : '';
                        @endphp

                        <div class="row g-3 align-items-start"><!-- PAI: duas colunas irmãs -->
                            <!-- ESQUERDA: horários -->
                            <div class="col-12 col-lg-9">
                                <fieldset class="border rounded p-3" style="margin-top:10px;">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Horários</legend>

                                    <div id="horarios-grid"
                                        class="horarios-grid"
                                        data-disciplina="{{ $agenda->DISCIPLINA ?? '' }}"
                                        data-turma="{{ $agenda->TURMA ?? '' }}"
                                        data-dia="{{ $agenda->DIA_SEMANA ?? '' }}"
                                        data-hr-ini="{{ $hrIni }}"
                                        data-hr-fim="{{ $hrFim }}"
                                        data-selected='@json($horariosSelecionados ?? [])'>
                                    </div>

                                    <input type="hidden" name="hr_ini" id="hr_ini" value="{{ $hrIni }}">
                                    <input type="hidden" name="hr_fim" id="hr_fim" value="{{ $hrFim }}">

                                    @isset($agenda)
                                    <div class="text-muted small mt-2">Intervalo atual: {{ $hrIni }} – {{ $hrFim }}</div>
                                    @endisset
                                </fieldset>
                            </div>

                            <!-- DIREITA: recorrência -->
                            <div class="col-12 col-lg-3">
                                <fieldset class="border rounded p-3 h-100 sticky-top" style="top: 12px;">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Recorrência</legend>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input" id="recorrenciapon" type="radio" name="recorrencia" value="1"
                                            {{ old('recorrencia', '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recorrenciapon">Pontual</label>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input class="form-check-input" id="recorrenciarec" type="radio" name="recorrencia" value="2"
                                            {{ old('recorrencia') == '2' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="recorrenciarec">Recorrência</label>
                                    </div>

                                    <div class="fw-semibold mb-2">Dias da semana</div>
                                    <div class="row row-cols-3 g-2">
                                        @php $dias = ['1'=>'Seg','2'=>'Ter','3'=>'Qua','4'=>'Qui','5'=>'Sex','6'=>'Sáb']; @endphp
                                        @foreach($dias as $val=>$lbl)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="dia_{{ $val }}" name="dia_recorrencia[]" value="{{ $val }}"
                                                    {{ (collect(old('dia_recorrencia', []))->contains($val)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dia_{{ $val }}">{{ $lbl }}</label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Observações ocupa largura total para respiro -->
                <div class="col-12">
                    <div class="form-floating">
                        <input
                            type="text"
                            id="obs"
                            name="obs"
                            class="form-control"
                            placeholder="Escreva observações"
                            value="{{ old('obs', $agenda->OBSERVACOES ?? '') }}">
                        <label for="obs">Observações</label>
                    </div>
                </div>
                <!-- Status alinhado à direita em telas grandes -->
                @php
                $remarcado = old('remarcado', isset($agenda) ? ($agenda->UPDATED_AT ? '1' : '0') : '0');
                @endphp
            </div>
            <div class="mb-5">
                <div class="linha-com-titulo">
                    <h5>Pagamento</h5>
                    <div class="linha-flex"></div>
                </div>
                <!-- Pagamento -->
                @php
                $pagto = old('pagto');
                if (is_null($pagto) && isset($agenda)) {
                $pagto = $agenda->VALOR_AGEND !== null ? 'S' : 'N';
                }
                @endphp
                <div class="row g-3 align-items-end">
                    <!-- Haverá Pagamento? -->
                    <div class="col-12 col-md-3">
                        <label for="pagto" class="form-label">Haverá Pagamento?<div id="help-pagto" class="form-text">Selecione “Sim” para informar o valor.</div></label>
                        <select id="pagto" name="pagto" class="form-select" aria-describedby="help-pagto">
                            <option value=""></option>
                            <option value="S" {{ $pagto === 'S' ? 'selected' : '' }}>Sim</option>
                            <option value="N" {{ $pagto === 'N' ? 'selected' : '' }}>Não</option>
                        </select>
                        @error('pagto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    @php $valorDisabled = ($pagto === 'N') ? 'disabled' : ''; @endphp
                    <div class="col-12 col-md-3">
                        <label for="valor" class="form-label" style="font-size: 15px;">Valor</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input
                                type="text"
                                id="valor"
                                name="valor"
                                class="form-control"
                                placeholder="0,00"
                                inputmode="decimal"
                                value="{{ old('valor', $agenda->VALOR_AGEND ?? '') }}"
                                {{ $valorDisabled }}>
                        </div>
                        @error('valor')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="remarcado" class="form-label" style="font-size: 15px;">Remarcado?</label>
                        <select id="remarcado" name="remarcado" class="form-select" disabled>
                            <option value="0" {{ $remarcado == '0' ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ $remarcado == '1' ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex justify-content-md-end">
                        <button class="btn btn-primary btn-lg w-100 w-md-auto" id="btn-agendar" type="submit">
                            <i class="bi bi-calendar-plus"></i> Agendar
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
    </div>
    <script>
        (function() {
            const pagto = document.getElementById('pagto');
            const valor = document.getElementById('valor');

            if (pagto && valor) {
                const toggleValor = () => {
                    const isPago = pagto.value === 'S';
                    valor.disabled = !isPago;
                    if (!isPago) valor.value = valor.value; // mantém valor; remova esta linha se quiser limpar
                };
                pagto.addEventListener('change', toggleValor);
                // garante estado correto ao carregar
                toggleValor();
            }
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Erros de validação (errors bag) --}}
    @if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erro ao validar informações',
            html: `<ul style="text-align:left;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>`,
            showConfirmButton: true,
            confirmButtonText: 'Ok',
            allowOutsideClick: false
        }).then(r => {
            if (r.isConfirmed) window.location.reload();
        });
    </script>
    @endif

    {{-- Mensagem única do backend (ex.: with('alert','...') ou with('success','...') ) --}}
    @if (session('alert') || session('success') || session('warning'))
    @php
    $type = session('success') ? 'success' : (session('warning') ? 'warning' : 'warning');
    $title = session('success') ?? session('warning') ?? session('alert');
    @endphp
    <script>
        Swal.fire({
            icon: @json($type),
            title: @json($title), // vem do backend
            showConfirmButton: true,
            confirmButtonText: 'Ok',
            allowOutsideClick: false
        }).then(r => {
            if (r.isConfirmed) window.location.reload();
        });
    </script>
    @endif

    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
        }).then(() => {
            window.location.href = "{{ url('odontologia/consultaragenda') }}";
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