const add = document.getElementById('add');

document.addEventListener('DOMContentLoaded', function () {
    const disciplina = document.getElementById('disciplina');
    const disciplinaSave = disciplina.value;
    const turma = document.getElementById('turma');
    const data = document.getElementById('data');

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

            // exemplo de uso: depois que usuário escolher disciplina/turma/dia:
            // fetch(`/odontologia/horarios/${disc}/${tur}/${dia}`)
            //   .then(r => r.json())
            //   .then(renderHorarios)
            //   .catch(() => clearHorarios('Erro ao carregar horários.'));

            // estado inicial
            clearHorarios();


            fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}/${encodeURIComponent(dt)}`)
                .then(r => r.json())
                .then(items => {
                    renderHorarios(items);      // <-- só isso
                })
                .catch(() => clearHorarios('Erro ao carregar horários.'));
        });
    });

    fetch('/odontologia/boxes')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('boxes-container');
            container.innerHTML = ''; // Limpa o conteúdo anterior
            data.forEach(item => {
                const div = document.createElement('div');

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'boxes[]';
                checkbox.value = item.ID_BOX_CLINICA;
                checkbox.id = `box_${item.ID_BOX_CLINICA.replace(/\s+/g, '_')}`;

                if (Array.isArray(boxesSelecionados) && boxesSelecionados.includes(item.ID_BOX_CLINICA)) {
                    checkbox.checked = true;
                }

                const label = document.createElement('label');
                label.style.marginLeft = '6px';
                label.htmlFor = checkbox.id;
                label.textContent = ' ' + item.DESCRICAO;

                div.appendChild(checkbox);
                div.appendChild(label);
                container.appendChild(div);
            });
            console.log(boxesSelecionados)
        })
        .catch(error => {
            console.error('Erro ao carregar boxes:', error);
        });
});