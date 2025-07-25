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

            // Define a cor de fundo com base no status
            let status = info.event.extendedProps.status;

            let color = '#6c757d'; // cor padrão (cinza)
            if (status === 'Agendado') {
                color = '#1d8ae9ff'; // azul
            } else if (status === 'Presente') {
                color = '#36e25eff'; // verde
            } else if (status === 'Cancelado') {
                color = '#e91b1bff'; // laranja
            }

            info.el.style.backgroundColor = color;
        },
        eventClick: async function (info) {
                try {
                    const statusAtual = info.event.extendedProps.status || 'Agendado';

                    const result = await Swal.fire({
                        title: 'Detalhes do Agendamento',
                        html: `
                    <div style="text-align: left; font-size: 16px;">
                        <p><strong>Paciente:</strong> ${info.event.title}</p>
                        <p><strong>Observações:</strong> ${info.event.extendedProps.observacoes || 'Sem observações'}</p>
                        <p><strong>Status:</strong>
                            <span style="color: ${statusAtual === 'Agendado' ? '#007bff' :
                                statusAtual === 'Presente' ? '#28a745' :
                                    statusAtual === 'Cancelado' ? '#dc3545' :
                                        '#6c757d'
                            }">
                                ${statusAtual}
                            </span>
                        </p>
                        <div style="margin-top: 15px;">
                            <label for="new-status" style="font-weight: bold;">Alterar status:</label>
                            <select id="new-status" class="swal2-select" style="width: 20%; margin-top: 5px;">
                                <option value="Agendado" ${statusAtual === 'Agendado' ? 'selected' : ''}>Agendado</option>
                                <option value="Presente" ${statusAtual === 'Presente' ? 'selected' : ''}>Presente</option>
                                <option value="Falta com justificativa" ${statusAtual === 'Falta com justificativa' ? 'selected' : ''}>Falta com justificativa</option>
                                <option value="Falta sem justificativa" ${statusAtual === 'Falta sem justificativa' ? 'selected' : ''}>Falta sem justificativa</option>
                                <option value="Cancelado" ${statusAtual === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                            </select>
                        </div>
                        <div style="margin-top: 15px;">
                            <label for="mensagem" style="font-weight: bold;">Mensagem:</label><br>
                            <textarea id="mensagem" class="swal2-textarea" placeholder="Anotação..." style="width: 80%; height: 80px;"></textarea>
                        </div>
                    </div>
                `,
                    icon: 'info',
                    customClass: {
                        popup: 'swal2-popup-clinica'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const novoStatus = document.getElementById('new-status')?.value;
                        const mensagem = document.getElementById('mensagem')?.value;
                        return { novoStatus, mensagem };
                    }
                });

                if (result.isConfirmed) {
                    const { novoStatus, mensagem } = result.value;
                    console.log('Novo status:', novoStatus);
                    console.log('Mensagem:', mensagem);

                }
            } catch (error) {
                console.error('Erro ao abrir modal:', error);
                Swal.fire('Erro', 'Houve um problema ao exibir os detalhes.', 'error');
            }
        }

    });
    calendar.render();
});