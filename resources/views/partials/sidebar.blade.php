<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<link href="/css/sidebar.css" rel="stylesheet">

@php
// Tipo (Admin|Coordenador|Usuario)
$tipo = \Auth::user()->tipo
?? session('tipo')
?? data_get(session('usuario'), 'tipo')
?? 'Usuario';

$tipo = ucfirst(strtolower($tipo)); // normaliza o case

// Nome para exibir no chip
$nome = \Auth::user()->name // Laravel padrão: 'name'
?? \Auth::user()->nome // se seu model usa 'nome'
?? session('nome')
?? data_get(session('usuario'), 'NOME') // se vier da sua tabela
?? data_get(session('usuario'), 'nome')
?? 'Usuário';

// (opcional) só limpar espaços
$nome = trim($nome);
@endphp

<!-- Botão mobile -->
<button class="menu-toggle d-lg-none" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="fas fa-bars" aria-hidden="true"></i>
    <span class="sr-only">Abrir menu</span>
</button>

<nav class="sidebar" role="navigation" aria-label="Menu principal">
    <div class="logo-wrap">
        <a href="/perfil" class="user-chip" title="{{ $nome }}" aria-label="Perfil de {{ $nome }}">
            <span class="status-dot" aria-hidden="true"></span>
            <i class="fas fa-user-circle" aria-hidden="true"></i>
            <span class="user-name">{{ $nome }}</span>
        </a>
        <img src="/img/faesa.png" alt="FAESA">
    </div>

    <div class="clinic-badge">
        <i class="fas fa-tooth" aria-hidden="true"></i>
        <span>Odontologia</span>
    </div>

    @if(in_array($tipo, ['Admin','Coordenador','Usuario'], true))
    <ul class="nav-section">
        <li>
            <a href="/odontologia/menu_agenda"
                class="{{ request()->is('odontologia/menu_agenda') ? 'active' : '' }}"
                aria-current="{{ request()->is('odontologia/menu_agenda') ? 'page' : '' }}">
                <i class="fas fa-home"></i>Início
            </a>
        </li>

        <li>
            <a href="/odontologia/consultaragenda"
                class="{{ request()->is('odontologia/consultaragenda') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-alt"></i>Agenda
            </a>
        </li>

    </ul>
    @endif

    @if(in_array($tipo, ['Admin','Coordenador'], true))
    <ul class="nav-section">
        <li>
            <a href="/odontologia/consultarpaciente"
                class="{{ request()->is('odontologia/consultarpaciente') ? 'active' : '' }}">
                <i class="fas fa-users"></i>Pacientes
            </a>
        </li>
        <li>
            <a href="/odontologia/criaragenda"
                class="{{ request()->is('odontologia/criaragenda') ? 'active' : '' }}">
                <i class="fas fa-calendar-plus"></i>Novo agendamento
            </a>
        </li>
        <li>
            <a href="/odontologia/encaminhamentos"
                class="{{ request()->is('odontologia/encaminhamentos') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i>Encaminhamentos
            </a>
        </li>
        <li>
            <a href="/odontologia/consultardisciplinabox"
                class="{{ request()->is('odontologia/consultardisciplinabox') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i>Disciplinas por box
            </a>
        </li>
        <li>
            <a href="/odontologia/consultarbox"
                class="{{ request()->is('odontologia/consultarbox') ? 'active' : '' }}">
                <i class="fas fa-hospital"></i>Box de atendimento
            </a>
        </li>
        <li>
            <a href="/odontologia/relatorios"
                class="{{ request()->is('odontologia/relatorio') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>Relatório
            </a>
        </li>
    </ul>
    @endif

    {{-- Somente Admin --}}
    @if($tipo === 'Admin')
    <ul class="nav-section">
        <li>
            <a href="/odontologia/consultarusuario"
                class="{{ request()->is('odontologia/consultarusuario') ? 'active' : '' }}">
                <i class="fas fa-user"></i>Usuários
            </a>
        </li>
    </ul>
    @endif

    <a id="logout" href="/logout" class="logout-link">
        <i class="fas fa-sign-out-alt"></i>Sair
    </a>
</nav>