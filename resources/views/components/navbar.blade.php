<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

<style>
    body {
        font-family: "Montserrat", sans-serif;
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
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        /* se quiser borda, coloque aqui, ex: border: 4px solid transparent; */
    }

    .link-agendar:hover {
        background-color: var(--blue-color);
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        /* border: 4px solid white; */
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

    /* REMOVE MARGEM EM TELAS MAIORES */
    @media (min-width: 992px) {
        #main-container {
            margin-top: 0;
        }
    }
</style>


<nav class="navbar navbar-dark bg-primary d-lg-none fixed-top" style="height: 56px; padding-left: 1rem;">
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasMenu">
            <i class="fas fa-bars"></i>
        </button>
        <img src="{{ asset('faesa_branco.png') }}" id="faesa_icone_branco" alt="" style="margin: auto; display: block; width: 120px;">
        <span class="navbar-brand mb-0 h1 ms-2">Agendamento Psicologia</span>
    </nav>

    <div id="main-container" class="d-flex vh-100">

        <!-- SIDEBAR DESKTOP -->
        <nav class="p-3 d-none d-lg-flex flex-column align-items-center" style="width: 250px; background-color: var(--blue-color);">
            <img src="{{ asset('faesa_branco.png') }}" alt="Logo" class="img-fluid mb-2" />

            <!-- TITULO SIDEBAR -->
            <h4 class="mb-5 p-2 rounded-3 text-black-emphasis" style="background-color: var(--secondary-color);">
                <strong>Psicologia</strong>
            </h4>

            <ul class="list-group list-group-flush w-100 gap-1">
                <!-- LINKS -->
                <li class="list-group-item rounded-1 p-0 overflow-hidden ">
                    <a href="/psicologia/" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-home"></i> Início
                    </a>
                </li>
                <!-- INCLUIR AGENDAMENTO -->
                <li class="list-group-item rounded-1 p-0 overflow-hidden ">
                    <a href="/psicologia/criar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-calendar-plus"></i> Criar Agendamento
                    </a>
                </li>
                <!-- CONSULTAR AGENDA -->
                <li class="list-group-item rounded-1 p-0 overflow-hidden ">
                    <a href="/psicologia/consultar-agendamento" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="fas fa-edit"></i> Consultar Agenda
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
                        <i class="fas fa-users"></i> Consultar Paciente
                    </a>
                </li>
                <!-- CADASTRAR SERVIÇO -->
                <li class="list-group-item rounded-1 p-0 overflow-hidden ">
                    <a href="/psicologia/criar-servico" class="link-agendar d-flex align-items-center gap-2 p-2">
                        <i class="bi bi-hammer"></i> Criar Serviço
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
                            <i class="fas fa-users"></i> Consultar Paciente
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