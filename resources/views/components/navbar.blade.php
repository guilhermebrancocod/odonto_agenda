<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

{{-- SCRIPT DE CORREÇÃO DE LAYOUT (ANTI-FOUC) --}}
{{-- Este script roda imediatamente para evitar que o conteúdo sobreponha a navbar durante o carregamento --}}
<script>
    (function() {
        // Aplica a classe de margem principal ao corpo da página
        document.body.classList.add('has-sidebar');
        // Verifica o estado salvo no localStorage e aplica a classe de recolhimento, se necessário
        if (localStorage.getItem('sidebarState') === 'collapsed' && window.innerWidth >= 992) {
            document.body.classList.add('sidebar-collapsed');
        }
    })();
</script>

{{-- ================================================================= --}}
{{-- COMPONENTE DA SIDEBAR - VERSÃO FINAL COM TODAS AS CORREÇÕES       --}}
{{-- ================================================================= --}}

<style>
    :root {
        --sidebar-width: 250px;
        --sidebar-width-collapsed: 80px;
        --blue-color: #2596be;
        --secondary-color: #7aacce;
        --third-color: #fc7c34;
        --light-color: #ecf5f9;
    }
    body {
        font-family: "Montserrat", sans-serif;
        transition: margin-left 0.3s ease;
    }
    body.has-sidebar {
        margin-left: var(--sidebar-width);
    }
    body.sidebar-collapsed {
        margin-left: var(--sidebar-width-collapsed);
    }
    #mainNavbar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: var(--sidebar-width);
        background-color: var(--blue-color);
        transition: width 0.3s ease;
        overflow-x: hidden;
    }
    #mainNavbar.collapsed {
        width: var(--sidebar-width-collapsed);
    }
    .nav-link-custom {
        color: white;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .nav-link-custom:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .nav-link-custom.primary { background-color: var(--secondary-color); }
    .nav-link-custom.primary:hover { background-color: var(--blue-color); }
    .nav-link-custom.logout { background-color: var(--third-color); }
    .nav-link-custom.logout:hover { background-color: #e66a2e; }

    /* --- ESTILOS PARA O ESTADO RECOLHIDO --- */
    #logo-faesa {
        width: 150px;
        transition: opacity 0.3s ease-in-out, width 0.3s ease;
    }
    #mainNavbar.collapsed #logo-faesa {
        width: 50px;
    }
    #mainNavbar.collapsed .sidebar-title,
    #mainNavbar.collapsed .user-info {
        opacity: 0;
        visibility: hidden;
        height: 0;
        margin: 0 !important;
        padding: 0 !important;
        transition: opacity 0.1s ease, height 0.3s ease;
    }
    #mainNavbar.collapsed .nav-link-custom {
        justify-content: center;
    }
    #mainNavbar.collapsed .nav-link-text {
        opacity: 0;
        visibility: hidden;
        width: 0;
        white-space: nowrap;
        transition: opacity 0.1s ease, width 0.1s ease;
    }
    #mainNavbar.collapsed .nav-link-custom i {
        margin: 0;
    }
    /* --- FIM DOS ESTILOS PARA O ESTADO RECOLHIDO --- */

    @media (max-width: 991.98px) {
        #mainNavbar {
            display: none !important;
        }
        body.has-sidebar, body.sidebar-collapsed {
            margin-left: 0;
        }
        #page-header-container {
            display: none !important;
        }
        body {
            padding-top: 56px;
        }
    }
</style>

<nav class="navbar navbar-dark bg-primary d-lg-none fixed-top shadow-sm px-3" style="height: 56px">
    <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
        <i class="fas fa-bars"></i>
    </button>
    <img src="{{ asset('img/faesa_logo_expandido.png') }}" alt="Logo FAESA" class="mx-auto d-block" style="width: 100px;">
</nav>

<div class="d-none d-lg-block">
    <nav class="p-3 d-flex flex-column align-items-center shadow-lg" id="mainNavbar">
        <img src="{{ asset('img/faesa_logo_expandido.png') }}" alt="Logo" class="img-fluid mb-2" id="logo-faesa" />
        
        <div class="sidebar-title text-center text-white mb-2 mt-3">
            <h5 class="mb-1"><strong>Clínica de Psicologia</strong></h5>
            <p class="p-0 m-0" style="font-size: 12px;"><em>Administrador</em></p>
        </div>
        <div class="user-info text-center" style="color:#ecf5f9">
            @if(session()->has('usuario'))
                <p>{{ session('usuario')['ID_USUARIO_CLINICA'] }}</p>
            @endif
        </div>
        <ul class="list-group list-group-flush w-100 gap-1 mt-3">
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-home"></i><span class="nav-link-text"> Início</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-calendar-plus"></i><span class="nav-link-text"> Criar Agenda</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/consultar-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-edit"></i><span class="nav-link-text"> Agendas</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-paciente" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-person-add"></i><span class="nav-link-text"> Criar Paciente</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/consultar-paciente" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-people"></i><span class="nav-link-text"> Pacientes</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-servico" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-gear"></i><span class="nav-link-text"> Serviços</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-sala" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-door-open"></i><span class="nav-link-text"> Salas</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-horario" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-alarm"></i><span class="nav-link-text"> Horários</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/relatorios-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-chart-bar"></i><span class="nav-link-text"> Relatório</span></a></li>
            <li class="list-group-item mt-auto rounded-1 p-0 overflow-hidden"><a href="/logout" class="nav-link-custom logout d-flex align-items-center gap-2 p-2"><i class="fas fa-sign-out-alt"></i><span class="nav-link-text"> Logout</span></a></li>
        </ul>
    </nav>
