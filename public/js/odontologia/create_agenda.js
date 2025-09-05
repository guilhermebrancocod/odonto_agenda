$('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    language: 'pt-BR',
    autoclose: true,
    todayHighlight: true
});

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

$(document).ready(function () {
    $('#valor').on('blur', function () {
        let valor = parseFloat($(this).val().replace(',', '.'));

        if (!isNaN(valor) && valor > 100) {
            if (!confirm("O valor informado é superior a R$ 100,00. Deseja continuar?")) {
                $(this).val('');
                $(this).focus();
            }
        }
    });
});

const cbV1 = document.querySelector('input[name="recorrencia"][value="1"]');
const cbV2 = document.querySelector('input[name="recorrencia"][value="2"]');
const diasBox = document.getElementsByName('dia_recorrencia');

if (cbV1 && cbV2) {
    // quando marcar o 2, desmarca o 1
    cbV2.addEventListener('change', () => {
        if (cbV2.checked) cbV1.checked = false;
    });

    // quando marcar o 1, desmarca o 2 (opcional, para ficar mutuamente exclusivo)
    cbV1.addEventListener('change', () => {
        if (cbV1.checked) cbV2.checked = false;
    });

    // regra inicial: se ambos vierem marcados por algum motivo, prioriza o 2 (ou ajuste como preferir)
    if (cbV1.checked && cbV2.checked) cbV1.checked = false;
}

$(document).ready(function () {
    // Inicializa os dois timepickers
    $('#hr_ini, #hr_fim')
        .attr('maxlength', 4) // Limita para 5 caracteres
        .on('input', function () {
            // Remove caracteres inválidos
            this.value = this.value.replace(/[^0-9:]/g, '').slice(0, 5);
        })
        .timepicker({
            showMeridian: false,
            defaultTime: false,
            minuteStep: 1
        });

    $('#hr_ini').on('focus', function () {
        const agora = new Date();
        const hora = agora.getHours().toString().padStart(2, '0');
        const minuto = agora.getMinutes().toString().padStart(2, '0');
        $(this).timepicker('setTime', `${hora}:${minuto}`);
    });

    // Quando o campo de hora inicial for selecionado
    $('#hr_ini').on('changeTime.timepicker', function (e) {
        const time = e.time; // objeto com hora e minuto
        let hour = parseInt(time.hours);
        const minutes = time.minutes;

        // Adiciona 1 hora (sem ultrapassar 23)
        hour = (hour + 1) % 24;
        const hourStr = hour < 10 ? '0' + hour : hour;
        const minuteStr = minutes < 10 ? '0' + minutes : minutes;

        const novaHoraFim = `${hourStr}:${minuteStr}`;
        $('#hr_fim').timepicker('setTime', novaHoraFim);
    });
});

$(document).ready(function () {
    $('#dia_semana').select2({
        placeholder: '',
        allowClear: true,
        width: '100%'
    });
});

$(document).ready(function () {
    $('#valor').mask('000.000.000.000.000,00', { reverse: true });
});

$(document).ready(function () {
    $('#date, #date_end')
        .mask('00/00/0000')
        .on('input', function () {
            validarDataAnoAtual(this);
        });
});

