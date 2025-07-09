import { createNavBar } from './navbar.js';

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
        events: '/odontologia/agendamentos',
        eventDidMount: function (info) {
            // Mostra as observações no tooltip
            info.el.setAttribute('title', info.event.extendedProps.observacoes || '');
            info.el.setAttribute('color', info.event.extendedProps.color || '');
        },
        eventClick: function (info) {
            Swal.fire({
                title: 'Detalhes do Agendamento',
                html: `
            <strong>Paciente:</strong> ${info.event.title}<br>
            <strong>Observações:</strong> ${info.event.extendedProps.observacoes || 'Sem observações'}<br>
            <strong>Status:</strong> ${info.event.extendedProps.status === '0' ? 'Agendado/Pendente' :
                        info.event.extendedProps.status === '1' ? 'Agendado/Presente' :
                            info.event.extendedProps.status === '2' ? 'Cancelado' :
                                info.event.extendedProps.status === '3' ? 'Finalizado' :
                                    'Sem observações'
                    }<br><br>
            <label for="new-status">Alterar status:</label>
            <select id="new-status" class="swal2-select">
                <option value="0">Agendado/Pendente</option>
                <option value="1">Agendado/Presente</option>
                <option value="2">Cancelado</option>
                <option value="3">Finalizado</option>
            </select>
        `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const newStatus = $('#new-status').val();
                    return fetch(`/alterarstatus/${info.event.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: newStatus })
                    }).then(res => {
                        if (!res.ok) throw new Error("Erro ao atualizar status");
                        return res.json();
                    });
                }
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire('Atualizado!', 'O status foi alterado.', 'success');
                    calendar.refetchEvents(); // Use API correta do FullCalendar
                }
            });
        }

    });

    calendar.render();
});