</div>

<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasMenuLabel">
    <div class="offcanvas-header" style="background-color: var(--blue-color);">
        <h5 class="offcanvas-title text-white" id="offcanvasMenuLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>
    <div class="offcanvas-body p-0" style="background-color: var(--light-color);">
        <ul class="list-group list-group-flush w-100 gap-1 mt-3">
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-home"></i><span class="nav-link-text"> Início</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-calendar-plus"></i><span class="nav-link-text"> Criar Agenda</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/consultar-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-edit"></i><span class="nav-link-text"> Agendas</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-paciente" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-person-add"></i><span class="nav-link-text"> Criar Paciente</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/consultar-paciente" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-people"></i><span class="nav-link-text"> Pacientes</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-servico" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-gear"></i><span class="nav-link-text"> Serviços</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-sala" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-door-open"></i><span class="nav-link-text"> Salas</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/criar-horario" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="bi bi-alarm"></i><span class="nav-link-text"> Horários</span></a></li>
            <li class="list-group-item rounded-1 p-0 overflow-hidden"><a href="/psicologia/relatorios-agendamento" class="nav-link-custom primary d-flex align-items-center gap-2 p-2"><i class="fas fa-chart-bar"></i><span class="nav-link-text"> Relatório</span></a></li>
            <li class="list-group-item mt-auto rounded-1 p-0 overflow-hidden"><a href="/logout" class="nav-link-custom logout d-flex align-items-center gap-2 p-2"><i class="fas fa-sign-out-alt"></i><span class="nav-link-text"> Logout</span></a></li>
        </ul>
    </div>
</div>

<div class="col-12 text-center mb-1 d-none d-lg-block px-4" id="page-header-container">
    <div class="d-flex flex-row justify-content-between align-items-center">
        <p class="p-0 mt-2 mb-1 text-start fs-4">
            <i class="bi bi-list" id="btnToggleNavbar" style="cursor: pointer;"></i>
            <strong id="page-title"></strong>
        </p>
        <div class="me-2 pt-2 d-flex align-items-center">
            @if(isset($slot) && $slot->isNotEmpty())
                {{ $slot }}
            @endif
            <div class="profile-container ms-3" style="position: relative;">
                <i class="bi bi-person-circle fs-2" id="profile" style="cursor: pointer;"></i> 
            </div>
        </div>          
    </div>
</div>

<script>
// SCRIPT PRINCIPAL (INTERATIVIDADE)
// Roda após o carregamento completo da página
document.addEventListener("DOMContentLoaded", function () {
    // Apenas executa a lógica da sidebar em telas de desktop
    if (window.innerWidth < 992) {
        return;
    }

    // --- ELEMENTOS DO DOM ---
    const btnToggle = document.getElementById("btnToggleNavbar");
    const navbar = document.getElementById("mainNavbar");
    const logo = document.getElementById("logo-faesa");
    const pageTitleElement = document.getElementById('page-title');
    const body = document.body;
    
    // --- CONSTANTES ---
    const storageKey = 'sidebarState';
    const ANIMATION_DURATION = 300;

    if (!btnToggle || !navbar || !logo || !pageTitleElement) {
        console.error("Um ou mais elementos da sidebar não foram encontrados.");
        return;
    }

    // --- PRÉ-CARREGAMENTO DAS IMAGENS ---
    const logoExpandido = new Image();
    logoExpandido.src = "{{ asset('img/faesa_logo_expandido.png') }}";
    const logoRecolhido = new Image();
    logoRecolhido.src = "{{ asset('img/faesa_logo_recolhido.png') }}";

    // --- FUNÇÕES ---
    function trocaLogo(src) {
        logo.style.opacity = 0;
        setTimeout(() => { logo.src = src; logo.style.opacity = 1; }, 200);
    }
    
    function atualizaLogo() {
        if (navbar.classList.contains("collapsed")) {
            trocaLogo(logoRecolhido.src);
        } else {
            trocaLogo(logoExpandido.src);
        }
    }

    function atualizarCalendario() {
        if (typeof renderCalendar === 'function') {
            setTimeout(() => renderCalendar(), ANIMATION_DURATION);
        }
    }
    
    function aplicarEstado(estado) {
        // Aplica estado na NAVBAR
        if (estado === 'collapsed') {
            navbar.classList.add('collapsed');
        } else {
            navbar.classList.remove('collapsed');
        }
        // Aplica estado no BODY (já feito pelo script anti-fouc, mas garantimos aqui também)
        document.body.classList.toggle('sidebar-collapsed', estado === 'collapsed');
        
        atualizaLogo();
    }

    // --- LÓGICA PRINCIPAL ---
    pageTitleElement.textContent = document.title;
    
    // O estado inicial já foi aplicado pelo script no topo.
    // Apenas garantimos que a logo esteja correta no primeiro carregamento.
    if (localStorage.getItem(storageKey) === 'collapsed') {
        navbar.classList.add('collapsed');
        if (typeof renderCalendar === 'function') {
           setTimeout(() => renderCalendar(), 50);
        }
    }
    atualizaLogo();

    // Evento de clique no botão
    btnToggle.addEventListener("click", function () {
        const novoEstado = navbar.classList.contains("collapsed") ? 'expanded' : 'collapsed';
        localStorage.setItem(storageKey, novoEstado);
        aplicarEstado(novoEstado);
        atualizarCalendario();
    });
});
</script>