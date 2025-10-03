// =========================
// Helpers e utilitários
// =========================
const pad2 = n => String(n).padStart(2, '0');
const norm = t => { if (!t) return ''; const [h, m] = String(t).split(':').map(Number); return `${pad2(h)}:${pad2(m)}`; };
const toMin = t => { const [h, m] = String(t).split(':').map(Number); return h * 60 + m; };

function normalize(hhmm) {
    if (!hhmm) return '';
    const [h, m] = String(hhmm).split(':');
    return `${parseInt(h, 10)}:${m}`;
}

const toMinutes = hm => { const [h, m] = String(hm).split(':').map(Number); return h * 60 + m; };
const padHHMM = hm => { const [h, m] = String(hm).split(':'); return `${String(h).padStart(2, '0')}:${m}`; };

// =========================
// Validar valores do formulário
// =========================

window.validarFormulario = function () {
    const errors = [];
    const getVal = (sel) => document.querySelector(sel)?.value?.trim() || "";
    const byId = (id) => document.getElementById(id);

    const paciente = getVal('[name="ID_PACIENTE"]');
    const status = getVal('[name="status"]');
    const date = getVal('[name="date"]');
    const dateEnd = getVal('[name="date_end"]');
    const disciplina = getVal('[name="disciplina"]');
    const box = getVal('[name="ID_BOX"]');
    const turma = getVal('[name="turma"], [name="TURMA"]');
    const procedimento = getVal('[name="procedimento"]');
    const recorrencia = document.querySelector('input[name="recorrencia"]:checked')?.value;

    // 1) Tente pelos IDs (únicos)
    const hrIniEl = byId('hr_ini');
    const hrFimEl = byId('hr_fim');
    let hr_ini = hrIniEl?.value?.trim() || "";
    let hr_fim = hrFimEl?.value?.trim() || "";

    // 2) Se vazio, tente pelos data-* do grid
    if (!hr_ini || !hr_fim) {
        const grid = byId('horarios-grid');
        const dIni = grid?.dataset?.hrIni || "";
        const dFim = grid?.dataset?.hrFim || "";
        if (dIni && dFim) {
            hr_ini = dIni;
            hr_fim = dFim;
            if (hrIniEl) hrIniEl.value = hr_ini;
            if (hrFimEl) hrFimEl.value = hr_fim;
        }
    }

    // 3) Se ainda vazio, derive dos horários marcados
    if (!hr_ini || !hr_fim) {
        const marcados = Array.from(document.querySelectorAll('input[name="horarios[]"]:checked'))
            .map(el => el.value)
            .filter(Boolean)
            .sort(); // HH:mm ordena certo
        if (marcados.length > 0) {
            hr_ini = marcados[0];
            hr_fim = marcados[marcados.length - 1];
            if (hrIniEl) hrIniEl.value = hr_ini;
            if (hrFimEl) hrFimEl.value = hr_fim;
        }
    }

    // ---- Validações demais ----
    const regexData = /^\d{2}\/\d{2}\/\d{4}$/;

    if (!paciente) errors.push("O paciente é obrigatório.");
    if (!status) errors.push("O status é obrigatório.");

    if (!date) errors.push("Informe a data inicial.");
    else if (!regexData.test(date)) errors.push("Data inicial deve estar no formato dd/mm/aaaa.");

    if (!dateEnd) errors.push("Informe a data final.");
    else if (!regexData.test(dateEnd)) errors.push("Data final deve estar no formato dd/mm/aaaa.");

    if (regexData.test(date) && regexData.test(dateEnd)) {
        const [d1, m1, y1] = date.split('/').map(Number);
        const [d2, m2, y2] = dateEnd.split('/').map(Number);
        if (new Date(y1, m1 - 1, d1) > new Date(y2, m2 - 1, d2)) {
            errors.push("A data final deve ser maior ou igual à data inicial.");
        }
    }

    if (!hr_ini) errors.push("Informe a hora inicial.");
    if (!hr_fim) errors.push("Informe a hora final.");

    if (!disciplina) errors.push("A disciplina é obrigatória.");
    if (!box) errors.push("O box é obrigatório.");
    if (!turma) errors.push("A turma é obrigatória.");
    if (!procedimento) errors.push("O procedimento é obrigatório.");

    /*if (recorrencia === '2') {
        const diasMarcados = document.querySelectorAll('input[name="dia_recorrencia[]"]:checked');
        if (diasMarcados.length === 0) errors.push("Escolha ao menos um dia da recorrência.");
    }*/

    if (errors.length) {
        Swal.fire({ icon: "warning", title: "Atenção", html: errors.join("<br>"), confirmButtonText: "Ok" });
        return false;
    }
    return true;
};

