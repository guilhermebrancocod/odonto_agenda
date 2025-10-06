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
                        <label for="date">Data</label>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="form-floating">
                        <input
                            type="text"
                            id="date"
                            name="date_end"
                            class="form-control datepicker"
                            placeholder="dd/mm/aaaa"
                            inputmode="numeric"
                            value="{{ old('date_end', isset($agenda->DT_AGEND_FINAL) ? \Carbon\Carbon::parse($agenda->DT_AGEND_FINAL)->format('d/m/Y') : '') }}">
                        <label for="date">Data Final</label>
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
                                    @if(isset($agenda) && $agenda->TURMA)
                                    <option value="{{ old('TURMA', isset($agenda->TURMA) ? $agenda->TURMA : '') }}" selected>
                                        {{ isset($agenda->TURMA) ? $agenda->TURMA: '' }}
                                    </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-lg-6">
                                @php
                                // $alunosSelecionados pode ser:
                                // - mapa [id => nome], ou
                                // - lista de objetos [{id, nome}] / stdClass com ->id, ->nome
                                $lista = $alunosSelecionados ?? [];
                                $temSelecionados = !empty($lista);
                                @endphp

                                <fieldset class="border rounded p-3">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Alunos</legend>

                                    <div
                                        id="alunos-readonly"
                                        class="d-flex flex-wrap gap-2"
                                        aria-live="polite"
                                        @if(!$temSelecionados)
                                        data-ajax="1"
                                        data-disc="{{ $disc ?? '' }}"
                                        data-turma="{{ $turma ?? '' }}"
                                        data-box="{{ $box ?? '' }}"
                                        @endif>
                                        @if($temSelecionados)
                                        {{-- MODO CONSULTA/EDIÇÃO: renderiza direto da variável --}}
                                        @foreach($lista as $k => $v)
                                        @php
                                        // Suporta tanto mapa [id=>nome] quanto objetos
                                        $id = is_object($v) ? ($v->id ?? $v->ALUNO ?? $k) : $k;
                                        $nome = is_object($v) ? ($v->nome ?? $v->NOME ?? $v->NOME_COMPL ?? $v->NOME_COMPL_PACIENTE ?? '') : $v;
                                        @endphp
                                        <span class="badge bg-secondary">{{ $nome ?: 'Aluno' }} ({{ $id }})</span>
                                        @endforeach
                                        @else
                                        {{-- MODO CRIAÇÃO: placeholder enquanto AJAX carrega --}}
                                        <span class="text-muted">Selecione: dia, disciplina e box</span>
                                        @endif
                                    </div>

                                    {{-- (Opcional) eco de IDs mostrados --}}
                                    <input type="hidden" name="alunos_visualizados" id="alunos-ids-eco" value="">
                                </fieldset>
                            </div>
                            @php
                            $hrIniRaw = old('inicio', $agenda->HR_AGEND_INI ?? null);
                            $hrFimRaw = old('fim', $agenda->HR_AGEND_FIN ?? null);
                            $hrIni = $hrIniRaw ? \Carbon\Carbon::parse($hrIniRaw)->format('H:i') : '';
                            $hrFim = $hrFimRaw ? \Carbon\Carbon::parse($hrFimRaw)->format('H:i') : '';
                            @endphp
                            <!-- PAI: duas colunas irmãs -->
                            <!-- ESQUERDA: horários -->
                            <div class="col-12 col-lg-6">
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
                                        <span class="text-muted">Informe dados</span>
                                    </div>
                                    @isset($agenda)
                                    <div class="text-muted small mt-2">Intervalo atual: {{ $hrIni }} – {{ $hrFim }}</div>
                                    @endisset
                                </fieldset>
                                <div class="col-1">
                                    <input id="hr_ini" name="hr_ini" type="hidden" value="{{ $hrIni }}">
                                    <input id="hr_fim" name="hr_fim" type="hidden" value="{{ $hrFim }}">
                                </div>
                            </div>
                            <!-- DIREITA: recorrência -->
                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-stretch gap-2">
                                    <div class="form-floating flex-grow-1" id="wrap-proc">
                                        <select id="form-select-proc" name="procedimento" class="form-select" placeholder="Procedimento">
                                            <option value="" disabled
                                                {{ old('SERVICO_CLINICA_DESC', $agenda->SERVICO_CLINICA_DESC ?? '') == '' ? 'selected' : '' }}>
                                                Selecione um procedimento…
                                            </option>
                                            @if(isset($agenda) && $agenda->ID_SERVICO)
                                            <option value="{{ old('ID_SERVICO', $agenda->ID_SERVICO ?? '') }}" selected>
                                                {{ $agenda->SERVICO_CLINICA_DESC ?? '' }}
                                            </option>
                                            @endif
                                        </select>
                                    </div>
                                    <div placeholder="Adicionar procedimento" style="margin-top: 8px;">
                                        <a href="{{ route('selectService') }}"
                                            class="btn btn-outline-primary d-flex align-items-center justify-content-center px-2">
                                            <i class="fas fa-plus" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-3">
                                <fieldset class="border rounded p-3 h-100 sticky-top" style="width: max-content;">
                                    <legend class="float-none w-auto px-2 fs-6 mb-3">Recorrência</legend>

                                    <div class="d-flex align-items-center gap-3 flex-nowrap">

                                        <div class="form-check mb-0">
                                            <input class="form-check-input" id="recorrenciapon" type="radio" name="recorrencia" value="1"
                                                {{ old('recorrencia','1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="recorrenciapon">Pontual</label>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" id="recorrenciarec" type="radio" name="recorrencia" value="2"
                                                    {{ old('recorrencia') == '2' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="recorrenciarec">Recorrência</label>
                                            </div>

                                            <select id="freq" name="freq" class="form-select form-select-sm w-auto"
                                                @disabled(old('recorrencia','1')!='2' )>
                                                @php $freqOld = old('freq','WEEKLY'); @endphp
                                                <option value="1" {{ $freqOld=='1'  ? 'selected' : '' }}>Semanal</option>
                                                <option value="2" {{ $freqOld=='2'? 'selected' : '' }}>Quinzenal</option>
                                                <option value="3" {{ $freqOld=='3' ? 'selected' : '' }}>Mensal</option>
                                            </select>
                                        </div>
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

                @php
                $pagto = old('pagto');
                if (is_null($pagto) && isset($agenda)) {
                $pagto = $agenda->VALOR_AGEND !== null ? 'S' : 'N';
                }
                $valorDisabled = ($pagto === 'N') ? 'disabled' : '';
                $valorAtual = old('valor', $agenda->VALOR_AGEND ?? '');
                $formaAtual = old('forma-pag', $agenda->FORMA_PAG ?? 'A VISTA');
                $qtdParcelasAtual = (int) old('qtd_parcelas', 0);
                @endphp

                {{-- LINHA ÚNICA: total 12 colunas no md+ (3 + 3 + 4 + 2) --}}
                <div class="row g-3 align-items-stretch">
                    <div class="row g-3 align-items-start">
                        {{-- Forma de pagamento --}}
                        <div class="row g-3">
                            <!-- Coluna 1: Haverá Pagamento + Valor (lado a lado) -->
                            <div class="col-12 col-md-5">
                                <fieldset class="border rounded p-3 h-100">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Haverá Pagamento?</legend>

                                    <div class="row g-3 align-items-end">
                                        <!-- Haverá Pagamento? -->
                                        <div class="col-12 col-sm-3">
                                            <select id="pagto" name="pagto" class="form-select" aria-describedby="help-pagto">
                                                <option value=""></option>
                                                <option value="S" {{ $pagto === 'S' ? 'selected' : '' }}>Sim</option>
                                                <option value="N" {{ $pagto === 'N' ? 'selected' : '' }}>Não</option>
                                            </select>
                                            @error('pagto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>

                                        <!-- Valor -->
                                        <div class="col-12 col-sm-5">
                                            <label for="valor" class="form-label mb-1">Valor</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input
                                                    type="text"
                                                    id="valor"
                                                    name="valor"
                                                    class="form-control"
                                                    placeholder="0,00"
                                                    inputmode="decimal"
                                                    value="{{ $valorAtual }}"
                                                    {{ $valorDisabled }}>
                                            </div>
                                            @error('valor')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <!-- Coluna 2: Forma de pagamento -->
                            <div class="col-12 col-md-4">
                                <fieldset class="border rounded p-3 h-100">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Forma de pagamento</legend>

                                    <div class="btn-group w-100 mb-2" role="group" aria-label="Forma de pagamento">
                                        <input type="radio" class="btn-check" name="forma-pag" id="fp-vista" value="A VISTA"
                                            autocomplete="off" {{ $formaAtual === 'A VISTA' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="fp-vista" style="flex:1 1 0">
                                            <i class="fa-solid fa-money-bill me-2" aria-hidden="true"></i> À vista
                                        </label>

                                        <input type="radio" class="btn-check" name="forma-pag" id="fp-parcelado" value="PARCELADO"
                                            autocomplete="off" {{ $formaAtual === 'PARCELADO' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="fp-parcelado" style="flex:1 1 0">
                                            <i class="fa-regular fa-credit-card me-2" aria-hidden="true"></i> Parcelado
                                        </label>
                                    </div>

                                    @php
                                    $qtdParcelasAtual = (int) old('qtd_parcelas', $qtdParcelasAtual ?? 0);
                                    $diaVencAtual = (int) old('dia_venc', $agenda->DIA_VENC ?? 0);
                                    @endphp

                                    <div id="wrap-parcelas" class="mt-2" style="display:none;">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-auto">
                                                <label for="qtd-parcelas" class="form-label mb-0">Parcelas</label>
                                            </div>
                                            <div class="col-auto">
                                                <select id="qtd-parcelas" name="qtd_parcelas" class="form-select form-select-sm">
                                                    @for ($i = 2; $i <= 12; $i++)
                                                        <option value="{{ $i }}" {{ $qtdParcelasAtual === $i ? 'selected' : '' }}>{{ $i }}x</option>
                                                        @endfor
                                                </select>
                                            </div>

                                            <div class="col-auto">
                                                <label for="dia-venc" class="form-label mb-0">Data venc.</label>
                                            </div>
                                            <div class="col-1">
                                                <input id="dia-venc" name="dia_venc" type="text" class="form-control form-control-sm datepicker" style="width: 9rem;">
                                            </div>
                                        </div>
                                </fieldset>
                            </div>
                            {{-- Botão + Encaminhamento + Disciplina --}}
                            <div class="col-12 col-md-3">
                                <button class="btn btn-primary btn-lg w-100 mb-2" id="btn-agendar" type="submit">
                                    <i class="bi bi-calendar-plus"></i> Agendar
                                </button>
                                @isset($agenda)
                                <input type="hidden" name="encaminhamento" value="0">
                                <div class="form-check d-flex justify-content-center align-items-center gap-2 mb-2">
                                    <input
                                        class="form-check-input m-0"
                                        id="encaminhamento"
                                        type="checkbox"
                                        name="encaminhamento"
                                        value="1"
                                        @checked(old('encaminhamento', $agenda->ENCAMINHAMENTO ?? 0) == 1)
                                    >
                                    <label class="form-check-label" for="encaminhamento">Encaminhamento</label>
                                </div>
                                @endisset
                                {{-- Disciplina (aparece se marcar encaminhamento) --}}
                                @php
                                // valores para o select
                                $valor = old('disciplina', isset($agenda) ? ($agenda->DISCIPLINA ?? '') : '');
                                $rotulo = '';
                                if ($valor !== '' && isset($agenda)) {
                                $rotulo = ($agenda->DISCIPLINA ?? '') . '-' . ($agenda->DISCIPLINA_NOME ?? '');
                                }
                                @endphp
                                <div id="wrap-encaminhamento" class="mt-2" style="display:none;">
                                    <div class="form-floating">
                                        <select id="disciplina-enc" name="disciplina-enc" class="form-select">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // --- Forma de pagamento: mostrar/esconder parcelas ---
                            const vista = document.getElementById('fp-vista');
                            const parcelado = document.getElementById('fp-parcelado');
                            const wrapParc = document.getElementById('wrap-parcelas');

                            function toggleParcelas() {
                                wrapParc.style.display = (parcelado && parcelado.checked) ? 'block' : 'none';
                            }
                            [vista, parcelado].forEach(el => el && el.addEventListener('change', toggleParcelas));
                            toggleParcelas(); // estado inicial

                            // --- Encaminhamento: mostrar/esconder disciplina ---
                            const chkEnc = document.getElementById('encaminhamento');
                            const wrapEnc = document.getElementById('wrap-encaminhamento');

                            function toggleEncaminhamento() {
                                wrapEnc.style.display = (chkEnc && chkEnc.checked) ? 'block' : 'none';
                            }
                            chkEnc && chkEnc.addEventListener('change', toggleEncaminhamento);
                            toggleEncaminhamento(); // estado inicial (respeita old())

                            // (Opcional) atualiza resumos simples
                            const qtdParcelas = document.getElementById('qtd-parcelas');
                            const parcelasResumo = document.getElementById('parcelas-resumo');
                            const diaVenc = document.getElementById('dia-venc');
                            const vencResumo = document.getElementById('venc-resumo');

                            function updateParcelasResumo() {
                                if (parcelasResumo && qtdParcelas) parcelasResumo.textContent = qtdParcelas.value ? `${qtdParcelas.value} parcelas` : '';
                            }

                            function updateVencResumo() {
                                if (vencResumo && diaVenc) vencResumo.textContent = diaVenc.value ? `Vencimento: ${diaVenc.value}` : '';
                            }
                            qtdParcelas && qtdParcelas.addEventListener('change', updateParcelasResumo);
                            diaVenc && diaVenc.addEventListener('change', updateVencResumo);
                            updateParcelasResumo();
                            updateVencResumo();
                        });
                    </script>

                    <style>
                        /* Refinos leves de layout */
                        fieldset legend {
                            margin-bottom: .25rem;
                        }

                        #wrap-encaminhamento .form-floating>label {
                            opacity: .85;
                        }
                    </style>

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
    <script>
        document.addEventListener('change', function(e) {
            if (e.target.name === 'recorrencia') {
                const freq = document.getElementById('freq');
                const isRec = e.target.value === '2';
                freq.disabled = !isRec;
            }

            if (e.target.id === 'freq') {
                const interval = document.getElementById('interval'); // opcional
                if (interval) interval.value = (e.target.value === 'BIWEEKLY') ? 2 : 1;
            }
        });
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