import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';

$('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    language: 'pt-BR',
    autoclose: true,
    todayHighlight: true
});

$(document).ready(function () {
    // Inicializa os dois timepickers
    $('#hr_ini, #hr_fim').timepicker({
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
    $.fn.select2.defaults.set("language", "pt-BR");

    $('#selectPatient').select2({
        placeholder: "Busque o paciente por nome ou CPF",
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function (args) {
                const remainingChars = args.minimum - args.input.length;
                return `Digite pelo menos mais ${remainingChars} caractere${remainingChars > 1 ? 's' : ''}...`;
            }
        }
    });
});

$(document).ready(function () {
    $('#selectPatient').select2({
        placeholder: "Busque o paciente por nome ou CPF",
        allowClear: true,
        minimumInputLength: 2,
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

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

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