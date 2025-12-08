document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    function fmt(d) { const y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0'); return `${y}-${m}-${day}` }

    let lastRange = { ini: null, fimIncl: null };


    function abrirModalSemAgendamento() {
        if (!lastRange.ini || !lastRange.fimIncl) return;

        const periodoTxt = `Período: ${lastRange.ini} → ${lastRange.fimIncl}`;
        Swal.fire({
            title: 'Alunos sem agendamento',
            html: `
      <div style="text-align:left">
        <div class="text-muted" style="margin-bottom:.5rem">${periodoTxt}</div>
        <div id="modal-sem-ag-body" style="max-height:60vh; overflow:auto">Carregando…</div>
      </div>
    `,
            width: 800,
            showConfirmButton: true,
            confirmButtonText: 'Fechar',
        });

        // carrega dados e injeta na div do modal
        const bodyEl = document.getElementById('modal-sem-ag-body');
        const disciplina = document.querySelector('[name="disciplina-filtro"]')?.value || '';
        const turma = document.querySelector('[name="turma-filtro"]')?.value || '';
        const box = document.querySelector('[name="box-filtro"]')?.value || '';

        const params = new URLSearchParams({
            ini: lastRange.ini,
            fim: lastRange.fimIncl,
            disciplina, turma, box,
            limit: '200', offset: '0'
        });

        fetch(`/odontologia/agendamentos/alunos-sem-agendamento?${params.toString()}`)
            .then(r => { if (!r.ok) throw new Error('HTTP'); return r.json(); })
            .then(rows => {
                if (!Array.isArray(rows) || rows.length === 0) {
                    bodyEl.innerHTML = `<div class="text-muted">Nenhum aluno pendente.</div>`;
                    return;
                }

                // monta a URL para a tela de criação de agendamento
                const mkUrl = (r) => {
                    const p = new URLSearchParams({
                        aluno: r.ALUNO ?? '',
                        disciplina: r.DISCIPLINA ?? '',
                        turma: r.TURMA ?? '',
                        box: r.BOX ?? '',
                        dia_semana: r.DIA_SEMANA ?? '',
                        hr_inicio: r.HR_INICIO ?? '',
                        hr_fim: r.HR_FIM ?? ''
                    });
                    // ajuste o path abaixo conforme sua rota
                    return `/odontologia/agendamentos/criar?${p.toString()}`;
                };

                const tdTime = v => (v ?? '').toString().slice(0, 5);
                const diaSemana = v => ({
                    '1': 'Seg',
                    '2': 'Ter',
                    '3': 'Qua',
                    '4': 'Qui',
                    '5': 'Sex',
                    '6': 'Sáb',
                    '7': 'Dom'
                }[v] ?? '');

                const table = `
                <div class="table-responsive" style="font-size: small">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                    <tr class="col-2">
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>Disciplina</th>
                        <th>Turma</th>
                        <th>Dia</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <!--<!00<th class="text-end col-2" style="width: 10%;">Agendar</th>-->
                    </tr>
                    </thead>
                    <tbody>
                    ${rows.map(r => `
                        <tr>
                        <td>${r.ALUNO ?? ''}</td>
                        <td>${r.NOME_COMPL ?? ''}</td>
                        <td>${r.DISCIPLINA ?? ''}</td>
                        <td>${r.TURMA ?? ''}</td>
                        <td>${diaSemana(r.DIA_SEMANA) ?? ''}</td>
                        <td>${tdTime(r.HR_INICIO)}</td>
                        <td>${tdTime(r.HR_FIM)}</td>
                        <!--<td class="text-end">
                            <a class="btn btn-sm btn-primary" href="${mkUrl(r)}" title="Criar agendamento">
                            <i class="fa-solid fa-calendar-plus fa-xs"></i> Agendar
                            </a>
                        </td>-->
                        </tr>
                    `).join('')}
                    </tbody>
                </table>
                </div>
                `;

                bodyEl.innerHTML = table;
            })

            .catch(() => {
                bodyEl.innerHTML = `<div class="text-danger">Erro ao carregar relatório.</div>`;
            });
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek', // Visão inicial: mês
        headerToolbar: {
            left: 'prev,next today semAgBtn',
            center: 'title',
            right: 'dayGridDay,timeGridWeek,dayGridMonth,listWeek'
        },
        customButtons: {
            semAgBtn: {
                text: 'Alunos sem agendamento',
                click: abrirModalSemAgendamento
            }
        },
        datesSet: ({ start, end }) => {
            const endIncl = new Date(end.getTime() - 24 * 60 * 60 * 1000);
            lastRange = { ini: fmt(start), fimIncl: fmt(endIncl) };
        },
        slotMinTime: "08:00:00",
        slotMaxTime: "19:00:00",
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5, 6],
            startTime: '08:00',
            endTime: '18:00',
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        eventContent(arg) {
            const e = arg.event;
            const turma = e.extendedProps?.turma || e.extendedProps?.TURMA || '';
            const local = e.extendedProps?.local || 'Sem local';
            const status = e.extendedProps?.status || '';

            if (arg.view.type.startsWith('listWeek')) {
                const e = arg.event;
                const turma = e.extendedProps?.turma || e.extendedProps?.TURMA || '';
                const local = e.extendedProps?.local || 'Sem local';
                const alunos = e.extendedProps?.NOME_ALUNO || [];
                const status = e.extendedProps?.status || '';

                const el = document.createElement('div');
                el.innerHTML = `
                    <div style="display:flex;flex-direction:column;gap:2px;">
                    <strong>${e.title || ''}</strong>
                    <small>${local}${turma ? ' • Turma ' + turma : ''}${status ? ' • ' + status : ''}${alunos?.length ? ' • Aluno ' + alunos.join(', ') : ''}</small>
                    </div>
                `;
                return { domNodes: [el] };
            } else {
                const root = document.createElement('div');
                root.style.display = 'flex';
                root.style.flexDirection = 'column';
                root.style.gap = '2px';
                root.innerHTML = `
                <div style="font-size: 10px"><strong>${e.title || ''}</strong></div>
                <div style="font-size:9px;opacity:.85;">
                    ${local}${turma ? ' • Turma ' + turma : ''}
                </div>`;
                return { domNodes: [root] };
            }
        },
        locale: 'pt-br',
        allDaySlot: false,
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
                        <p><strong>Aluno:</strong> ${info.event.extendedProps.NOME_ALUNO?.join(', ') || 'Sem aluno definido'}</p>
                        <p><strong>Status:</strong>
                            <span 
                            style="color: ${statusAtual === 'Agendado' ? '#007bff' :
                            statusAtual === 'Presente' ? '#28a745' :
                                statusAtual === 'Falta com justificativa' ? '#dbd81fff' :
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
                                <label for="mensagem" style="font-weight: bold;">Observações:</label><br>
                                <textarea id="mensagem" class="swal2-textarea" placeholder="Digite aqui..." style="width: 80%; height: 80px;">${info.event.extendedProps.mensagem || ''}</textarea>

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

    });
    calendar.render();
});