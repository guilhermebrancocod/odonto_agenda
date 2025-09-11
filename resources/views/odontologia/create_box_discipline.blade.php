<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    @include('components.sidebar')
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Disciplinas por box</legend>
        </fieldset>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($BoxDiscipline) ? route('updateBoxDiscipline', $BoxDiscipline->ID_BOX_DISCIPLINA) : route('createBoxDiscipline') }}"
            method="POST">

            @csrf

            @if(isset($BoxDiscipline))
            @method('PUT')
            @endif
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
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
            <div class="agendamento-grid" style="margin: 20px 0;">
                <!-- COLUNA ESQUERDA: campos + horários -->
                <div>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div class="field" style="flex: 1;">
                            <label for="disciplina">Disciplina</label>
                            <select id="disciplina" name="disciplina" class="form-select">
                                <option value="" {{ old('DISCIPLINA', $agenda->DISCIPLINA ?? '') == '' ? 'selected' : '' }}></option>
                                @if(isset($BoxDiscipline))
                                <option value="{{ $BoxDiscipline->DISCIPLINA }}" selected>
                                    {{ $BoxDiscipline->DISCIPLINA ?? 'Selecionado' }}
                                </option>
                                @endif
                            </select>
                        </div>
                        <div class="field" style="flex: 1;">
                            <label for="turma">Turma</label>
                            <select id="turma" name="turma" class="form-select">
                                <option value="" {{ old('TURMA', $agenda->TURMA ?? '') == '' ? 'selected' : '' }}>
                                    Turma
                                </option>
                                @if(isset($BoxDiscipline))
                                <option value="{{ $BoxDiscipline->TURMA }}" selected>
                                    {{ $BoxDiscipline->TURMA ?? 'Selecionado' }}
                                </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    @php
                    $dias = [
                    '1' => 'Domingo',
                    '2' => 'Segunda-feira',
                    '3' => 'Terça-feira',
                    '4' => 'Quarta-feira',
                    '5' => 'Quinta-feira',
                    '6' => 'Sexta-feira',
                    '7' => 'Sábado',
                    ];

                    // prioridade: old('data') -> BoxDiscipline->DIA_SEMANA -> agenda->DIA_SEMANA
                    $diaSelecionado = (string) old('data', $BoxDiscipline->DIA_SEMANA ?? ($agenda->DIA_SEMANA ?? ''));
                    @endphp

                    <div style="display:flex; gap:15px; margin-bottom:15px;">
                        <div class="field" style="flex:0.5;">
                            <label for="data">Dia da semana</label>
                            <select id="data" name="data" class="form-select @error('data') is-invalid @enderror">
                                <option value="">Dia da semana</option>
                                @foreach ($dias as $val => $lbl)
                                <option value="{{ $val }}" @selected($diaSelecionado===(string) $val)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    @php
                    $hrIni = isset($BoxDiscipline) ? substr($BoxDiscipline->HR_INICIO, 0, 5) : old('hr_ini', '');
                    $hrFim = isset($BoxDiscipline) ? substr($BoxDiscipline->HR_FIM, 0, 5) : old('hr_fim', '');
                    @endphp
                    <fieldset class="border rounded p-3" style="margin-top:10px;">
                        <legend class="float-none w-auto px-2 fs-6 mb-2">Horários</legend>

                        <div id="horarios-grid"
                            class="horarios-grid"
                            data-disciplina="{{ $BoxDiscipline->DISCIPLINA ?? '' }}"
                            data-turma="{{ $BoxDiscipline->TURMA ?? '' }}"
                            data-dia="{{ $BoxDiscipline->DIA_SEMANA ?? '' }}"
                            data-hr-ini="{{ $hrIni }}"
                            data-hr-fim="{{ $hrFim }}"
                            {{-- se você salvar horários individuais, pode enviar também --}}
                            data-selected='@json($horariosSelecionados ?? [])'>
                        </div>

                        <input type="hidden" name="hr_ini" id="hr_ini" value="{{ $hrIni }}">
                        <input type="hidden" name="hr_fim" id="hr_fim" value="{{ $hrFim }}">

                        @isset($BoxDiscipline)
                        <div class="text-muted small mt-2">Intervalo atual: {{ $hrIni }} – {{ $hrFim }}</div>
                        @endisset
                    </fieldset>
                </div>
                <!-- COLUNA DIREITA: seleção de box -->
                <div>
                    <div class="field">
                        <label>Selecionar Box</label>
                        <div id="boxes-container" class="boxes-wrapper"></div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="{{ url('odontologia/consultardisciplinabox') }}" class="btn btn-primary" id="voltar">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    Salvar
                </button>
            </div>
        </form>
    </div>
    </form>
    </div>
    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
        }).then(() => {
            window.location.href = "{{ url('odontologia/consultardisciplinabox') }}";
        });
    </script>
    @endif
    @if (session('alert'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: "{{ session('alert') }}",
        });
    </script>
    @endif
    <script>
        const disciplinaSelecionada = @json($BoxDiscipline -> DISCIPLINA ?? '');
    </script>
    @php
    $disciplinas = old('disciplines');
    if (!$disciplinas && isset($servico)) {
    $disciplinas = DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
    ->where('ID_SERVICO_CLINICA', $servico->ID_SERVICO_CLINICA)
    ->pluck('DISCIPLINA')
    ->toArray();
    }
    @endphp
    <script>
        const disciplinasSelecionadas = @json($disciplinas);
    </script>
    @php
    $boxesSelecionados = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
    ->where('ID_BOX_DISCIPLINA', $BoxDiscipline->ID_BOX_DISCIPLINA ?? null)
    ->pluck('ID_BOX')
    ->toArray();
    @endphp

    <script>
        const boxesSelecionados = @json($boxesSelecionados);
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_box_discipline.js"></script>
</body>

</html>