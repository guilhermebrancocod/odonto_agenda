<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
</head >
    
<body>
    
    @include('components.navbar')

    <!-- CALENDÁRIO -->
    <div id="calendar" class="flex-grow-1 p-3 overflow-auto"></div>

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
            slotMaxTime: "19:00:00",
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: "08:00",
                endTime: "18:00",
            },
            eventDidMount: function (info) {
                info.el.setAttribute("title", info.event.extendedProps.description);
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
            events: [
                {
                    title: "Consulta Odontologia",
                    start: new Date().toISOString().split("T")[0],
                    color: "#007bff",
                },
                {
                    title: "Reunião de Equipe",
                    start: new Date(new Date().setDate(new Date().getDate() + 2)).toISOString().split("T")[0],
                    end: new Date(new Date().setDate(new Date().getDate() + 4)).toISOString().split("T")[0],
                    color: "#28a745",
                },
                {
                    title: "Avaliação com Paciente",
                    start: new Date(new Date().setHours(10, 0, 0, 0)).toISOString(),
                    end: new Date(new Date().setHours(11, 0, 0, 0)).toISOString(),
                    allDay: false,
                    color: "#ffc107",
                },
            ],
        });
        calendar.render();
    });
</script>

</html>