function validarDataAnoAtual(campo) {
    const valor = campo.value.replace(/[^0-9\/]/g, '').slice(0, 10);
    campo.value = valor;

    if (valor.length === 10) {
        const [dia, mes, ano] = valor.split('/').map(Number);
        const anoAtual = new Date().getFullYear();

        if (ano !== anoAtual) {
            alert(`O ano deve ser ${anoAtual}`);
            campo.value = '';
            return;
        }
        if (dia < 1 || dia > 30) {
            alert('O dia deve estar entre 1 e 30');
            campo.value = '';
            return;
        }
        if (mes < 1 || mes > 12) {
            alert('O mês deve estar entre 1 e 12');
            campo.value = '';
            return;
        }
    }
}

function getAllTimeBoxes() {
    return Array.from(document.querySelectorAll('.time-check')).map(box => {
        const input = box.querySelector('input.form-check-input');
        const label = box.querySelector('label.form-check-label');
        const base = (label?.dataset?.time || label?.textContent || '').trim();
        return { box, input, label, base, norm: normalize(base) };
    });
}

// Mantido o mesmo nome (global); usado por enableHorariosFrom.
function disableAllHorarios() {
    const all = getAllTimeBoxes();
    all.forEach(({ input }) => {
        if (!input) return;
        input.checked = false;
        input.disabled = true;
    });
    const hrIni = document.getElementById('hr_ini');
    const hrFim = document.getElementById('hr_fim');
    if (hrIni) hrIni.value = '';
    if (hrFim) hrFim.value = '';
}

// util: "dd/mm/aaaa" -> código 1..7 (1=Domingo ... 7=Sábado)
function brDateToWeekCode(br) {
    if (!br) return '';
    const [d, m, y] = br.split('/').map(Number);
    if (!d || !m || !y) return '';
    const js = new Date(y, m - 1, d); // JS: mês 0..11
    const dow = js.getDay();           // 0=Dom .. 6=Sáb
    return dow === 0 ? 1 : (dow + 1);  // 1..7 (1=Dom)
}

// utilitário para pré-selecionar programaticamente no Select2
function hydrateSelect2($el, id, text) {
    if (!id || !text) return;
    const exists = Array.from($el[0].options).some(o => String(o.value) === String(id));
    if (!exists) {
        const opt = new Option(text, id, true, true);
        $el.append(opt).trigger('change', { silentInit: true });
    } else {
        $el.val(String(id)).trigger('change', { silentInit: true });
    }
}
// Exposto globalmente com o MESMO nome
function updateHrBoundsFrom(items) {
    const hrIni = document.getElementById('hr_ini');
    const hrFim = document.getElementById('hr_fim');
    if (!items || !items.length) {
        if (hrIni) hrIni.value = '';
        if (hrFim) hrFim.value = '';
        return;
    }
    const starts = items.map(i => normalize(i.inicio)).sort((a, b) => toMinutes(a) - toMinutes(b));
    const first = starts[0];
    const last = starts[starts.length - 1];

    if (hrIni) hrIni.value = padHHMM(first);

    const lastObj = items.find(i => normalize(i.inicio) === last);
    if (hrFim) hrFim.value = padHHMM(normalize(lastObj?.fim || last));
}

// =========================
// DOM Ready (centralizado)
// =========================

