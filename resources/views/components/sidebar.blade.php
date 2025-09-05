<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<link href="/css/sidebar.css" rel="stylesheet">

</style>
<button class="menu-toggle d-lg-none" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="fas fa-bars"></i>
</button>
<nav class="sidebar" role="navigation" aria-label="Menu principal">
    <div class="logo-wrap">
        <!-- chip do usuário (clicável para /perfil, se quiser) -->
        <div>
            <ul>
                <li>
                    <a href="/perfil"
                        class="user-chip"
                        title="{{ $currentUser['name'] ?? 'Usuário' }}"
                        aria-label="Perfil de {{ $currentUser['name'] ?? 'Usuário' }}">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        <span class="user-name">{{ $currentUser['name'] ?? 'Usuário' }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <img src="/img/faesa.png" alt="Logo">
    </div>
    <h4>Odontologia</h4>
    <ul>
        <li><a href="/odontologia/criaragenda" style="font-size:medium;"><i class="fas fa-calendar-plus"></i>Novo agendamento</a></li>
        <li><a href="/odontologia/menu_agenda" style="font-size:medium;"><i class="fas fa-home"></i>Início</a></li>
        <li><a href="/odontologia/consultaragenda" style="font-size:medium;"><i class="fa-solid fa-calendar-alt"></i>Agenda</a></li>
        <li><a href="/odontologia/consultarpaciente" style="font-size:medium;"><i class="fas fa-users"></i>Pacientes</a></li>
        <li><a href="/odontologia/consultarservico" style="font-size:medium;"><i class="fa-solid fa-tooth"></i>Procedimentos</a></li>
        <li><a href="/odontologia/consultardisciplinabox" style="font-size:medium;"><i class="fa-solid fa-layer-group"></i>Disciplinas por box</a></li>
        <li><a href="/odontologia/consultarbox" style="font-size:medium;"><i class="fas fa-hospital"></i>Box de atendimento</a></li>
        <li><a href="/odontologia/relatorio"style="font-size:medium;"><i class="fas fa-chart-bar"></i>Relatório</a></li>
        <li><a href="/odontologia/consultarusuario" style="font-size:medium;margin-bottom: 45px"><i class="fas fa-user"></i>Usuários</a></li>
    </ul>
    <a id="logout" href="/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
</nav>