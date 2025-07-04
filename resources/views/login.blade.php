<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }

        :root {
            --blue-color: #085ca4;
            --secondary-color: #7aacce;
            --third-color: #fc7c34;
            --light-color: #ecf5f9;
        }

        .link-agendar {
            background-color: var(--secondary-color);
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            border-left: 4px solid transparent;
        }

        .link-agendar:hover {
            background-color: var(--third-color);
            color: white;
            transform: translateX(4px);
            border-left: 4px solid white;
        }

        .link-agendar i {
            transition: transform 0.3s ease;
        }

        .link-agendar:hover i {
            transform: scale(1.2) rotate(-5deg);
        }

        .link-logout {
            background-color: #c0392b;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease, border-left 0.2s ease;
            border-left: 4px solid transparent;
        }

        .link-logout:hover {
            background-color: #e74c3c;
            color: white;
            transform: translateX(4px);
            border-left: 4px solid white;
        }

        .link-logout i {
            transition: transform 0.3s ease;
        }

        .link-logout:hover i {
            transform: scale(1.2) rotate(-5deg);
        }
    </style>
</head>

<body>
    <div class="d-flex vh-100">

        <!-- SIDEBAR DESKTOP -->
        <nav class="p-3 d-none d-lg-flex flex-column align-items-center" style="width: 250px; background-color: var(--blue-color);">
            <img src="{{ asset('faesa.png') }}" alt="Logo" class="img-fluid mb-2">
            <h4 class="mb-5 p-2 rounded-3 text-black-emphasis" style="background-color: var(--secondary-color);">Psicologia</h4>
            <ul class="list-group list-group-flush w-100 gap-1">
                <!-- LINKS -->
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-home"></i> Início
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/criar-agenda" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-calendar-plus"></i> Incluir Agendamento
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/consultar-agenda" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-edit"></i> Consultar Agenda
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/criar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-user-plus"></i> Cadastrar Paciente
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/consultar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-users"></i> Consultar Paciente
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/criar-servico" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="bi bi-hammer"></i> Cadastrar Serviço
                    </a>
                </li>
                <li class="list-group-item rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/relatorio" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-chart-bar"></i> Relatório
                    </a>
                </li>
                <li class="list-group-item mt-auto rounded-1 p-0 overflow-hidden border border-1 border-black">
                    <a href="/psicologia/logout" class="link-logout d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- BOTÃO OFFCANVAS MOBILE -->
        <button class="btn btn-primary position-absolute m-2 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu">
            <i class="fas fa-bars"></i>
        </button>

        <!-- OFFCANVAS MOBILE -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
            <div class="offcanvas-header" style="background-color: var(--blue-color);">
                <h5 class="offcanvas-title text-white" id="offcanvasMenuLabel">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
            </div>
            <div class="offcanvas-body p-0" style="background-color: var(--light-color);">
                <ul class="list-group list-group-flush w-100">
                    <!-- MESMOS LINKS DO SIDEBAR -->
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/criar-agenda" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-calendar-plus"></i> Incluir Agendamento
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/consultar-agenda" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-edit"></i> Consultar Agenda
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/criar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-user-plus"></i> Cadastrar Paciente
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/consultar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-users"></i> Consultar Paciente
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/criar-servico" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="bi bi-hammer"></i> Cadastrar Serviço
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/relatorio" class="link-agendar d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-chart-bar"></i> Relatório
                        </a>
                    </li>
                    <li class="list-group-item p-0 overflow-hidden border border-1 border-black">
                        <a href="/psicologia/logout" class="link-logout d-flex align-items-center gap-2 p-2">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- CALENDÁRIO -->
        <div id="calendar" class="flex-grow-1 p-3 overflow-auto"></div>
    </div>

    <!-- FULLCALENDAR SCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
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
    </script>
</body>

</html>
