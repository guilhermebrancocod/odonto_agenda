<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootsrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous">
    </script>
</head >
    
<body>
    
    @include('components.navbar')

    <!-- CALENDÁRIO -->
    <div id="calendar" class="flex-grow-1 p-3 overflow-auto"></div>

    <!-- Modal para detalhes do agendamento -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1" aria-labelledby="agendamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="agendamentoModalLabel">Detalhes do Agendamento</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
            <p><strong>Paciente:</strong> <span id="modalPaciente"></span></p>
            <p><strong>Data e Horário:</strong> <span id="modalDataHora"></span></p>
            <p><strong>Serviço:</strong> <span id="modalServico"></span></p>
            <div class="mb-3">
            <label for="modalStatusSelect" class="form-label"><strong>Status:</strong></label>
            <select class="form-select" id="modalStatusSelect">
                <option value="Agendado">Agendado</option>
                <option value="Presente">Presente</option>
                <option value="Cancelado">Cancelado</option>
                <option value="Finalizado">Finalizado</option>
            </select>
            </div>
            <p><strong>Local:</strong> <span id="modalLocal"></span></p>
            <p><strong>Observações:</strong> <span id="modalObservacoes"></span></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btnSalvarStatus">Salvar Status</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
        </div>
    </div>
    </div>
</body >

<!-- FULLCALENDAR SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const calendarEl = document.getElementById("calendar");

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            timeZone: 'local',
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridDay,timeGridWeek,dayGridMonth",
            },
            slotMinTime: "08:00:00",
            slotMaxTime: "20:30:00",
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: "08:00",
                endTime: "20:30",
            },
            buttonText: {
                today: "Hoje",
                month: "Mês",
                week: "Semana",
                day: "Dia",
                list: "Lista",
            },
            locale: "pt-br",
            selectable: true,
            editable: false,
            select: function (info) {
                alert("Selecionado de " + info.startStr + " até " + info.endStr);
            },
            eventDidMount: function(info) {
                // Aplica a cor de fundo ao evento
                info.el.style.backgroundColor = info.event.backgroundColor;
                info.el.style.borderColor = info.event.backgroundColor;
                info.el.style.color = 'white'; // ou outra cor que fique legível
            },
            events: '/psicologia/agendamentos-calendar',

            eventClick: function(info) {
                const event = info.event;
                const start = event.start;
                const end = event.end;
                const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                const dataHoraStr = start.toLocaleString('pt-BR', options) + " - " + (end ? end.toLocaleString('pt-BR', options) : '');

                document.getElementById('modalPaciente').textContent = event.title;
                document.getElementById('modalDataHora').textContent = dataHoraStr;
                document.getElementById('modalObservacoes').textContent = event.extendedProps.description || 'Nenhuma observação';
                document.getElementById('modalStatusSelect').value = event.extendedProps.status || 'Agendado';
                document.getElementById('modalLocal').textContent = event.extendedProps.local || 'Não informado';
                document.getElementById('modalServico').textContent = event.extendedProps.servico || 'Não informado';

                const modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                document.getElementById('btnSalvarStatus').setAttribute('data-event-id', event.id);
                modal.show();
            }
        });

        calendar.render();

        document.getElementById('btnSalvarStatus').addEventListener('click', function () {
            const eventId = this.getAttribute('data-event-id');
            const novoStatus = document.getElementById('modalStatusSelect').value;

            fetch(`/psicologia/agendamentos/${eventId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: novoStatus })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao atualizar status.');
                }
                return response.json();
            })
            .then(data => {
                // Atualiza o evento visualmente (força refetch)
                calendar.refetchEvents();
                bootstrap.Modal.getInstance(document.getElementById('agendamentoModal')).hide();
            })
            .catch(error => {
                alert('Erro: ' + error.message);
            });
        });

    });
</script>

</html>
