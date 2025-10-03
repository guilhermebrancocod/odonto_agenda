const add = document.getElementById('add');

document.addEventListener('DOMContentLoaded', function () {

    const disciplina = document.getElementById('disciplina');
    const disciplinaSave = disciplina.value;
    const turma = document.getElementById('turma');
    const data = document.getElementById('data');

    const CAP = 2; // Aqui limito o número de alunos no box
    const state = {
        selected: new Set(),                // ids marcados na lista
        activeBox: null,                    // 'BOX 3', p.ex.
        boxes: new Map(),                   // boxId -> Set<alunoId>
        alunos: new Map(),                  // alunoId -> {id, nome}
    };

    // aluno já alocado em algum box?
    function isAssigned(id) {
        for (const set of state.boxes.values()) if (set.has(id)) return true;
        return false;
    }

    function renderAlunosSelectionState() {

        document.querySelectorAll('#alunos-container input[name="alunos[]"]').forEach(cb => {
            console.log('teste-2');
            const id = cb.value;
            const row = cb.closest('.aluno-row');
            row?.classList.toggle('is-assigned', isAssigned(id));
            // opcional: desmarcar check quando for alocado
            /* if (isAssigned(id)) {
                 cb.checked = false;
                 state.selected.delete(id);
             }*/
        });
    }

    // destaca visualmente o box ativo
    function efeitoBoxAtivo(boxId) {
        document.querySelectorAll('#boxes-container .box-chip')
            .forEach(chip => {
                console.log('teste');
                const val = chip.querySelector('input[name="boxes[]"]')?.value;
                chip.classList.toggle('is-active', val === boxId);
            });
    }

    // Carregar Disciplinas
    fetch('/odontologia/disciplinas')
        .then(r => r.json())
        .then(items => {
            if (!disciplinaSave) {
                disciplina.innerHTML = '<option value="">Selecione a disciplina</option>';
                items.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.DISCIPLINA;
                    opt.textContent = `${item.NOME} (${item.DISCIPLINA})`;
                    disciplina.appendChild(opt);
                });
            } else {
                disciplina.value = disciplinaSave;
            }
        })
        .catch(err => console.error('Erro ao carregar disciplinas:', err));

    function normalize(hhmm) {
        if (!hhmm) return '';
        const [h, m] = String(hhmm).split(':');
        return `${parseInt(h, 10)}:${m}`; // "07:30" -> "7:30"
    }

    function getAllTimeBoxes() {
        return Array.from(document.querySelectorAll('.time-check')).map(box => {
            const input = box.querySelector('input.form-check-input');
            const label = box.querySelector('label.form-check-label');
            const base = (label.dataset.time || label.textContent).trim();
            return { box, input, label, base, norm: normalize(base) };
        });
    }

    // Quando a disciplina muda, buscar as turmas daquela disciplina
    disciplina.addEventListener('change', () => {
        const valor = disciplina.value;
        turma.innerHTML = '<option value="">Carregando turmas...</option>';
        data.innerHTML = '<option value="">Selecione uma turma primeiro</option>';
        data.disabled = true;

        if (!valor) {
            turma.innerHTML = '<option value="">Selecione uma disciplina primeiro</option>';
            turma.disabled = true;
            return;
        }

        turma.disabled = false;
        fetch(`/odontologia/turmas/${encodeURIComponent(valor)}`)
            .then(r => r.json())
            .then(items => {
                turma.innerHTML = '<option value="">Selecione a turma</option>';
                items.forEach(t => {
                    const opt = document.createElement('option');
                    // se seu backend retornou apenas array de strings (pluck), use opt.value = opt.textContent = t;
                    opt.value = t;
                    opt.textContent = t;
                    turma.appendChild(opt);
                });
            })
            .catch(err => {
                console.error('Erro ao carregar turmas:', err);
                turma.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            })

        turma.addEventListener('change', () => {
            const disc = disciplina.value;
            const tur = turma.value;

            data.innerHTML = '<option value="">Carregando datas...</option>';
            data.disabled = true;

            if (!disc || !tur) {
                data.innerHTML = '<option value="">Selecione disciplina e turma primeiro</option>';
                return;
            }

            fetch(`/odontologia/datas/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}`)
                .then(r => r.json())
                .then(items => {
                    data.innerHTML = '<option value="">Selecione a data</option>';

                    if (!items || !items.length) {
                        data.innerHTML = '<option value="">Sem datas disponíveis</option>';
                        return;
                    }

                    const diasPT = {
                        '1': 'domingo',
                        '2': 'segunda-feira',
                        '3': 'terça-feira',
                        '4': 'quarta-feira',
                        '5': 'quinta-feira',
                        '6': 'sexta-feira',
                        '7': 'sábado'
                    };

                    // items vindo de ->pluck('A.DT_AULA') (array de strings)
                    items.forEach(d => {
                        const opt = document.createElement('option');

                        // se vier string tipo "2" ou número tipo 2, normaliza
                        const code = (typeof d === 'object')
                            ? (d.DIA_SEMANA ?? d.data ?? d)
                            : d;

                        opt.value = code;
                        opt.textContent = diasPT[code] || code; // mostra nome ou fallback o número
                        data.appendChild(opt);
                    });

                    data.disabled = false;
                })
                .catch(err => {
                    console.error('Erro ao carregar datas:', err);
                    data.innerHTML = '<option value="">Erro ao carregar datas</option>';
                });
        });

        data.addEventListener('change', () => {
            const disc = disciplina.value;
            const tur = turma.value;
            const dt = data.value;

            const all = getAllTimeBoxes();
            all.forEach(({ input, label, base }) => {
                input.disabled = true;
                input.checked = false;
                label.innerHTML = base; // restaura o texto base (só início)
            });

            if (!disc || !tur || !dt) return;


            const grid = document.getElementById('horarios-grid');
            const hrIni = document.getElementById('hr_ini');
            const hrFim = document.getElementById('hr_fim');

            // utilidades
            const pad2 = n => String(n).padStart(2, '0');
            const norm = hhmm => {
                if (!hhmm) return '';
                const [h, m] = String(hhmm).split(':').map(Number);
                return `${pad2(h)}:${pad2(m)}`;
            };
            const toMin = hhmm => { const [h, m] = hhmm.split(':').map(Number); return h * 60 + m; };

            // limpa e mostra placeholder
            function clearHorarios(msg = 'Selecione disciplina, turma e dia para ver horários.') {
                grid.innerHTML = `<div class="text-muted small">${msg}</div>`;
                if (hrIni) hrIni.value = '';
                if (hrFim) hrFim.value = '';
            }

            // desenha apenas o que veio do backend
            function renderHorarios(items) {
                grid.innerHTML = '';

                // 1) Achata {inicio,fim} -> [horarios únicos]
                const set = new Set();
                (items || []).forEach(it => {
                    const i = norm(it.inicio);
                    const f = norm(it.fim);
                    if (i) set.add(i);
                    if (f) set.add(f);
                });
                const horarios = Array.from(set).sort((a, b) => toMin(a) - toMin(b));

                if (!horarios.length) {
                    grid.innerHTML = `<div class="text-muted small">Sem horários disponíveis.</div>`;
                    if (hrIni) hrIni.value = '';
                    if (hrFim) hrFim.value = '';
                    return;
                }

                // 2) Renderiza UM checkbox por horário
                horarios.forEach((h, idx) => {
                    const id = `hor_${h.replace(':', '')}_${idx}`;
                    const html = `
                    <div class="time-item">
                    <input class="time-input" type="checkbox" id="${id}" name="horarios[]" value="${h}" checked>
                    <label class="time-card" for="${id}">${h}</label>
                    </div>`;
                    grid.insertAdjacentHTML('beforeend', html);
                });

                // 3) Atualiza hr_ini / hr_fim conforme seleção (opcional)
                grid.addEventListener('change', onSelectChange, { once: true });
                // também define valores iniciais vazios
                if (hrIni) hrIni.value = '';
                if (hrFim) hrFim.value = '';
            }

            function onSelectChange(e) {
                // reatacha para próximas mudanças
                grid.addEventListener('change', onSelectChange, { once: true });

                const checked = Array
                    .from(grid.querySelectorAll('input[name="horarios[]"]:checked'))
                    .map(i => i.value)
                    .sort((a, b) => toMin(a) - toMin(b));

                if (hrIni) hrIni.value = checked[0] || '';
                if (hrFim) hrFim.value = checked[checked.length - 1] || '';
            }

            clearHorarios();

            fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}/${encodeURIComponent(dt)}`)
                .then(r => r.json())
                .then(items => {
                    renderHorarios(items);
                })
                .catch(() => clearHorarios('Erro ao carregar horários.'));

            function renderAlunosEmTabela(data, alunosSelecionados = [], limitePorColuna = 10) {
                const container = document.getElementById('alunos-container');
                if (!container) return;

                container.classList.add('alunos-wrapper');
                container.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<p class="muted">Nenhum aluno encontrado.</p>';
                    return;
                }

                // preenche mapa de alunos (nome) para uso na pré-seleção
                data.forEach(it => {
                    const id = String(it.ALUNO ?? '').trim();
                    const nome = String(it.NOME_COMPL ?? '').trim();
                    state.alunos.set(id, { id, nome });
                });

                const selecionadosStr = alunosSelecionados.map(String);
                const frag = document.createDocumentFragment();

                for (let i = 0; i < data.length; i += limitePorColuna) {
                    const chunk = data.slice(i, i + limitePorColuna);

                    const coluna = document.createElement('div');
                    coluna.className = 'alunos-coluna';

                    chunk.forEach(item => {
                        const alunoId = String(item.ALUNO ?? '').trim();
                        const nome = String(item.NOME_COMPL ?? '').trim();
                        const isSelected = selecionadosStr.includes(alunoId);
                        const already = isAssigned(alunoId);

                        const row = document.createElement('label');
                        row.className = 'aluno-row';
                        if (already) row.classList.add('is-assigned');
                        row.dataset.search = `${alunoId} ${nome}`.toLowerCase();

                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.name = 'alunos[]';
                        input.value = alunoId;
                        input.id = `aluno_${alunoId}`;
                        input.checked = isSelected;
                        input.dataset.nome = nome;
                        input.setAttribute('aria-label', `Selecionar ${nome} (${alunoId})`);

                        row.setAttribute('for', input.id);

                        const info = document.createElement('div');
                        info.className = 'aluno-info';

                        const nomeEl = document.createElement('span');
                        nomeEl.className = 'aluno-nome';
                        nomeEl.textContent = nome || '(Sem nome)';
                        nomeEl.title = nome;

                        info.appendChild(nomeEl);
                        row.appendChild(input);
                        row.appendChild(info);
                        coluna.appendChild(row);
                    });

                    frag.appendChild(coluna);
                }

                container.appendChild(frag);

                // delegação: marca/desmarca mantém state.selected
                container.addEventListener('change', onAlunoToggle, { once: true });
            }

            function onAlunoToggle(e) {
                if (!e.target.matches('input[name="alunos[]"]')) return;
                const id = String(e.target.value);
                const nome = e.target.dataset.nome || state.alunos.get(id)?.nome || id;
                state.alunos.set(id, { id, nome });
                if (e.target.checked) state.selected.add(id);
                else state.selected.delete(id);

                // re-arma delegação para próximos changes
                e.currentTarget.addEventListener('change', onAlunoToggle, { once: true });
            }

            async function carregarAlunos(disc, tur, alunosSelecionados = [], limitePorColuna = 10) {
                const container = document.getElementById('alunos-container');
                if (!container) {
                    console.error('#alunos-container não encontrado');
                    return;
                }

                container.classList.add('alunos-wrapper');
                container.innerHTML = '<p class="muted">Carregando…</p>';

                try {
                    const res = await fetch(`/odontologia/alunos/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);

                    const data = await res.json();
                    renderAlunosEmTabela(data, alunosSelecionados, limitePorColuna);
                } catch (e) {
                    console.error(e);
                    container.innerHTML = `<p class="error">Erro ao carregar alunos. ${e.message || ''}</p>`;
                }
            }

            // Exemplo de uso:
            carregarAlunos(disc, tur, [], 10);

        });
    });

    fetch('/odontologia/boxes')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('boxes-container');
            container.classList.add('boxes-wrapper');
            container.innerHTML = '';
            data.forEach(item => {
                const id = `box_${String(item.ID_BOX_CLINICA).replace(/\s+/g, '_')}`;
                const isSelected = Array.isArray(boxesSelecionados) && boxesSelecionados.map(String).includes(String(item.ID_BOX_CLINICA));
                const isDisponivel = (item.DISPONIVEL ?? true) === true;

                const wrap = document.createElement('div');
                wrap.className = 'box-chip';

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.name = 'boxes[]';
                input.value = item.ID_BOX_CLINICA;
                input.id = id;
                input.checked = !!isSelected;
                if (!isDisponivel) input.disabled = true;

                const label = document.createElement('label');
                label.setAttribute('for', id);
                label.title = isDisponivel ? 'Disponível' : 'Indisponível';

                const text = document.createElement('span');
                text.textContent = item.DESCRICAO;

                if (item.SALA || item.STATUS) {
                    const badge = document.createElement('span');
                    badge.className = 'box-badge';
                    badge.textContent = item.SALA ?? item.STATUS;
                    label.appendChild(badge);
                }

                label.appendChild(text);
                wrap.appendChild(input);
                wrap.appendChild(label);
                container.appendChild(wrap);
            });

            container.addEventListener('click', (e) => {
                const chip = e.target.closest('.box-chip');
                if (!chip) return;

                const input = chip.querySelector('input[name="boxes[]"]'); //Aqui verifico a quantidade de alunos selecionados para pintar o box
                if (!input) return;

                if (!state?.selected || state.selected.size === 0) {
                    e.preventDefault();
                    input.checked = false;
                    console.log('Necessário selecionar ao menos 1 aluno para selecionar o box.');
                    return;
                }

                state.activeBox = input.value;
                efeitoBoxAtivo(state.activeBox);
                assignSelectedToActive();
            });

            /*console.log('Boxes carregados:', data);
            console.log('Selecionados:', boxesSelecionados);*/
        })
        .catch(error => {
            console.error('Erro ao carregar boxes:', error);
        });


    function renderPreSelecao() {
        const wrap = document.getElementById('pre-container');
        if (!wrap) return;
        wrap.innerHTML = '';

        const entries = [...state.boxes.entries()].sort((a, b) => a[0].localeCompare(b[0], 'pt-BR'));
        for (const [boxId, set] of entries) {
            const box = document.createElement('div'); box.className = 'pre-box';
            const head = document.createElement('div'); head.className = 'pre-header';
            head.innerHTML = `<span class="tag">${boxId}</span><span class="pre-count">${set.size}/${CAP}</span>`;
            box.appendChild(head);

            const list = document.createElement('div'); list.className = 'pre-list';
            for (const id of set) {
                const nome = state.alunos.get(id)?.nome || id;
                const chip = document.createElement('div'); chip.className = 'pre-chip';
                chip.innerHTML = `<span>${nome}</span><button type="button" class="rm" data-rm="${boxId}|${id}">×</button>`;
                list.appendChild(chip);
            }
            box.appendChild(list);
            wrap.appendChild(box);
        }
    }

    // remover da pré-seleção
    document.getElementById('pre-container')?.addEventListener('click', (e) => {
        const btn = e.target.closest('button.rm');
        if (!btn) return;
        const [boxId, alunoId] = btn.dataset.rm.split('|');
        unassign(boxId, alunoId);
    });

    function renderAlunosBoxes() {
        const wrap = document.getElementById('alunos-boxes-container');
        if (!wrap) return;

        // Limpa e trata vazio
        wrap.innerHTML = '';
        if (state.boxes.size === 0) {
            wrap.innerHTML = '<p class="muted">Faça a pré-seleção de alunos e box.</p>';
            return;
        }

        const frag = document.createDocumentFragment();

        // Ordena boxes para render estável
        const entries = [...state.boxes.entries()].sort((a, b) => a[0].localeCompare(b[0], 'pt-BR'));

        for (const [boxId, set] of entries) {
            const box = document.createElement('div');
            box.className = 'pre-box';

            // Cabeçalho do box: nome + contagem + limpar
            const head = document.createElement('div');
            head.className = 'pre-header';
            head.innerHTML = `
            <span class="tag">Box ${boxId}</span>
            <span class="pre-count">${set.size}/${CAP}</span>
            <button type="button" class="pre-clear" data-clear="${boxId}" title="Limpar este box">Limpar</button>
        `;
            box.appendChild(head);

            // Lista de alunos (chips)
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

            box.appendChild(list);
            frag.appendChild(box);
        }

        wrap.appendChild(frag);
    }

    function assignSelectedTo(boxId) {
        if (!boxId) return;

        const selected = [...(state?.selected ?? new Set())];
        if (selected.length === 0) {
            console.log('Necessário selecionar ao menos 1 aluno para selecionar o box.');
            return;
        }

        // bucket alvo
        const bucket = state.boxes.get(boxId) ?? new Set();
        const before = bucket.size;
        let free = CAP - before;

        if (free <= 0) {
            warn('Box atingiu o limite de alunos');
            return; // mantém selecionados para você escolher outro box
        }

        // cada aluno só pode estar em 1 box: remove dos outros antes
        for (const id of selected) {
            for (const [bId, set] of state.boxes) {
                if (bId !== boxId) set.delete(id);
            }
        }

        // adiciona até CAP; remove do selected apenas os que couberem
        let added = 0;
        for (const id of selected) {
            if (free <= 0) break;
            bucket.add(id);
            state.selected.delete(id);
            free--; added++;
        }

        state.boxes.set(boxId, bucket);

        if (added < selected.length) {
            // sobrou gente em selected → não coube todo mundo
            warn('Box atingiu o limite de alunos');
        }

        renderAlunosBoxes?.();
        renderAlunosSelectionState?.();
    }

    function assignSelectedToActive() {
        const container = document.getElementById('boxes-container');
        const boxId = state.activeBox;
        if (!boxId || !state?.selected || state.selected.size === 0) {
            console.log('Necessário selecionar ao menos 1 aluno para selecionar o box.');
            return;
        }

        container.addEventListener('click', (e) => {
            const chip = e.target.closest('.box-chip');
            if (!chip) return;

            const input = chip.querySelector('input[name="boxes[]"]'); //Aqui verifico a quantidade de alunos selecionados para pintar o box
            if (!input) return;

            console.log('teste-1')
            const bucket = state.boxes.get(boxId) ?? new Set();

            if (input.checked && bucket && bucket.size > 0) {
                e.preventDefault();           // impede toggle
                input.checked = true;

                return;
            }


            // aluno só em um box
            for (const [bId, set] of state.boxes) {
                if (bId !== boxId) for (const id of state.selected) set.delete(id);
            }

            for (const id of [...state.selected]) {
                if (bucket.size >= CAP) break;
                bucket.add(id);
                state.selected.delete(id);
            }

            assignSelectedTo(boxId);

            state.boxes.set(boxId, bucket);
            renderPreSelecao?.();
            renderAlunosBoxes();
            renderAlunosSelectionState?.();
        });
    }

    function unassign(boxId, alunoId) {
        const set = state.boxes.get(boxId);
        if (!set) return;
        set.delete(alunoId);
        if (set.size === 0) state.boxes.delete(boxId);
        - renderPreSelecao?.();
        + renderAlunosBoxes();
        renderAlunosSelectionState?.();
    }
    const form = document.getElementById("form-agenda");
    form.addEventListener('submit', (e) => {
        // limpa hiddens anteriores para não duplicar em um segundo submit
        form.querySelectorAll('input[name^="alocacoes["]').forEach(el => el.remove());

        // gera inputs: alocacoes[BOX_ID][]=ALUNO_ID
        state.boxes.forEach((setAlunos, boxId) => {
            // de preferência use o ID numérico do box (ex.: ID_BOX_CLINICA)
            setAlunos.forEach(alunoId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `alocacoes[${boxId}][]`;
                input.value = String(alunoId);
                form.appendChild(input);
            });
        });
    });

});