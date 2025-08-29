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

            fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(tur)}/${encodeURIComponent(dt)}`)
                .then(r => r.json())
                .then(items => {
                    // items = [{inicio: "07:30", fim: "08:15"}, ...]
                    const toMinutes = hm => { const [h, m] = String(hm).split(':').map(Number); return h * 60 + m; };
                    const padHHMM = hm => { const [h, m] = String(hm).split(':'); return `${String(h).padStart(2, '0')}:${m}`; };

                    const slotEndByStart = new Map(items.map(it => [normalize(it.inicio), it.fim]));
                    const allowed = [...slotEndByStart.keys()];

                    // reset: desmarca e desabilita todos
                    all.forEach(({ input, label, base }) => {
                        input.checked = false;
                        input.disabled = true;      // sem opção de marcar manualmente
                        label.innerHTML = base;
                    });

                    // marca e mostra "até fim" nos retornados
                    all.forEach(({ input, label, base, norm }) => {
                        if (slotEndByStart.has(norm)) {
                            input.checked = true;
                            input.disabled = false;    // mantém bloqueado
                            const fim = slotEndByStart.get(norm);
                            if (fim) label.innerHTML = `${base}`;
                        }
                    });

                    // define hr_ini (primeiro) e hr_fim (fim do último)
                    if (allowed.length) {
                        allowed.sort((a, b) => toMinutes(a) - toMinutes(b));
                        const first = allowed[0];
                        const last = allowed[allowed.length - 1];
                        if (typeof hrIni !== 'undefined' && hrIni) hrIni.value = padHHMM(first);
                        if (typeof hrFim !== 'undefined' && hrFim) hrFim.value = padHHMM(slotEndByStart.get(last) || last);
                    } else {
                        if (typeof hrIni !== 'undefined' && hrIni) hrIni.value = '';
                        if (typeof hrFim !== 'undefined' && hrFim) hrFim.value = '';
                    }
                })
                .catch(err => console.error('Erro ao carregar horários:', err));
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