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
            <legend class="w-auto px-2">Vinculo de box com disciplina</legend>
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
                <div style="text-align: right;flex:0.2">
                    <button class="btn btn-primary btn-lg" id="btn-agendar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 10px; border-radius: 6px; cursor: pointer;">
                        <i class="bi bi-calendar-plus"></i> Histórico de Alterações
                    </button>
                </div>
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
                    /*display: block;*/
                }

                .field .form-select {
                    width: 100%;
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    font-size: 14px;
                }

                /* Grade de horários como botões */
                .horarios-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    /* 4 por linha; ajuste se quiser 3 */
                    gap: 6px;
                    /* espaçamento menor entre botões */
                }

                .time-check {
                    position: relative;
                }

                .time-check input[type="checkbox"] {
                    position: absolute;
                    opacity: 0;
                    pointer-events: none;
                }

                .time-check label {
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

                .time-check label:hover {
                    border-color: #bbb;
                    transform: translateY(-1px);
                }

                .time-check input[type="checkbox"]:checked+label {
                    background: #e8f3ff;
                    border-color: #8dbbfd;
                    box-shadow: inset 0 0 0 1px #8dbbfd;
                    font-weight: 600;
                }

                /* Box selector (lado direito) */
                .boxes-wrapper {
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    padding: 10px;
                    max-height: 260px;
                    overflow-y: auto;
                    background-color: #f9f9f9;
                }

                /* Responsivo */
                @media (max-width: 992px) {
                    .agendamento-grid {
                        grid-template-columns: 1fr;
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
                    $valueSel = old('data', $BoxDiscipline->DIA_SEMANA ?? '');
                    @endphp

                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div class="field" style="flex: 0.5;">
                            <label for="data">Dia de semana</label>
                            <select id="data" name="data" class="form-select">
                                @if($valueSel !== '')
                                <option value="{{ $valueSel }}" selected>
                                    {{ $dias[$valueSel] ?? 'Selecionado' }}
                                </option>
                                @endif
                            </select>
                        </div>
                    </div>
                    @php
                    // Seus horários (label) por chave
                    $dias = [
                    '1' => '7:30','2' => '8:15','3' => '9:00','4' => '9:45',
                    '5' => '10:15','6' => '11:00','7' => '11:45','8' => '12:30',
                    '9' => '13:15','10' => '14:00','11' => '14:45','12' => '15:30',
                    '13' => '16:15','14' => '17:00','15' => '17:45','16' => '18:30',
                    ];

                    // Converte "H:i" p/ minutos
                    $toMin = function (string $hi) {
                    [$h,$m] = array_map('intval', explode(':', $hi));
                    return $h * 60 + $m;
                    };

                    // Normaliza "07:30:00.0000000" -> "07:30"
                    $fmtDB = function ($t) {
                    if (!$t) return null;
                    try { return \Carbon\Carbon::parse($t)->format('H:i'); }
                    catch (\Exception $e) { return null; }
                    };

                    // Lista vinda do POST anterior (se houver erro de validação)
                    $oldDias = collect(old('dias_semana', []))->map(fn($v) => (string)$v)->all();

                    // Pré-seleção baseada no banco (só se não houver old)
                    $prechecked = [];
                    if (isset($BoxDiscipline) && empty($oldDias)) {
                    $ini = $fmtDB($BoxDiscipline->HR_INICIO); // ex: "07:30"
                    $fim = $fmtDB($BoxDiscipline->HR_FIM); // ex: "09:00"
                    if ($ini && $fim) {
                    $mi = $toMin($ini);
                    $mf = $toMin($fim);

                    foreach ($dias as $k => $lbl) {
                    // normaliza "7:30" -> "07:30" e compara
                    [$h,$m] = array_map('intval', explode(':', $lbl));
                    $tm = $h * 60 + $m;

                    // Inclui limites (07:30 e 09:00 também marcam)
                    if ($tm >= $mi && $tm <= $mf) {
                        $prechecked[]=(string)$k;
                        }
                        }
                        }
                        }
                        @endphp
                        <fieldset class="border rounded p-3" style="margin-top: 10px;">
                        <legend class="float-none w-auto px-2 fs-6 mb-2">Horários</legend>

                        @php
                        $dias = [
                        '1' => '7:30','2' => '8:15','3' => '9:00','4' => '9:45',
                        '5' => '10:15','6' => '11:00','7' => '11:45','8' => '12:30',
                        '9' => '13:15','10' => '14:00','11' => '14:45','12' => '15:30',
                        '13' => '16:15','14' => '17:00','15' => '17:45','16' => '18:30',
                        ];
                        @endphp

                        <div class="horarios-grid">
                            @foreach($dias as $val => $lbl)
                            @php
                            // Se existir old(), usa old; senão, usa a pré-seleção do banco
                            $checked = !empty($oldDias)
                            ? in_array((string)$val, $oldDias, true)
                            : in_array((string)$val, $prechecked, true);
                            @endphp
                            <div class="time-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="dia_{{ $val }}"
                                    name="dias_semana[]"
                                    value="{{ $val }}"
                                    {{ $checked ? 'checked' : '' }}>
                                {{-- data-time sem zero à esquerda para casar com o JS (ex.: "07:30" -> "7:30") --}}
                                <label class="form-check-label" for="dia_{{ $val }}" data-time="{{ ltrim($lbl, '0') }}">
                                    {{ $lbl }}
                                </label>
                            </div>
                            @endforeach
                        </div>
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

            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button id="voltar" name="voltar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Voltar
                </button>
                <button style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
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