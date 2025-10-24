<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Box Disciplina | FAESA</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<style>
    #assigner .assigner-layout {
        display: flex;
        gap: 2rem;
        /* Espaço entre colunas */
        flex-wrap: wrap;
        /* Responsivo para telas pequenas */
    }

    #assigner .coluna {
        flex: 1;
        min-width: 300px;
        /* Largura mínima por coluna */
    }

    .linha-com-titulo {
        margin-bottom: 1rem;
    }

    .alunos-wrapper,
    .boxes-wrapper {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 1rem;
        border-radius: 5px;
        min-height: 200px;
    }

    .boxes-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
    }

    .box-chip {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .box-chip input[type="checkbox"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .box-chip label {
        display: inline-flex;
        align-items: center;
        padding: .5rem .75rem;
        border: 1px solid #ddd;
        border-radius: 999px;
        background: #fff;
        font-size: .9rem;
        color: #333;
        transition: all .15s;
        cursor: pointer;
    }

    .box-chip input:checked+label {
        border-color: #466eff;
        background: #e9563cff;
        color: #1d2a5b;
    }

    /* Campo padrão */
    .field {
        display: flex;
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

    .alunos-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(225px, 1fr));
        gap: 20px;
    }

    .alunos-wrapper.columns {
        columns: 3 280px;
        /* até 3 colunas, min 280px */
        column-gap: 20px;
    }

    .alunos-coluna,
    .aluno-row {
        break-inside: avoid;
    }

    .aluno-nome {
        text-transform: capitalize;
    }

    .alunos-coluna {
        min-width: 200px;
        /* largura visual de uma coluna */
        display: flex;
        flex-direction: column;
        gap: 10px;

        /* snap opcional */
        scroll-snap-align: start;
    }

    .aluno-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border: 1px solid #e3e3e7;
        border-radius: 10px;
        background: #fafafa;
        transition: border-color .15s, background .15s;
    }

    .aluno-row:hover {
        background: #f5f8ff;
        border-color: #2563eb;
    }

    .aluno-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .aluno-nome {
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .aluno-meta {
        font-size: 12px;
        color: #667085;
    }

    .aluno-row:hover {
        background: #f5f8ff;
        border-color: #2563eb;
    }

    .aluno-info {
        display: flex;
        flex-direction: column;
    }

    .aluno-nome {
        font-weight: 500;
        font-size: 14px;
    }

    .aluno-meta {
        font-size: 12px;
        color: #666;
    }

    .alunos-boxes-wrapper {
        display: grid;
        gap: 14px;
        max-height: 320px;
        /* opcional: limitar altura */
        overflow: auto;
        /* scroll interno se crescer */
    }

    .pre-box {
        border: 1px solid #e3e3e7;
        border-radius: 10px;
        background: #fff;
        padding: 10px;
    }

    .pre-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }

    .pre-header .tag {
        font-weight: 300;
    }

    .pre-header .pre-count {
        margin-left: auto;
        color: #666;
        font-size: 12px;
    }

    .pre-header .pre-clear {
        border: 0;
        background: transparent;
        color: #c00;
        cursor: pointer;
        font-size: 12px;
    }

    .pre-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pre-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border: 1px solid #e3e3e7;
        border-radius: 999px;
        background: #f7f7f9;
    }

    .pre-chip .rm {
        border: 0;
        background: transparent;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
        color: #555;
    }

    .box-chip.is-active label {
        outline: 2px solid #0d6efd;
        background: #e7f1ff;
        border-radius: .5rem;
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
    @include('partials.sidebar')
    <div style="margin-left:225px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Disciplinas por box</legend>
        </fieldset>
        @php
        $boxDiscipline = $boxDiscipline ?? null;
        $isEdit = isset($boxDiscipline) && isset($boxDiscipline->regra);
        $idBox = $isEdit ? $boxDiscipline->regra->ID_BOX_DISCIPLINA : null;
        @endphp
        <form
            id="form-agenda"
            action="{{ $isEdit
                ? route('updateBoxDiscipline', ['idBoxDiscipline' => $idBox])
                : route('createBoxDiscipline') }}"
            method="POST">
            @csrf
            @if ($isEdit)
            @method('PUT')
            @endif
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="agendamento-grid" style="margin: 20px 0;">
                <!-- COLUNA ESQUERDA: campos + horários -->
                <div>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div class="field" style="flex: 1;">
                            @php
                            $selectedDisc = old('disciplina', $boxDiscipline->disciplina ?? ($agenda->DISCIPLINA ?? ''));
                            $selectedNome = $boxDiscipline->disciplina_nome ?? ($agenda->DISCIPLINA_NOME ?? null);
                            @endphp

                            <select id="disciplina" name="disciplina" class="form-select">
                                <option value="" {{ $selectedDisc === '' ? 'selected' : '' }} disabled>Selecione a disciplina</option>

                                @if(!empty($disciplinas))
                                @foreach($disciplinas as $d)
                                @php
                                $codigo = $d->DISCIPLINA ?? $d['DISCIPLINA'] ?? '';
                                $nome = $d->NOME ?? $d['NOME'] ?? '';
                                @endphp
                                @if($codigo !== '')
                                <option value="{{ $codigo }}" {{ (string)$codigo === (string)$selectedDisc ? 'selected' : '' }}>
                                    {{ $codigo }} @if($nome) — {{ $nome }} @endif
                                </option>
                                @endif
                                @endforeach
                                @else
                                {{-- Fallback quando não há lista completa --}}
                                @if($selectedDisc !== '')
                                <option value="{{ $selectedDisc }}" selected>
                                    {{ $selectedDisc }} @if($selectedNome) — {{ $selectedNome }} @endif
                                </option>
                                @endif
                                @endif
                            </select>

                        </div>
                        @php
                        // Ordem de precedência: old() > boxDiscipline > agenda
                        $selectedTurma = old('turma', $boxDiscipline->turma ?? ($agenda->TURMA ?? ''));
                        @endphp
                        <div class="field" style="flex: 0.5;">
                            <select id="turma" name="turma" class="form-select">
                                <option value="" {{ $selectedTurma === '' ? 'selected' : '' }} disabled>Selecione a turma</option>

                                @forelse(($turmas ?? []) as $turma)
                                <option value="{{ $turma }}" {{ (string)$turma === (string)$selectedTurma ? 'selected' : '' }}>
                                    {{ $turma }}
                                </option>
                                @empty
                                {{-- Se não veio lista de turmas, garante a turma selecionada atual na edição --}}
                                @if($selectedTurma !== '')
                                <option value="{{ $selectedTurma }}" selected>{{ $selectedTurma }}</option>
                                @endif
                                @endforelse
                            </select>
                        </div>
                        @php
                        $diasSemana = [
                        '1' => 'Domingo',
                        '2' => 'Segunda-feira',
                        '3' => 'Terça-feira',
                        '4' => 'Quarta-feira',
                        '5' => 'Quinta-feira',
                        '6' => 'Sexta-feira',
                        '7' => 'Sábado',
                        ];

                        // Prioridade: old('data') > $boxDiscipline->dia_semana > $agenda->DIA_SEMANA
                        $selectedDiaSemana = (string) old('data', $boxDiscipline->dia_semana ?? ($agenda->DIA_SEMANA ?? ''));
                        @endphp

                        <div class="field" style="flex:0.4;">
                            <select id="data" name="data" class="form-select @error('data') is-invalid @enderror">
                                <option value="" {{ $selectedDiaSemana === '' ? 'selected' : '' }} disabled>Dia da semana</option>
                                @foreach ($diasSemana as $val => $lbl)
                                <option value="{{ $val }}" {{ (string)$val === $selectedDiaSemana ? 'selected' : '' }}>
                                    {{ $lbl }}
                                </option>
                                @endforeach
                            </select>
                            @error('data')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @php
                    // pega de old() > boxDiscipline > agenda
                    $hrIniRaw = old('hrIni', $boxDiscipline->hrIni ?? ($agenda->HR_INI ?? $agenda->HR_INICIO ?? ''));
                    $hrFimRaw = old('hrFim', $boxDiscipline->hrFim ?? ($agenda->HR_FIM ?? ''));
                    // normaliza para HH:MM (corta segundos se vier HH:MM:SS)
                    $hrIni = $hrIniRaw ? substr($hrIniRaw, 0, 5) : '';
                    $hrFim = $hrFimRaw ? substr($hrFimRaw, 0, 5) : '';
                    @endphp
                    <div class="d-flex flex-column">

                        <div class="d-flex gap-3 mb-3">
                            <div class="flex-grow-1">
                                <fieldset class="border rounded p-3 mt-2">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Horários</legend>

                                    <div id="horarios-grid"
                                        class="horarios-grid"
                                        data-disciplina="{{ $boxDiscipline->disciplina ?? ($agenda->DISCIPLINA ?? '') }}"
                                        data-turma="{{ $boxDiscipline->turma ?? ($agenda->TURMA ?? '') }}"
                                        data-dia="{{ old('data', $boxDiscipline->dia_semana ?? ($agenda->DIA_SEMANA ?? '')) }}"
                                        data-hr-ini="{{ $hrIni }}"
                                        data-hr-fim="{{ $hrFim }}"
                                        data-selected='@json($horariosSelecionados ?? [])'>
                                    </div>

                                    <input type="hidden" id="hr_ini" name="hr_ini" value="{{ $hrIni }}">
                                    <input type="hidden" id="hr_fim" name="hr_fim" value="{{ $hrFim }}">

                                    @if($hrIni || $hrFim)
                                    <div class="text-muted small mt-2">
                                        Intervalo atual: {{ $hrIni ?: '—' }} – {{ $hrFim ?: '—' }}
                                    </div>
                                    @endif
                                </fieldset>
                            </div>
                        </div>
                        @if ($isEdit)
                        <div class="d-flex justify-content-end mt-3">
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-log"
                                data-update-url="{{ route('box.troca.update', ['id' => $idBox]) }}"
                                data-box-disciplina-id="{{ $idBox }}">
                                Trocar box
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="container" id="assigner">
                        <div class="assigner-layout">
                            <!-- Coluna de Alunos -->
                            @if(is_null($boxDiscipline))
                            <div class="coluna">
                                <div class="linha-com-titulo">
                                    <h5>Selecionar os alunos</h5>
                                </div>
                                <div class="alunos-bloco">
                                    <div id="alunos-container" class="alunos-wrapper">
                                        <p>Selecione disciplina, turma e dia da semana para visualizar os alunos.</p>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="coluna">
                                <div class="linha-com-titulo">
                                    <h5>Selecionar os alunos</h5>
                                </div>
                                <div class="alunos-bloco">
                                    <div id="alunos-container" class="alunos-wrapper">
                                        <p>Selecione disciplina, turma e dia da semana para visualizar os alunos.</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @php
                            // Busca os boxes vinculados ao ID_BOX_DISCIPLINA atual
                            $boxesSelecionados = \Illuminate\Support\Facades\DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                            ->where('ID_BOX_DISCIPLINA', $boxDiscipline->regra->ID_BOX_DISCIPLINA ?? null)
                            ->pluck('ID_BOX')
                            ->toArray();
                            @endphp
                            <script>
                                window.boxesSelecionados = @json($boxesSelecionados);
                            </script>
                            @php
                            // Ajuste os campos conforme seu modelo (precisa ter ID_BOX e ALUNO)
                            $alocacoesIniciais = collect($boxDiscipline->alunos ?? [])
                            ->groupBy('ID_BOX')
                            ->map(function ($g) {
                            return $g->pluck('ALUNO')
                            ->map(fn ($v) => (string) $v) // <- aqui
                                ->values();
                                })
                                ->toArray();
                                @endphp

                                <script>
                                    window.alocacoesIniciais = @json($alocacoesIniciais);
                                </script>

                                <script>
                                    window.isEdit = @json($isEdit);
                                    window.initialForm = {
                                        disciplina: @json($selectedDisc ?? ''),
                                        turma: @json($selectedTurma ?? ''),
                                        dia: @json($selectedDiaSemana ?? ''),
                                    };
                                </script>
                                <!-- Coluna de Box -->
                                <div class="coluna">
                                    <div class="linha-com-titulo">
                                        <h5>Selecionar o box</h5>
                                    </div>
                                    <div class="field">
                                        <div id="boxes-container" class="boxes-wrapper">
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <div class="linha-com-titulo">
                        <h5>Pré-Seleção de alunos e box</h5>
                        <div class="linha-flex"></div>
                    </div>
                    <div>
                        @if(is_null($boxDiscipline))
                        <div class="field">
                            <div id="alunos-boxes-container" class="alunos-boxes-wrapper">
                                <p>Faça a pré-seleção de alunos e box.</p>
                            </div>
                        </div>
                        @else
                        <div class="field">
                            <div id="alunos-boxes-container" class="alunos-boxes-wrapper">
                                <fieldset class="border rounded p-3">
                                    <legend class="float-none w-auto px-2 fs-6 mb-2">Alunos</legend>
                                    <!-- container onde o JS vai injetar os checkboxes -->
                                    <div id="alunos-container"
                                        class="d-flex flex-wrap gap-2"
                                        aria-live="polite"
                                        data-disc="{{ $boxDiscipline->disciplina }}"
                                        data-turma="{{ $boxDiscipline->turma }}"
                                        data-box="{{ $boxDiscipline->box_id }}">
                                        <span class="text-muted">Carregando…</span>
                                    </div>

                                    <!-- campo oculto usado pelo JS pra enviar os alunos -->
                                    <div id="alunos-ids-eco">
                                        @foreach($boxDiscipline->alunosIds as $id)
                                        <input type="hidden" name="alunos[]" value="{{ $id }}">
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="{{ url('odontologia/consultardisciplinabox') }}" class="btn btn-primary" id="voltar">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ $isEdit ? 'Atualizar' : 'Salvar' }}
                </button>
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
    <div id="trocaBoxPopup" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.25);align-items:center;justify-content:center;">
        <div style="background:#fff;border:1px solid #ddd;border-radius:10px;padding:14px;min-width:280px;box-shadow:0 10px 30px rgba(0,0,0,.15);">
            <div style="font-weight:600;margin-bottom:8px">Selecione o novo box</div>
            <select id="trocaBoxSelect" style="width:100%;padding:.4rem;border:1px solid #ccc;border-radius:6px" disabled>
                <option>Carregando...</option>
            </select>
            <div style="margin-top:10px;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" id="trocaBoxCancel" style="padding:.4rem .7rem;border:1px solid #ccc;border-radius:6px;background:#f8f9fa">Cancelar</button>
                <button type="button" id="trocaBoxConfirm" style="padding:.4rem .7rem;border:1px solid #0d6efd;border-radius:6px;background:#0d6efd;color:#fff" disabled>Confirmar</button>
            </div>
            <div id="trocaBoxMsg" style="margin-top:8px;font-size:.875rem;color:#6c757d;display:none"></div>
        </div>
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function() {
            const LIST_URL = @json(route('box.troca')); // GET que retorna os boxes [{id, nome}]
            const popup = document.getElementById('trocaBoxPopup');
            const selectEl = document.getElementById('trocaBoxSelect');
            const confirmBtn = document.getElementById('trocaBoxConfirm');
            const cancelBtn = document.getElementById('trocaBoxCancel');
            const msgEl = document.getElementById('trocaBoxMsg');

            let currentUpdateUrl = null;
            let currentBoxDisciplinaId = null;

            // Abre popup quando clicar no botão .btn-log
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('.btn-log');
                if (!btn) return;

                currentUpdateUrl = btn.dataset.updateUrl || null;
                currentBoxDisciplinaId = btn.dataset.boxDisciplinaId || null;

                resetPopup();
                showPopup(true);

                try {
                    const res = await fetch(LIST_URL, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const boxes = await res.json();
                    renderOptions(boxes);
                    selectEl.disabled = false;
                } catch (err) {
                    showMsg('Erro ao carregar boxes: ' + err.message, true);
                    renderOptions([]);
                }
            });

            // Habilita confirmar quando selecionar
            selectEl.addEventListener('change', () => {
                confirmBtn.disabled = !selectEl.value;
            });

            // Confirmar envio
            confirmBtn.addEventListener('click', async () => {
                if (!selectEl.value) return;
                if (!currentUpdateUrl) return showMsg('URL de atualização não definida.', true);

                setLoading(true);
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const res = await fetch(currentUpdateUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            novo_box_id: selectEl.value,
                            box_disciplina_id: currentBoxDisciplinaId
                        })
                    });
                    if (!res.ok) {
                        let msg = `HTTP ${res.status}`;
                        try {
                            const j = await res.json();
                            if (j?.message) msg = j.message;
                        } catch {}
                        throw new Error(msg);
                    }
                    showMsg('Box atualizado com sucesso!', false);
                    setTimeout(() => location.reload(), 500);
                } catch (err) {
                    showMsg('Falha ao atualizar: ' + err.message, true);
                } finally {
                    setLoading(false);
                }
            });

            // Cancelar
            cancelBtn.addEventListener('click', () => showPopup(false));
            // Fecha ao clicar fora da caixinha
            popup.addEventListener('click', (e) => {
                if (e.target === popup) showPopup(false);
            });
            // Esc fecha
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') showPopup(false);
            });

            // Helpers
            function showPopup(visible) {
                popup.style.display = visible ? 'flex' : 'none';
            }

            function resetPopup() {
                msgEl.style.display = 'none';
                msgEl.textContent = '';
                msgEl.style.color = '#6c757d';
                selectEl.innerHTML = '<option>Carregando...</option>';
                selectEl.disabled = true;
                confirmBtn.disabled = true;
                confirmBtn.textContent = 'Confirmar';
            }

            function renderOptions(items) {
                selectEl.innerHTML = '';
                if (!items?.length) {
                    selectEl.innerHTML = '<option value="">Nenhum box disponível</option>';
                    confirmBtn.disabled = true;
                    return;
                }
                selectEl.insertAdjacentHTML('beforeend', '<option value="">Selecione...</option>');
                items.forEach(it => {
                    const id = it.id ?? it.ID_BOX_CLINICA ?? '';
                    const nome = it.nome ?? it.DESCRICAO ?? `Box ${id}`;
                    if (!id) return;
                    selectEl.insertAdjacentHTML('beforeend', `<option value="${id}">${nome}</option>`);
                });
            }

            function showMsg(text, error) {
                msgEl.style.display = 'block';
                msgEl.textContent = text;
                msgEl.style.color = error ? '#dc3545' : '#198754';
            }

            function setLoading(is) {
                selectEl.disabled = is;
                confirmBtn.disabled = is || !selectEl.value;
                confirmBtn.textContent = is ? 'Salvando...' : 'Confirmar';
            }
        })();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_box_discipline.js"></script>
</body>

</html>