$(document).ready(function () {
    $.fn.select2.defaults.set("language", "pt-BR");

    const $disciplineSelect = $('#form-select-proc');

    $disciplineSelect.select2({
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/procedimentos',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
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
});

$(document).ready(function () {
    $.fn.select2.defaults.set("language", "pt-BR");
    let disciplinaSelecionada = null;
    let boxSelecionado = null;

    const $disciplineSelect = $('#form-select-discipline');
    const $boxSelect = $('#form-select-box');
    const $turmaSelect = $('#form-select-turma');


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

    $disciplineSelect.on('select2:select', (e) => {
        disciplinaSelecionada = e.params.data.id;
        boxSelecionado = null;
        $boxSelect.prop('disabled', false).val(null).trigger('change');
        $turmaSelect.prop('disabled', false).val(null).trigger('change'); // limpa boxes e habilita
    });

    $disciplineSelect.on('select2:clear select2:unselect', () => {
        disciplinaSelecionada = null;
        boxSelecionado = null;
        $boxSelect.val(null).trigger('change').prop('disabled', true);
        $turmaSelect.val(null).trigger('change').prop('disabled', true);
    });

    // Inicializa o segundo select (boxes)
    $('#form-select-box').select2({
        allowClear: true,
        ajax: {
            url: function () {
                return '/getBoxDisciplines/' + encodeURIComponent(disciplinaSelecionada || 'default');
            },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
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
    })
        .on('select2:select', (e) => {
            boxSelecionado = String(e.params.data.id || '');
            const ready = !!(disciplinaSelecionada && boxSelecionado);
            $turmaSelect.prop('disabled', !ready).val(null).trigger('change.select2');
        })

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
                term: params.term || '',                          // busca digitada
                disciplina: $disciplineSelect.val() || '',        // pega direto do DOM
                box: $boxSelect.val() || ''                       // idem
            }),
            processResults: data => ({
                results: (Array.isArray(data) ? data : []).map(t => ({ id: t, text: t }))
            })
        }
    });


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

    // 1) Rehidrata a disciplina e o box com os valores do edit
    const discInitId = $disciplineSelect.data('initial-id');
    const discInitText = $disciplineSelect.data('initial-text');
    if (discInitId && discInitText) {
        hydrateSelect2($disciplineSelect, discInitId, discInitText);
    }

    const boxInitId = $boxSelect.data('initial-id');
    const boxInitText = $boxSelect.data('initial-text');
    if (boxInitId && boxInitText) {
        hydrateSelect2($boxSelect, boxInitId, boxInitText);
    }

    // Foco automático na busca quando abrir
    $disciplineSelect.on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });
});

const hrIni = document.getElementById('hr_ini');
const hrFim = document.getElementById('hr_fim');

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

const all = getAllTimeBoxes();

function disableAllHorarios() {
    all.forEach(({ input }) => {
        input.checked = false;
        input.disabled = true;
    });
    if (hrIni) hrIni.value = '';
    if (hrFim) hrFim.value = '';
}

function enableHorariosFrom(items) {
    // items esperado: [{inicio: "07:30", fim: "08:15"}, ...]
    const allowed = new Set(items.map(i => normalize(i.inicio)));

    all.forEach(({ input, norm }) => {
        if (allowed.has(norm)) {
            input.disabled = false;        // habilita
            // input.checked = true;       // (opcional) já marcar
        } else {
            input.checked = false;
            input.disabled = true;
        }
    });
}

const toMinutes = hm => { const [h, m] = String(hm).split(':').map(Number); return h * 60 + m; };
const padHHMM = hm => { const [h, m] = String(hm).split(':'); return `${String(h).padStart(2, '0')}:${m}`; };

function updateHrBoundsFrom(items) {
    if (!items || !items.length) {
        if (hrIni) hrIni.value = '';
        if (hrFim) hrFim.value = '';
        return;
    }
    // pega todos os inícios permitidos
    const starts = items.map(i => normalize(i.inicio)).sort((a, b) => toMinutes(a) - toMinutes(b));
    const first = starts[0];
    const last = starts[starts.length - 1];

    if (hrIni) hrIni.value = padHHMM(first);

    // tenta usar o fim do último slot; se não vier, usa o próprio last
    const lastObj = items.find(i => normalize(i.inicio) === last);
    if (hrFim) hrFim.value = padHHMM(normalize(lastObj?.fim || last));
}

function reloadHorarios(disc, turma, dataISO) {
    if (!disc || !turma || !dataISO) {
        disableAllHorarios();
        return;
    }

    disableAllHorarios(); // trava tudo enquanto carrega

    fetch(`/odontologia/horarios/${encodeURIComponent(disc)}/${encodeURIComponent(turma)}/${encodeURIComponent(dataISO)}`)
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(items => {
            enableHorariosFrom(items);
            updateHrBoundsFrom(items);
        })
        .catch(() => {
            disableAllHorarios();
        });
}


$(document).ready(function () {
    $('#selectPatient').select2({
        placeholder: "Selecione o paciente por nome ou CPF",
        allowClear: true,
        ajax: {
            url: '/getPacientes',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    query: params.term
                };
            },
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
});

const pagto = document.getElementById('pagto');
const valor = document.getElementById('valor');

pagto.addEventListener('change', function () {
    if (pagto.value === 'S') {
        valor.disabled = false;
    } else {
        valor.disabled = true;
    }
})

$(document).ready(function () {
    const { pacienteId, nomePaciente, idAgendamento } = window.agendaData || {};

    if (pacienteId && nomePaciente) {
        const option = new Option(nomePaciente, pacienteId, true, true);
        $('#selectPatient').append(option).trigger('change');

        $(option).attr('data-id-agendamento', idAgendamento);
        $('#selectPatient').append(option).trigger('change');
    }
})