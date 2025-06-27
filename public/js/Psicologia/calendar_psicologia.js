import { createNavBar } from './navbar_psicologia.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

document.addEventListener('DOMContentLoaded', function () {

    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Visão inicial: mês
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridDay,timeGridWeek,dayGridMonth'
        },
        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime: '08:00',
            endTime: '18:00',
        },
        eventDidMount: function (info) {
            info.el.setAttribute('title', info.event.extendedProps.description);
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        locale: 'pt-br',
        selectable: true,
        editable: false,
        selectable: true,
        select: function (info) {
            alert('Selecionado de ' + info.startStr + ' até ' + info.endStr);
        },
        events: [
            {
                title: 'Consulta Odontologia',
                start: new Date().toISOString().split('T')[0],
                color: '#007bff'
            },
            {
                title: 'Reunião de Equipe',
                start: new Date(new Date().setDate(new Date().getDate() + 2)).toISOString().split('T')[0],
                end: new Date(new Date().setDate(new Date().getDate() + 4)).toISOString().split('T')[0],
                color: '#28a745'
            },
            {
                title: 'Avaliação com Paciente',
                start: new Date(new Date().setHours(10, 0, 0, 0)).toISOString(),
                end: new Date(new Date().setHours(11, 0, 0, 0)).toISOString(),
                allDay: false,
                color: '#ffc107'
            },
        ]
    });

    calendar.render();
});