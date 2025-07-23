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
                // Busca os locais (boxes) do serviço
                const response = await fetch(`/odontologia/boxeservicos/${info.event.extendedProps.servicoId}`);
                const boxes = await response.json();

                // Monta as opções do select
                let boxOptions = '';
                boxes.forEach(box => {
                    boxOptions += `<option value="${box.ID_BOX_CLINICA}">${box.DESCRICAO}</option>`;
                });

                // Exibe o alerta com o select de locais
                Swal.fire({
                    title: '🦷 Detalhes do Agendamento',
                    html: `
        <div style="text-align: left; font-size: 16px;">
            <p><strong>Paciente:</strong> ${info.event.title}</p>
            <p><strong>Observações:</strong> ${info.event.extendedProps.observacoes || 'Sem observações'}</p>
            <p><strong>Status:</strong>
                <span style="color: ${info.event.extendedProps.status === 'Agendado' ? '#007bff' :
                            info.event.extendedProps.status === 'Presente' ? '#28a745' :
                                info.event.extendedProps.status === 'Cancelado' ? '#dc3545' :
                                    '#6c757d'}">
                    ${info.event.extendedProps.status || 'Não informado'}
                </span>
            </p>
            <div style="margin-top: 15px;">
                <label for="box-select" style="font-weight: bold;">Selecionar Local (Box):</label><br>
                <select id="box-select" class="swal2-select" style="width: 40%; margin-top: 5px;">
                    ${boxOptions}
                </select>
            </div>
            <div style="margin-top: 15px;">
                <label for="new-status" style="font-weight: bold;">Alterar status:</label><br>
                <select id="new-status" class="swal2-select" style="width: 40%; margin-top: 5px;">
                    <option value="Agendado">Agendado</option>
                    <option value="Presente">Presente</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
            </div>
        </div>
    `,
                    icon: 'info',
                    customClass: {
                        popup: 'swal2-popup-clinica'
                    },
                    showCancelButton: true,
                    confirmButtonText: '💾 Confirmar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const newStatus = document.getElementById('new-status').value;
                        const selectedBox = document.getElementById('box-select').value;

                        return fetch(`/alterarstatus/${info.event.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                status: newStatus,
                                box: selectedBox
                            })
                        }).then(res => {
                            if (!res.ok) throw new Error('Erro ao atualizar');
                            return res.json();
                        });
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        Swal.fire('Atualizado!', 'Status e local atualizados com sucesso.', 'success');
                        calendar.refetchEvents();
                    }
                });

            } catch (error) {
                console.error('Erro ao buscar boxes:', error);
                Swal.fire('Erro', 'Falha ao buscar os locais.', 'error');
            }
        }
    });

    calendar.render();
});