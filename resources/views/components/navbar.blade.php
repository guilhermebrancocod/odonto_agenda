<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

{{-- Bootsrap Icons --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

{{-- Bootstrap CSS --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous">
</script>

<style>
    body {
        font-family: "Montserrat", sans-serif;
    }

    :root {
        --blue-color: #2596be;
        --secondary-color: #7aacce;
        --third-color: #fc7c34;
        --light-color: #ecf5f9;
    }

    .link-agendar {
        background-color: var(--secondary-color);
        color: white;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .link-agendar:hover {
        background-color: var(--blue-color);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .link-agendar i {
        transition: transform 0.3s ease;
    }

    .link-agendar:hover i {
        transform: scale(1.2) rotate(-5deg);
    }

    .link-logout {
        background-color: var(--third-color);
        color: white;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .link-logout:hover {
        background-color: var(--third-color);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .link-logout i {
        transition: transform 0.3s ease;
    }

    .link-logout:hover i {
        transform: scale(1.2) rotate(-5deg);
    }

    @media (max-width: 991.98px) {
        #main-container {
            margin-top: 56px; /* ALTURA NAVBAR FIXA */
        }
    }

    @media (min-width: 992px) {
        #main-container {
            margin-top: 0;
        }
    }
</style>


<nav class="navbar navbar-dark bg-primary d-lg-none fixed-top shadow-sm px-3" style="height: 56px;">
    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
        <i class="fas fa-bars"></i>
    </button>
    <img src="{{ asset('faesa_branco.png') }}" alt="Logo FAESA" class="mx-auto d-block" style="width: 100px;">
</nav>


<div id="main-container" class="d-flex vh-100">

<!-- SIDEBAR DESKTOP -->
<nav class="p-3 d-none d-lg-flex flex-column align-items-center" style="width: 250px; background-color: var(--blue-color);">

    <!-- LOGO DA FAESA - NAVBAR -->
    <img src="{{ asset('faesa_branco.png') }}" alt="Logo" class="img-fluid mb-2" />

    <!-- TITULO SIDEBAR -->
    <h4 class="mb-5 mt-5 p-2 rounded-3"
        style="color: white;">
        <strong>Psicologia</strong>
    </h4>

    <ul class="list-group list-group-flush w-100 gap-1">
        <!-- LINKS -->

        <!-- PÁGINA INICIAL - MENU AGENDA -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-home"></i> Início
            </a>
        </li>


        <!-- INCLUIR AGENDAMENTO -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/criar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-calendar-plus"></i> Criar Agenda
            </a>
        </li>


        <!-- CONSULTAR AGENDA -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/consultar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-edit"></i> Agendas
            </a>
        </li>


        <!-- CADASTRAR PACIENTE -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/criar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-user-plus"></i> Criar Paciente
            </a>
        </li>

        
        <!-- CONSULTAR PACIENTE -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/consultar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-users"></i> Pacientes
            </a>
        </li>


        <!-- CADASTRAR SERVIÇO -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/criar-servico" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="bi bi-hammer"></i>Serviços
            </a>
        </li>


        <!-- CADASTRAR SALA -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/criar-sala" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="bi bi-door-open"></i> Salas
            </a>
        </li>


        <!-- RELATÓRIO -->
        <li class="list-group-item rounded-1 p-0 overflow-hidden ">
            <a href="/psicologia/relatorios-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                <i class="fas fa-chart-bar"></i> Relatório
            </a>
        </li>


        <!-- LOGOUT -->
        <li class="list-group-item mt-auto rounded-1 p-0 overflow-hidden ">
            <a href="/logout" class="link-logout d-flex align-items-center gap-2 p-2">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </li>


    </ul>
</nav>

<!-- OFFCANVAS MOBILE - ESQUERDA PARA DIREITA -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
    <div class="offcanvas-header" style="background-color: var(--blue-color);">
        <h5 class="offcanvas-title text-white" id="offcanvasMenuLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body p-0" style="background-color: var(--light-color);">
        <ul class="list-group list-group-flush w-100">
            <!-- MESMOS LINKS DO SIDEBAR -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-home"></i> Início
                </a>
            </li>
            <!-- INCLUIR AGENDAMENTO -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/criar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-calendar-plus"></i> Incluir Agendamento
                </a>
            </li>
            <!-- CONSULTAR AGENDAMENTO -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/consultar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-edit"></i> Consultar Agenda
                </a>
            </li>
            <!-- CADASTRAR PACIENTE -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/criar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-user-plus"></i> Cadastrar Paciente
                </a>
            </li>
            <!-- CONSULTAR PACIENTE -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/consultar-paciente" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-users"></i> Pacientes
                </a>
            </li>
            <!-- CADASTRAR SERVIÇO -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/criar-servico" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="bi bi-hammer"></i> Cadastrar Serviço
                </a>
            </li>
            <!-- RELATÓRIO -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/relatorios-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-chart-bar"></i> Relatório
                </a>
            </li>
            <!-- LOGOUT -->
            <li class="list-group-item p-0 overflow-hidden ">
                <a href="/psicologia/logout" class="link-logout d-flex align-items-center gap-2 p-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>