$(document).ready(function () {
    $.fn.select2.defaults.set("language", "pt-BR");

    // Datepicker
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        language: 'pt-BR',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function () {
        // dispara o mesmo fluxo que você já usa em #date.on('change', ...)
        $(this).trigger('change');
    });

    // Máscaras e validações
    $('#valor').mask('000.000.000.000.000,00', { reverse: true });
    $('#date, #date_end')
        .mask('00/00/0000')
        .on('input', function () { validarDataAnoAtual(this); });

    // Aviso para valor > 100
    $('#valor').on('blur', function () {
        let valor = parseFloat($(this).val().replace(',', '.'));
        if (!isNaN(valor) && valor > 100) {
            if (!confirm("O valor informado é superior a R$ 100,00. Deseja continuar?")) {
                $(this).val('');
                $(this).focus();
            }
        }
    });

    // Recorrência (1 x 2)
    const cbV1 = document.querySelector('input[name="recorrencia"][value="1"]');
    const cbV2 = document.querySelector('input[name="recorrencia"][value="2"]');
    if (cbV1 && cbV2) {
        cbV2.addEventListener('change', () => { if (cbV2.checked) cbV1.checked = false; });
        cbV1.addEventListener('change', () => { if (cbV1.checked) cbV2.checked = false; });
        if (cbV1.checked && cbV2.checked) cbV1.checked = false;
    }

    // Timepicker
    $('#hr_ini, #hr_fim')
        .attr('maxlength', 4) // você definiu 4; se quiser HH:MM (5), ajuste aqui
        .on('input', function () { this.value = this.value.replace(/[^0-9:]/g, '').slice(0, 5); })
        .timepicker({ showMeridian: false, defaultTime: false, minuteStep: 1 });

    $('#hr_ini').on('focus', function () {
        const agora = new Date();
        const hora = agora.getHours().toString().padStart(2, '0');
        const minuto = agora.getMinutes().toString().padStart(2, '0');
        $(this).timepicker('setTime', `${hora}:${minuto}`);
    });

    $('#hr_ini').on('changeTime.timepicker', function (e) {
        const time = e.time;
        let hour = parseInt(time.hours);
        const minutes = time.minutes;
        hour = (hour + 1) % 24;
        const novaHoraFim = `${hour < 10 ? '0' + hour : hour}:${minutes < 10 ? '0' + minutes : minutes}`;
        $('#hr_fim').timepicker('setTime', novaHoraFim);
    });

    // Selects simples
    $('#dia_semana').select2({ placeholder: '', allowClear: true, width: '100%' });

    // Procedimentos
    const $disciplineSelectProc = $('#form-select-proc');
    $disciplineSelectProc.select2({
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/procedimentos',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { query: params.term || '' }; },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_SERVICO_CLINICA,
                        text: p.SERVICO_CLINICA_DESC + ' ' + (p.DISCIPLINA ?? ''),
                        disciplina: p.DISCIPLINA
                    }))
                };
            },
            cache: true
        }
    });

    // Agendamento – disciplina/box/turma/horários
    const hrIni = document.getElementById('hr_ini');
    const hrFim = document.getElementById('hr_fim');


    const $disciplineSelect = $('#form-select-discipline');
    const $boxSelect = $('#form-select-box');
    const $turmaSelect = $('#form-select-turma');

    let disciplinaSelecionada = null;
    let boxSelecionado = null;

    $boxSelect.prop('disabled', true);
    $turmaSelect.prop('disabled', true);

    $disciplineSelect.select2({
        allowClear: true,
        ajax: {
            url: '/odontologia/disciplinascombox/',
            dataType: 'json',
            delay: 250,
            data: params => ({ query: params.term || '' }),
            processResults: data => {
                const list = Array.isArray(data) ? data : (data?.data || []);
                return {
                    results: list.map(p => ({
                        id: p.DISCIPLINA,
                        text: p.DISCIPLINA + '-' + p.NOME,
                        box: p.ID_BOX
                    }))
                };
            },
            cache: true
        }

    });

    // Carrega box de acordo com a disciplina
    $('#form-select-box').select2({
        allowClear: true,
        ajax: {
            url: function () {
                return '/getBoxDisciplines/' + encodeURIComponent(disciplinaSelecionada || 'default');
            },
            dataType: 'json',
            delay: 250,
            data: function (params) { return { query: params.term || '' }; },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_BOX_CLINICA,
                        text: p.NOME_BOX || p.DESCRICAO
                    }))
                };
            },
            cache: true
        }
    }).on('select2:select', (e) => {
        boxSelecionado = String(e.params.data.id || '');
        const ready = !!(disciplinaSelecionada && boxSelecionado);
        $turmaSelect.prop('disabled', !ready).val(null).trigger('change.select2');
    });

    // Turmas de acordo com disciplina/box
    $turmaSelect.select2({
        allowClear: true,
        placeholder: 'Selecione a turma',
        minimumInputLength: 0,
        ajax: {
            url: '/odontologia/turmas',
            type: 'GET',
            dataType: 'json',
            delay: 250,
            cache: true,
            data: params => ({
                term: params.term || '',
                disciplina: $disciplineSelect.val() || '',
                box: $boxSelect.val() || ''
            }),
            processResults: data => ({
                results: (Array.isArray(data) ? data : []).map(t => ({ id: t, text: t }))
            })
        }
    });

    // Seleção disciplina
    $disciplineSelect.on('select2:select', (e) => {
        disciplinaSelecionada = e.params.data.id;
        boxSelecionado = null;
        $boxSelect.prop('disabled', false).val(null).trigger('change');
        $turmaSelect.prop('disabled', false).val(null).trigger('change');
    });

    $disciplineSelect.on('select2:clear select2:unselect', () => {
        disciplinaSelecionada = null;
        boxSelecionado = null;
        $boxSelect.val(null).trigger('change').prop('disabled', true);
        $turmaSelect.val(null).trigger('change').prop('disabled', true);
    });

    let diaSelecionado = '';

    const $date = $('#date');
    const grid = document.getElementById('horarios-grid');

    function handleDate() {
        setTimeout(() => {
            const raw = ($date.val() || '').trim();        // jQuery lê o valor certo pós-máscara
            if (raw.length === 10) {
                diaSelecionado = brDateToWeekCode(raw) || ''; // 1..7
            } else {
                diaSelecionado = '';
            }
            tryReload();
        }, 0);
    }

    function tryReload() {
        const discEl = document.getElementById('form-select-discipline');
        const turmaEl = document.getElementById('form-select-turma');
        const disc = (discEl?.value || '').trim();
        const turma = (turmaEl?.value || '').trim();

        // Fail-safe: se o dia ainda não foi calculado, tente agora
        if (!diaSelecionado) {
            const raw = ($date.val() || '').trim();
            if (raw.length === 10) {
                diaSelecionado = brDateToWeekCode(raw) || '';
            }
        }

        if (disc && turma && diaSelecionado) {
            reloadHorarios(disc, turma, String(diaSelecionado));
        } else if (grid) {
            grid.innerHTML = `<div class="text-muted small">Selecione disciplina, box, turma e dia</div>`;
        }

        if (disc && turma) {
            carregaAlunos();
        }
    }

    $date.on('change blur input', handleDate);
    $('.datepicker#date').on('changeDate', handleDate);

    if (($date.val() || '').trim().length === 10) {
        handleDate();
    }

    $('#form-select-discipline').on('change', tryReload);
    $('#form-select-turma').on('select2:select change', tryReload);

    if ($date.value) handleDate();

    // =========================
    // reloadHorarios (mesmo nome)
    // =========================

    function reloadHorarios(disc, turma, diaSelecionadoParam) {
        if (!disc || !turma || !diaSelecionadoParam) {
            if (grid) grid.innerHTML = `<div class="text-muted small">Selecione disciplina, box, turma e dia</div>`;
            if (hrIni) hrIni.value = '';
            if (hrFim) hrFim.value = '';
            return;
        }

        if (grid) grid.innerHTML = `<div class="text-muted small">Carregando horários...</div>`;
        if (hrIni) hrIni.value = '';
        if (hrFim) hrFim.value = '';

        function onSelectChange() {
            const checked = Array.from(grid.querySelectorAll('input[name="horarios[]"]'))
                .map(i => i.value)
                .sort((a, b) => toMin(a) - toMin(b));
            if (hrIni) hrIni.value = checked[0] || '';
            if (hrFim) hrFim.value = checked[checked.length - 1] || '';

        }

        function renderHorarios(items) {
            grid.innerHTML = '';
            const set = new Set();
            (items || []).forEach(it => {
                const i = norm(it.inicio);
                const f = norm(it.fim);
                if (i) set.add(i);
                if (f) set.add(f);
            });
            const horarios = Array.from(set).sort((a, b) => toMin(a) - toMin(b));

            horarios.forEach((h, idx) => {
                const id = `hor_${h.replace(':', '')}_${idx}`;
                grid.insertAdjacentHTML('beforeend', `
                <div class="time-item">
                <input class="time-input" type="checkbox" id="${id}" name="horarios[]" value="${h}" checked>
                </div>`);
            });

            grid.addEventListener('change', onSelectChange, { once: true });
            onSelectChange();
        }

        fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(turma)}/${encodeURIComponent(diaSelecionadoParam)}`)
            .then(r => r.json())
            .then(items => { renderHorarios(items); })
            .catch(() => {
                grid.innerHTML = `<div class="text-danger small">Erro ao carregar horários.</div>`;
            });
    }


    function carregaAlunos() {
        const disc = $disciplineSelect.val() || '';
        const turma = $turmaSelect.val() || '';
        const box = $boxSelect.val() || '';

        if (!disc || !turma || !box) {
            console.warn('Disciplina, turma ou box não selecionado(s).');
            return;
        }

        const $wrap = $('#alunos-readonly');
        if (!$wrap.length) {
            console.warn('Container #alunos-readonly não encontrado.');
            return;
        }

        // estado de carregamento
        $wrap.html('<span class="text-muted">Carregando…</span>');

        const url = `/odontologia/alunos/${encodeURIComponent(disc)}/${encodeURIComponent(turma)}/${encodeURIComponent(box)}`;

        $.ajax({
            url,
            dataType: 'json',
            cache: true,
            data: { query: '' }, // ajuste se o backend exigir termo
        })
            .done(function (data) {
                const alunos = document.getElementById('alunos-ids-eco');

                if (!Array.isArray(data) || data.length === 0) {
                    $wrap.html('<span class="text-muted">Nenhum aluno cadastrado</span>');
                    if (alunos) {
                        if (alunos.tagName === 'DIV') alunos.innerHTML = '';
                        else alunos.value = '';
                    }
                    return;
                }

                const frag = document.createDocumentFragment();
                data.forEach(p => {
                    const ra = String(p.ALUNO ?? '').trim();
                    const nome = p.SERVICO_CLINICA_DESC || p.NOME_COMPL || '';
                    const span = document.createElement('span');
                    span.className = 'badge bg-secondary';
                    span.dataset.ra = ra;
                    span.textContent = ra ? `${ra} — ${nome}` : nome;
                    frag.appendChild(span);
                });

                $wrap.empty().append(frag);


                if (alunos) {
                    const ids = data.map(p => String(p.ALUNO ?? '').trim()).filter(Boolean);

                    if (alunos.tagName === 'DIV') {
                        // MODO ARRAY: cria vários inputs hidden
                        alunos.innerHTML = '';
                        ids.forEach(id => {
                            const h = document.createElement('input');
                            h.type = 'hidden';
                            h.name = 'alunos_do_agendamento[]';
                            h.value = id;
                            alunos.appendChild(h);
                        });
                    } else {
                        // MODO STRING: "1,2,3"
                        alunos.value = ids.join(',');
                    }
                }
            })
            .fail(function (err) {
                console.error(err);
                $wrap.html('<span class="text-danger">Falha ao carregar alunos.</span>');
            });
    }

    // jQuery objects
    const $disciplinaEncaminhamento = $('#disciplina-enc');

    function carregaDisciplinaEncaminhamento() {
        // destrói antes de recriar (evita eventos duplicados)
        if ($disciplinaEncaminhamento.data('select2')) {
            $disciplinaEncaminhamento.select2('destroy');
        }

        // limpa seleção ao trocar a fonte
        $disciplinaEncaminhamento.empty().trigger('change');

        $disciplinaEncaminhamento.select2({
            width: '100%',
            placeholder: 'Disciplina do encaminhamento',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: `/odontologia/disciplinascombox/`,
                dataType: 'json',
                delay: 250,
                // ATENÇÃO: nome do parâmetro precisa bater com o seu backend
                data: params => ({ query: params.term || '' }),
                processResults: data => ({
                    results: (Array.isArray(data) ? data : []).map(p => ({
                        id: p.DISCIPLINA,                     // ou p.DISCIPLINA, conforme sua lógica
                        text: p.NOME
                        }))
                }),
                cache: true
            }
            // ,dropdownParent: $('#meuModal') // descomente se estiver dentro de modal Bootstrap
        });
    }

    // inicializa
    carregaDisciplinaEncaminhamento();

    // re-carrega quando a turma mudar
    $turmaSelect.on('change', carregaDisciplinaEncaminhamento);

    carregaDisciplinaEncaminhamento();

    // Se mudar o seletor de "dia" (você usa #data como dia – mantido)
    document.getElementById('date').addEventListener('change', () => {
        const raw = document.getElementById('date').value.trim();
        const dia = raw ? brDateToWeekCode(raw) : ''; // 1..7

        const disc = $disciplineSelect.val() || (grid?.dataset.disciplina || '');
        const turma = $turmaSelect.val() || (grid?.dataset.turma || '');

        if (disc && turma && dia) {
            reloadHorarios(disc, turma, String(dia));
        } else if (grid) {
            grid.innerHTML = `<div class="text-muted small">Selecione disciplina, box, turma e dia</div>`;
        }
    });

    // Quando escolher turma, tenta carregar horários (se já houver dia)
    $turmaSelect.on('select2:select', (e) => {
        const disc = $disciplineSelect.val() || '';
        const turma = e.params?.data?.id || $turmaSelect.val() || '';

        const raw = document.getElementById('date').value.trim();
        const dia = raw ? brDateToWeekCode(raw) : ''; // calcula o dia (1..7)

        if (disc && turma && dia) {
            reloadHorarios(disc, turma, String(dia));
        } else if (grid) {
            grid.innerHTML = `<div class="text-muted small">Selecione disciplina, box, turma e dia</div>`;
        }
    });

    // =========================
    // Select Paciente (mantido)
    // =========================
    $('#selectPatient').select2({
        placeholder: "Selecione o paciente por nome ou CPF",
        allowClear: true,
        ajax: {
            url: '/getPacientes',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { query: params.term }; },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_PACIENTE,
                        text: p.NOME_COMPL_PACIENTE
                    }))
                };
            },
            cache: true
        }
    }).on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });

    const { pacienteId, nomePaciente, idAgendamento } = window.agendaData || {};
    if (pacienteId && nomePaciente) {
        const option = new Option(nomePaciente, pacienteId, true, true);
        $('#selectPatient').append(option).trigger('change');
        $(option).attr('data-id-agendamento', idAgendamento);
        $('#selectPatient').append(option).trigger('change');
    }

    // Pagamento x valor
    const pagto = document.getElementById('pagto');
    const valor = document.getElementById('valor');
    if (pagto && valor) {
        pagto.addEventListener('change', function () {
            if (pagto.value === 'S') {
                valor.disabled = false;
            } else {
                valor.disabled = true;
            }
        });
    }
});