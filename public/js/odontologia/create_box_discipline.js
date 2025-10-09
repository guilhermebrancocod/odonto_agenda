// == Agenda / Alocação de Alunos por Box ============================
document.addEventListener('DOMContentLoaded', init);

function init() {
    'use strict';

    // ---- Constantes e Estado ---------------------------------------
    const CAP = 2; // limite por box
    const boxesSelecionados = Array.isArray(window.boxesSelecionados) ? window.boxesSelecionados : [];
    const state = {
        selected: new Set(),          // ids marcados (checkboxes)
        activeBox: null,              // box em foco
        boxes: new Map(),             // boxId -> Set<alunoId>
        alunos: new Map(),            // alunoId -> {id, nome}
    };

    // ---- Helpers ----------------------------------------------------
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const warn = (msg) => (window.toastr?.warning ? toastr.warning(msg) : alert(msg));
    const pad2 = (n) => String(n).padStart(2, '0');
    const toMin = (hhmm) => { const [h, m] = String(hhmm).split(':').map(Number); return h * 60 + m; };
    const normTime = (hhmm) => {
        if (!hhmm) return '';
        const [h, m] = String(hhmm).split(':').map(Number);
        return `${pad2(h)}:${pad2(m)}`;
    };

    const isAssigned = (id) => {
        for (const set of state.boxes.values()) if (set.has(id)) return true;
        return false;
    };

    // ---- Cache de elementos ----------------------------------------
    const el = {
        disciplina: $('#disciplina'),
        turma: $('#turma'),
        data: $('#data'),
        grid: $('#horarios-grid'),
        hrIni: $('#hrIni'),
        hrFim: $('#hrFim'),
        alunosWrap: $('#alunos-container'),
        boxesWrap: $('#boxes-container'),
        preWrap: $('#pre-container'),
        alunosBoxesWrap: $('#alunos-boxes-container'),
        form: $('#form-agenda')
    };

    // =================================================================
    // Carregamento básico (disciplinas / turmas / datas / horários)
    // =================================================================
    loadDisciplinas();

    el.disciplina?.addEventListener('change', onDisciplinaChange);
    el.turma?.addEventListener('change', onTurmaChange);
    el.data?.addEventListener('change', onDataChange);

    async function loadDisciplinas() {
        try {
            const r = await fetch('/odontologia/disciplinas', { headers: { 'Accept': 'application/json' } });
            const items = await r.json();

            const disciplinaSave = el.disciplina?.value;
            if (!disciplinaSave) {
                el.disciplina.innerHTML = '<option value="">Selecione a disciplina</option>';
                items.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.DISCIPLINA;
                    opt.textContent = `${item.NOME} (${item.DISCIPLINA})`;
                    el.disciplina.appendChild(opt);
                });
            } else {
                el.disciplina.value = disciplinaSave;
            }
        } catch (e) {
            console.error('Erro ao carregar disciplinas:', e);
        }
    }

    async function onDisciplinaChange() {
        resetTurma();
        resetData();
        clearHorarios();

        const disc = el.disciplina?.value;
        if (!disc) return;

        try {
            const r = await fetch(`/odontologia/turma/${encodeURIComponent(disc)}`, { headers: { 'Accept': 'application/json' } });
            const items = await r.json();

            el.turma.disabled = false;
            el.turma.innerHTML = '<option value="">Selecione a turma</option>';
            (items || []).forEach(t => {
                const opt = new Option(t, t);
                el.turma.appendChild(opt);
            });
        } catch (e) {
            el.turma.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            console.error(e);
        }
    }

    async function onTurmaChange() {
        resetData();
        clearHorarios();

        const disc = el.disciplina?.value;
        const tur = el.turma?.value;
        if (!disc || !tur) if (disc && tur) {
            loadAlunosGrid(disc, tur); // >>> FIX: busca alunos já aqui
        };

        try {
            el.data.disabled = true;
            el.data.innerHTML = '<option value="">Carregando datas...</option>';

            const r = await fetch(`/odontologia/datas/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}`, { headers: { 'Accept': 'application/json' } });
            const items = await r.json();

            const diasPT = { '1': 'domingo', '2': 'segunda-feira', '3': 'terça-feira', '4': 'quarta-feira', '5': 'quinta-feira', '6': 'sexta-feira', '7': 'sábado' };
            el.data.innerHTML = '<option value="">Selecione a data</option>';

            (items || []).forEach(d => {
                const code = (typeof d === 'object') ? (d.DIA_SEMANA ?? d.data ?? d) : d;
                el.data.appendChild(new Option(diasPT[code] || code, code));
            });

            el.data.disabled = false;
        } catch (e) {
            el.data.innerHTML = '<option value="">Erro ao carregar datas</option>';
            console.error(e);
        }
    }

    async function onDataChange() {
        clearHorarios();

        const disc = el.disciplina?.value;
        const tur = el.turma?.value;
        const dia = el.data?.value;
        if (!disc || !tur || !dia) return;

        try {
            const r = await fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}/${encodeURIComponent(dia)}`, { headers: { 'Accept': 'application/json' } });
            const items = await r.json();
            renderHorarios(items);
        } catch (e) {
            clearHorarios('Erro ao carregar horários.');
        }

        // alunos da turma (colunas)
        loadAlunosGrid(disc, tur);
    }

    // no final do init()
    bootstrapEdit();

    function bootstrapEdit() {
        const disc = el.disciplina?.value;
        const tur = el.turma?.value;
        const dia = el.data?.value;

        // carrega alunos mesmo sem mexer nos selects
        if (disc && tur) {
            // Pega todos os alunos já alocados em qualquer box
            const alunosMarcados = Object.values(window.alocacoesIniciais || {})
                .flat()
                .map(String);

            loadAlunosGrid(disc, tur, alunosMarcados);
        }

        // se já vier dia, renderiza horários também
        if (disc && tur && dia) onDataChange();
    }

    // =================================================================
    // Horários
    // =================================================================
    function clearHorarios(msg = 'Selecione disciplina, turma e dia para ver horários.') {
        if (!el.grid) return;
        el.grid.innerHTML = `<div class="text-muted small">${msg}</div>`;
        if (el.hrIni) el.hrIni.value = '';
        if (el.hrFim) el.hrFim.value = '';
    }

    function renderHorarios(items) {
        const set = new Set();
        (items || []).forEach(it => {
            const i = normTime(it.hrIni);
            const f = normTime(it.hrFim);
            if (i) set.add(i);
            if (f) set.add(f);
        });
        const horarios = Array.from(set).sort((a, b) => toMin(a) - toMin(b));

        if (!horarios.length) {
            clearHorarios('Sem horários disponíveis.');
            return;
        }

        el.grid.innerHTML = '';
        horarios.forEach((h, idx) => {
            const id = `hor_${h.replace(':', '')}_${idx}`;
              const checked = horarios.includes(h) ? 'checked' : '';
            el.grid.insertAdjacentHTML('beforeend', `
      <div class="time-item">
        <input class="time-input" type="checkbox" id="${id}" name="horarios[]" value="${h}" ${checked}>
        <label class="time-card" for="${id}">${h}</label>
      </div>
    `);
        });

        // >>> NOVO: aplica pré-seleção do banco
        applyPreSelection();

        // Atualiza hr_ini/hr_fim quando o usuário mexer
        el.grid.addEventListener('change', handleHorarioChange);
    }

    function applyPreSelection() {
        // 1) tenta pelos hiddens; 2) fallback para data-* do grid
        const ini = (el.hrIni?.value || el.grid.dataset.hrIni || '').slice(0, 5);
        const fim = (el.hrFim?.value || el.grid.dataset.hrFim || '').slice(0, 5);

        // Se você usa data-selected com array de horários exatos, preferir isso:
        let selected = [];
        try { selected = JSON.parse(el.grid.dataset.selected || '[]'); } catch { }

        const inputs = Array.from(el.grid.querySelectorAll('input[name="horarios[]"]'));
        const mark = (val) => {
            const n = normTime(val);
            const input = inputs.find(i => i.value === n);
            if (input) input.checked = true;
        };

        if (selected.length) {
            selected.forEach(mark);
        } else if (ini && fim) {
            const min = toMin(normTime(ini));
            const max = toMin(normTime(fim));
            inputs.forEach(i => {
                const m = toMin(i.value);
                i.checked = (m >= min && m <= max);
            });
        }

        // garante que os hiddens fiquem preenchidos
        handleHorarioChange();
    }

    function handleHorarioChange() {
        const marcados = $$('input[name="horarios-grid[]"]:checked', el.grid).map(i => i.value).sort((a, b) => toMin(a) - toMin(b));
        if (el.hrIni) el.hrIni.value = marcados[0] || '';
        if (el.hrFim) el.hrFim.value = marcados[marcados.length - 1] || '';
    }

    // =================================================================
    // Alunos (lista em colunas com checkbox)
    // =================================================================

    function renderAlunosSelectionState() {
        $$('#alunos-container input[name="alunos[]"]').forEach(cb => {
            const id = cb.value;
            const row = cb.closest('.aluno-row');
            row?.classList.toggle('is-assigned', isAssigned(id));
        });
    }
    async function loadAlunosGrid(disc, tur, alunosSelecionados = [], limitePorColuna = 10) {
        if (!el.alunosWrap) return;
        try {
            el.alunosWrap.classList.add('alunos-wrapper');
            el.alunosWrap.innerHTML = '<p class="muted">Carregando…</p>';

            const r = await fetch(`/odontologia/alunos/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}`, { headers: { 'Accept': 'application/json' } });
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            const data = await r.json();
            renderAlunosEmTabela(data, alunosSelecionados, limitePorColuna);
            bindAlunosChangeOnce();
        } catch (e) {
            el.alunosWrap.innerHTML = `<p class="error">Erro ao carregar alunos. ${e.message || ''}</p>`;
        }
    }

    function renderAlunosEmTabela(data, alunosSelecionados = [], limitePorColuna = 10) {
        const container = el.alunosWrap;
        if (!container) return;
        container.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p class="muted">Nenhum aluno encontrado.</p>';
            return;
        }

        // cache nomes
        data.forEach(it => {
            const id = String(it.ALUNO ?? '').trim();
            const nome = String(it.NOME_COMPL ?? '').trim();
            if (id) state.alunos.set(id, { id, nome });
        });

        const selecionadosStr = alunosSelecionados.map(String);
        const frag = document.createDocumentFragment();

        for (let i = 0; i < data.length; i += limitePorColuna) {
            const col = document.createElement('div');
            col.className = 'alunos-coluna';

            data.slice(i, i + limitePorColuna).forEach(item => {
                const alunoId = String(item.ALUNO ?? '').trim();
                const nome = String(item.NOME_COMPL ?? '').trim();
                const isSelected = selecionadosStr.includes(alunoId);
                const already = isAssigned(alunoId);

                const row = document.createElement('label');
                row.className = `aluno-row${already ? ' is-assigned' : ''}`;
                row.dataset.search = `${alunoId} ${nome}`.toLowerCase();

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'alunos[]';
                input.value = alunoId;
                input.id = `aluno_${alunoId}`;
                input.checked = isSelected;
                input.dataset.nome = nome;
                input.setAttribute('aria-label', `Selecionar ${nome} (${alunoId})`);

                const info = document.createElement('div');
                info.className = 'aluno-info';
                info.innerHTML = `<span class="aluno-nome" title="${nome}">${nome || '(Sem nome)'}</span>`;

                row.appendChild(input);
                row.appendChild(info);
                col.appendChild(row);
            });

            frag.appendChild(col);
        }

        container.appendChild(frag);
        renderAlunosSelectionState();
    }

    function bindAlunosChangeOnce() {
        if (!el.alunosWrap || el.alunosWrap._bound) return;
        el.alunosWrap._bound = true;

        el.alunosWrap.addEventListener('change', (e) => {
            if (!e.target.matches('input[name="alunos[]"]')) return;
            const id = String(e.target.value);
            const nome = e.target.dataset.nome || state.alunos.get(id)?.nome || id;
            state.alunos.set(id, { id, nome });

            if (e.target.checked) state.selected.add(id);
            else state.selected.delete(id);

            renderAlunosSelectionState();
        });
    }

    // =================================================================
    // Boxes: carregar, clicar, destacar e alocar
    // =================================================================

    async function loadBoxes() {
        const r = await fetch('/odontologia/boxes', { headers: { 'Accept': 'application/json' } });
        const data = await r.json();

        const c = el.boxesWrap;
        c.innerHTML = '';
        const pre = (Array.isArray(window.boxesSelecionados) ? window.boxesSelecionados : []).map(String);

        data.forEach(item => {
            const wrap = document.createElement('div');
            wrap.className = 'box-chip';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = 'boxes[]';
            input.value = item.ID_BOX_CLINICA;
            input.id = `box_${String(item.ID_BOX_CLINICA).replace(/\s+/g, '_')}`;
            input.checked = pre.includes(String(item.ID_BOX_CLINICA));
            if ((item.DISPONIVEL ?? true) !== true) input.disabled = true;

            const label = document.createElement('label');
            label.setAttribute('for', input.id);
            label.title = input.disabled ? 'Indisponível' : 'Disponível';
            label.appendChild(document.createTextNode(item.DESCRICAO));

            wrap.appendChild(input);
            wrap.appendChild(label);
            c.appendChild(wrap);

            // >>> FIX: pinta (classe) e define o activeBox inicial
            if (input.checked) {
                wrap.classList.add('is-active');
                if (!state.activeBox) state.activeBox = input.value;
            }
        });

        // >>> FIX: garante highlight consistente
        if (state.activeBox) efeitoBoxAtivo(state.activeBox);

        if (!el.boxesWrap._bound) {
            el.boxesWrap._bound = true;
            el.boxesWrap.addEventListener('click', onBoxClick);
        }
    }

    function seedFromServer() {
        const init = window.alocacoesIniciais || {};
        console.log(init);
        let firstBox = null;

        Object.entries(init).forEach(([boxId, arr]) => {
            const set = new Set((arr || []).map(String));
            if (set.size) {
                state.boxes.set(String(boxId), set);
                if (!firstBox) firstBox = String(boxId);
            }
        });

        if (firstBox && !state.activeBox) state.activeBox = firstBox;

        // pinta visualmente e sincroniza UI
        if (state.activeBox) efeitoBoxAtivo(state.activeBox);
        renderPreSelecao();
        renderAlunosBoxes();
        renderAlunosSelectionState(); // quando a grade de alunos carregar, isso marca “is-assigned”
    }

    loadBoxes();
    seedFromServer();

    function onBoxClick(e) {
        const chip = e.target.closest('.box-chip');
        if (!chip) return;

        const input = chip.querySelector('input[name="boxes[]"]');
        if (!input) return;

        if (!state.selected || state.selected.size === 0) {
            e.preventDefault();
            input.checked = false;
            warn('Necessário selecionar ao menos 1 aluno para selecionar o box.');
            return;
        }

        state.activeBox = input.value;
        efeitoBoxAtivo(state.activeBox);
        assignSelectedToActive();
    }

    function efeitoBoxAtivo(boxId) {
        $$('#boxes-container .box-chip').forEach(chip => {
            const val = chip.querySelector('input[name="boxes[]"]')?.value;
            chip.classList.toggle('is-active', val === boxId);
        });
    }

    function assignSelectedToActive() {
        const boxId = state.activeBox;
        if (!boxId || !state.selected || state.selected.size === 0) return;
        assignSelectedTo(boxId);
        renderPreSelecao();
        renderAlunosBoxes();
        renderAlunosSelectionState();
    }

    function assignSelectedTo(boxId) {
        const selected = [...(state.selected ?? new Set())];
        if (selected.length === 0) {
            warn('Necessário selecionar ao menos 1 aluno para selecionar o box.');
            return;
        }

        const bucket = state.boxes.get(boxId) ?? new Set();
        let free = CAP - bucket.size;
        if (free <= 0) { warn('Box atingiu o limite de alunos'); return; }

        // garante exclusividade do aluno em 1 box
        for (const id of selected) {
            for (const [bId, set] of state.boxes) if (bId !== boxId) set.delete(id);
        }

        let added = 0;
        for (const id of selected) {
            if (free <= 0) break;
            bucket.add(id);
            state.selected.delete(id);
            free--; added++;
        }
        state.boxes.set(boxId, bucket);

        if (added < selected.length) warn('Box atingiu o limite de alunos');
    }

    function unassign(boxId, alunoId) {
        const set = state.boxes.get(boxId);
        if (!set) return;
        set.delete(alunoId);
        if (set.size === 0) state.boxes.delete(boxId);
        renderPreSelecao();
        renderAlunosBoxes();
        renderAlunosSelectionState();
    }

    // =================================================================
    // Pré-visualização e lista final por box
    // =================================================================
    function renderPreSelecao() {
        const wrap = el.preWrap;
        if (!wrap) return;

        wrap.innerHTML = '';
        const entries = [...state.boxes.entries()].sort((a, b) => a[0].localeCompare(b[0], 'pt-BR'));

        for (const [boxId, set] of entries) {
            const box = document.createElement('div'); box.className = 'pre-box';
            const head = document.createElement('div'); head.className = 'pre-header';
            head.innerHTML = `<span class="tag">Box ${boxId}</span><span class="pre-count">${set.size}/${CAP}</span>`;

            const list = document.createElement('div'); list.className = 'pre-list';
            for (const id of set) {
                const nome = state.alunos.get(id)?.nome || id;
                const chip = document.createElement('div'); chip.className = 'pre-chip';
                chip.innerHTML = `<span>${nome}</span><button type="button" class="rm" data-rm="${boxId}|${id}">×</button>`;
                list.appendChild(chip);
            }

            box.appendChild(head);
            box.appendChild(list);
            wrap.appendChild(box);
        }
    }

    function renderAlunosBoxes() {
        const wrap = el.alunosBoxesWrap;
        if (!wrap) return;

        wrap.innerHTML = '';
        if (state.boxes.size === 0) {
            wrap.innerHTML = '<p class="muted">Faça a pré-seleção de alunos e box.</p>';
            return;
        }

        const frag = document.createDocumentFragment();
        const entries = [...state.boxes.entries()].sort((a, b) => a[0].localeCompare(b[0], 'pt-BR'));

        for (const [boxId, set] of entries) {
            const box = document.createElement('div');
            box.className = 'pre-box';

            const head = document.createElement('div');
            head.className = 'pre-header';
            head.innerHTML = `
        <span class="tag">Box ${boxId}</span>
        <span class="pre-count">${set.size}/${CAP}</span>
        <button type="button" class="pre-clear" data-clear="${boxId}" title="Limpar este box">Limpar</button>
      `;

            const list = document.createElement('div');
            list.className = 'pre-list';

            for (const id of set) {
                const nome = state.alunos.get(id)?.nome || id;
                const chip = document.createElement('div');
                chip.className = 'pre-chip';
                chip.innerHTML = `
          <span class="nome" title="${nome}">${nome}</span>
          <button type="button" class="rm" data-rm="${boxId}|${id}" aria-label="Remover ${nome} do ${boxId}">×</button>
        `;
                list.appendChild(chip);
            }

            box.appendChild(head);
            box.appendChild(list);
            frag.appendChild(box);
        }

        wrap.appendChild(frag);
    }

    // Delegação para remover aluno ou limpar box (registra 1x)
    if (el.alunosBoxesWrap && !el.alunosBoxesWrap._bound) {
        el.alunosBoxesWrap._bound = true;
        el.alunosBoxesWrap.addEventListener('click', (e) => {
            const rmBtn = e.target.closest('button.rm');
            if (rmBtn) {
                const [boxId, alunoId] = rmBtn.dataset.rm.split('|');
                unassign(boxId, alunoId);
                return;
            }
            const clearBtn = e.target.closest('button.pre-clear');
            if (clearBtn) {
                const boxId = clearBtn.dataset.clear;
                const set = state.boxes.get(boxId);
                if (set) set.clear();
                state.boxes.delete(boxId);
                renderPreSelecao();
                renderAlunosBoxes();
                renderAlunosSelectionState();
            }
        });
    }

    // =================================================================
    // Submit: serializa alocações -> inputs hidden
    // =================================================================
    el.form?.addEventListener('submit', () => {

        if (el.hrIni && !el.hrIni.value && el.grid?.dataset.hrIni) el.hrIni.value = el.grid.dataset.hrIni;
        if (el.hrFim && !el.hrFim.value && el.grid?.dataset.hrFim) el.hrFim.value = el.grid.dataset.hrFim;
        // remove só os que o JS criou
        el.form.querySelectorAll('.js-auto-aloc').forEach(n => n.remove());

        state.boxes.forEach((setAlunos, boxId) => {
            setAlunos.forEach(alunoId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `alocacoes[${boxId}][]`;
                input.value = String(alunoId);
                input.className = 'js-auto-aloc';
                el.form.appendChild(input);
            });
        });
    });

    // =================================================================
    // Resets auxiliares
    // =================================================================
    function resetTurma() {
        if (!el.turma) return;
        el.turma.disabled = true;
        el.turma.innerHTML = '<option value="">Selecione uma disciplina primeiro</option>';
    }

    function resetData() {
        if (!el.data) return;
        el.data.disabled = true;
        el.data.innerHTML = '<option value="">Selecione uma turma primeiro</option>';
    }
}