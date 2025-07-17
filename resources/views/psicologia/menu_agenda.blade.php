<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

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
            <p><strong>Observações:</strong> <span id="modalObservacoes"></span></p>
            <!-- Adicione mais campos se quiser -->
        </div>
        <div class="modal-footer">
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
            events: '/psicologia/agendamentos-calendar',

            eventClick: function(info) {
                document.getElementById('modalPaciente').textContent = info.event.title;

                // Formatar a data/hora para mostrar bonitinho:
                const start = info.event.start;
                const end = info.event.end;
                const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour:'2-digit', minute:'2-digit' };
                const dataHoraStr = start.toLocaleString('pt-BR', options) + " - " + (end ? end.toLocaleString('pt-BR', options) : '');

                document.getElementById('modalDataHora').textContent = dataHoraStr;
                document.getElementById('modalObservacoes').textContent = info.event.extendedProps.description || 'Nenhuma observação';

                // Abre o modal com Bootstrap 5
                const agendamentoModal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                agendamentoModal.show();
            }
        });

        calendar.render();
    });
</script>

</html>
