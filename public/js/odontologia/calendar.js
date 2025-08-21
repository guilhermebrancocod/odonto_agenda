

/*const calendar = new FullCalendar.Calendar(calendarEl, {
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
                <p><strong>Local:</strong> ${info.event.extendedProps.local || 'Sem local definido'}</p>
                <p><strong>Observações:</strong> ${info.event.extendedProps.observacoes || 'Sem observações'}</p>
                <p><strong>Status:</strong>
                    <span style="color: ${statusAtual === 'Agendado' ? '#007bff' :
                        statusAtual === 'Presente' ? '#28a745' :
                            statusAtual === 'Cancelado' ? '#dc3545' : '#6c757d'}">
                        ${statusAtual}
                    </span>
                </p>
                <div style="margin-top: 15px;">
                    <label for="new-status" style="font-weight: bold;">Alterar status:</label>
                    <select id="new-status" class="swal2-select custom-select" 
                        style="width: 20%; margin-top: 5px;margin-left:2px;
                        background-color: #f1f1f1;border: 1px solid #ccc;
                        color: #333; font-size: 14px; border-radius: 6px;
                        padding: 8px; min-width: 38%">
                        <option value="Agendado" ${statusAtual === 'Agendado' ? 'selected' : ''}>Agendado</option>
                        <option value="Presente" ${statusAtual === 'Presente' ? 'selected' : ''}>Presente</option>
                        <option value="Falta com justificativa" ${statusAtual === 'Falta com justificativa' ? 'selected' : ''}>Falta com justificativa</option>
                        <option value="Falta sem justificativa" ${statusAtual === 'Falta sem justificativa' ? 'selected' : ''}>Falta sem justificativa</option>
                        <option value="Cancelado" ${statusAtual === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                    </select>
                </div>
                <div style="margin-top: 15px;">
                    <label for="mensagem" style="font-weight: bold;">Mensagem:</label><br>
                    <textarea id="mensagem" class="swal2-textarea" placeholder="Anotação..." style="width: 80%; height: 80px;">${info.event.extendedProps.mensagem || ''}</textarea>

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

                    return fetch(`/alterarstatus/${info.event.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        body: JSON.stringify({ status: novoStatus, mensagem })
                    }).then(res => {
                        if (!res.ok) throw new Error("Erro ao atualizar status");
                        return { novoStatus, mensagem };
                    });
                }
            });

            if (result.isConfirmed && result.value) {
                const { novoStatus, mensagem } = result.value;
                console.log('Novo status:', novoStatus);
                console.log('Mensagem:', mensagem);

                Swal.fire('Atualizado!', 'O status foi alterado com sucesso.', 'success');

                // Opcional: recarregar os eventos
                info.view.calendar.refetchEvents(); // método do FullCalendar v5+
            }

        } catch (error) {
            console.error('Erro ao abrir modal:', error);
            Swal.fire('Erro', 'Houve um problema ao exibir os detalhes.', 'error');
        }
    }

});*/

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const filtroBoxEl = document.getElementById('filtroBox');
    let filtroBox = '';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        // Visão inicial mais útil para dias cheios
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth,listDay'
        },
        locale: 'pt-br',
        selectable: true,
        editable: false,
        nowIndicator: true,
        allDaySlot: false,
        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime: '08:00',
            endTime: '18:00',
        },

        // Para a visão mensal, colapsar e abrir uma lista "ver mais"
        dayMaxEventRows: 3,
        moreLinkClick: function (info) {
            const items = info.allSegs.map(seg => {
                const e = seg.event;
                const st = e.extendedProps.status || '';
                const hora = e.start
                    ? e.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                    : '';
                return `<li style="margin:6px 0">
          <b>${e.title}</b> — ${hora}
          <br><small>${e.extendedProps.local || 'Sem local'} • ${st}</small>
        </li>`;
            }).join('');
            Swal.fire({
                title: `Agendamentos — ${info.date.toLocaleDateString()}`,
                html: `<ul style="text-align:left;max-height:50vh;overflow:auto;padding-left:18px">${items}</ul>`,
                width: 700,
            });
            return 'popover';
        },

        // Carregar por faixa de datas + filtro do Local/Box
        events: function (info, success, failure) {
            const url = `/odontologia/agendamentos?start=${info.startStr}&end=${info.endStr}&box=${encodeURIComponent(filtroBox || '')}`;
            fetch(url)
                .then(r => {
                    if (!r.ok) throw new Error('Falha ao carregar eventos');
                    return r.json();
                })
                .then(success)
                .catch(failure);
        },

        // Texto nos botões
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },

        // Seleção no calendário (mantido do seu modelo)
        select: function (info) {
            alert('Selecionado de ' + info.startStr + ' até ' + info.endStr);
        },

        // Renderização compacta de cada evento
        eventContent: function (arg) {
            const titulo = (arg.event.title || '');
            const [nome, proc] = titulo.split('|').map(s => (s || '').trim());
            const iniciais = (nome || '').split(' ').slice(0, 2).map(s => s[0]).join('').toUpperCase() || 'PT';
            const viewType = arg.view.type;

            const mostrarNomeCompleto = (viewType === 'timeGridDay' || viewType === 'listDay');

            const root = document.createElement('div');
            root.style.fontSize = '20px';
            root.style.fontWeight = 'bold';
            root.style.color = 'black';
            root.style.textShadow = '3px';
            root.style.lineHeight = '1.1';
            root.style.padding = '1px 2px';
            root.style.height = '30px';
            root.style.overflow = 'hidden';
            root.style.display = 'flex';
            root.style.alignItems = 'center';
            root.style.fontSize = '11px';
            root.style.lineHeight = '1.1';

            if (mostrarNomeCompleto) {
                root.style.fontFamily = '"sans-serif';
                root.style.fontSize = '20px';
            }

            root.innerHTML = `
                            <div style="display:flex;gap:6px;align-items:center">
                            <span class="fc-badge">${iniciais}</span>
                            <div class="fc-title">
                                ${mostrarNomeCompleto ? nome : proc || ''}
                            </div>
                                <div class="fc-time">${arg.timeText}</div>
                            </div>`;

            if(mostrarNomeCompleto)
            {
                root.innerHTML = `
                            <div style="display:flex;gap:6px;align-items:center">
                            <div class="fc-title">
                                ${mostrarNomeCompleto ? nome : proc || ''}
                            </div>
                                <div class="fc-time">${arg.timeText}</div>
                            </div>`;
            }
            return { domNodes: [root] };
        },

        // Tooltip + classes de status
        eventDidMount: function (info) {
            // tooltip com observações (mantém seu comportamento)
            info.el.setAttribute('title', info.event.extendedProps.observacoes || '');

            // mapeia status -> classe
            const st = info.event.extendedProps.status;
            info.el.classList.add(
                st === 'Agendado' ? 'ev-agendado' :
                    st === 'Presente' ? 'ev-presente' :
                        st === 'Cancelado' ? 'ev-cancelado' : 'ev-default'
            );
        },
        eventDidMount: function (info) {
            // Mostra as observações no tooltip
            info.el.setAttribute('title', info.event.extendedProps.observacoes || '');

            // Define a cor de fundo com base no status
            let status = info.event.extendedProps.status;

            let color = '#e6e1cd';
            if (status === 'Agendado') {
                color = '#68b2f8';
            } else if (status === 'Presente') {
                color = '#6e9167';
            } else if (status === 'Cancelado') {
                color = '#c21b12';
            }

            info.el.style.backgroundColor = color;
        },

        // Clique no evento (mantive seu SweetAlert e acrescentei feedback instantâneo)
        eventClick: async function (info) {
            try {
                const statusAtual = info.event.extendedProps.status || 'Agendado';

                const result = await Swal.fire({
                    title: 'Detalhes do Agendamento',
                    html: `
            <div style="text-align: left; font-size: 16px;">
                <p><strong>Paciente:</strong> ${info.event.title}</p>
                <p><strong>Local:</strong> ${info.event.extendedProps.local || 'Sem local definido'}</p>
                <p><strong>Observações:</strong> ${info.event.extendedProps.observacoes || 'Sem observações'}</p>
                <p><strong>Status:</strong>
                    <span style="color: ${statusAtual === 'Agendado' ? '#007bff' :
                            statusAtual === 'Presente' ? '#28a745' :
                                statusAtual === 'Cancelado' ? '#dc3545' : '#6c757d'}">
                        ${statusAtual}
                    </span>
                </p>
                <div style="margin-top: 15px;">
                    <label for="new-status" style="font-weight: bold;">Alterar status:</label>
                    <select id="new-status" class="swal2-select custom-select" 
                        style="width: 20%; margin-top: 5px;margin-left:2px;
                        background-color: #f1f1f1;border: 1px solid #ccc;
                        color: #333; font-size: 14px; border-radius: 6px;
                        padding: 8px; min-width: 38%">
                        <option value="Agendado" ${statusAtual === 'Agendado' ? 'selected' : ''}>Agendado</option>
                        <option value="Presente" ${statusAtual === 'Presente' ? 'selected' : ''}>Presente</option>
                        <option value="Falta com justificativa" ${statusAtual === 'Falta com justificativa' ? 'selected' : ''}>Falta com justificativa</option>
                        <option value="Falta sem justificativa" ${statusAtual === 'Falta sem justificativa' ? 'selected' : ''}>Falta sem justificativa</option>
                        <option value="Cancelado" ${statusAtual === 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                    </select>
                </div>
                <div style="margin-top: 15px;">
                    <label for="mensagem" style="font-weight: bold;">Mensagem:</label><br>
                    <textarea id="mensagem" class="swal2-textarea" placeholder="Anotação..." style="width: 80%; height: 80px;">${info.event.extendedProps.mensagem || ''}</textarea>
                </div>
            </div>
          `,
                    icon: 'info',
                    customClass: { popup: 'swal2-popup-clinica' },
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const novoStatus = document.getElementById('new-status')?.value;
                        const mensagem = document.getElementById('mensagem')?.value;

                        return fetch(`/alterarstatus/${info.event.id}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ status: novoStatus, mensagem })
                        }).then(res => {
                            if (!res.ok) throw new Error("Erro ao atualizar status");
                            return { novoStatus, mensagem };
                        });
                    }
                });

                if (result.isConfirmed && result.value) {
                    const { novoStatus, mensagem } = result.value;

                    // Feedback visual imediato sem precisar recarregar tudo:
                    info.el.classList.remove('ev-agendado', 'ev-presente', 'ev-cancelado', 'ev-default');
                    info.el.classList.add(
                        novoStatus === 'Agendado' ? 'ev-agendado' :
                            novoStatus === 'Presente' ? 'ev-presente' :
                                novoStatus === 'Cancelado' ? 'ev-cancelado' : 'ev-default'
                    );
                    info.event.setExtendedProp('status', novoStatus);
                    info.event.setExtendedProp('mensagem', mensagem);

                    Swal.fire('Atualizado!', 'O status foi alterado com sucesso.', 'success');

                    // Se seu backend também altera horários/locais, mantenha este refetch:
                    // info.view.calendar.refetchEvents();
                }

            } catch (error) {
                console.error('Erro ao abrir/atualizar modal:', error);
                Swal.fire('Erro', 'Houve um problema ao exibir/atualizar os detalhes.', 'error');
            }
        }
    });

    calendar.render();

    // Filtro por Local/Box
    filtroBoxEl.addEventListener('change', (e) => {
        filtroBox = e.target.value;
        calendar.refetchEvents();
    });

    calendar.render();

    filtroBoxEl.addEventListener('change', (e) => {
        filtroBox = e.target.value;
        calendar.refetchEvents();
    });
});