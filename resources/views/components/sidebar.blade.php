<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<link href="/css/sidebar.css" rel="stylesheet">

<button class="menu-toggle d-lg-none" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="fas fa-bars"></i>
</button>

<nav class="sidebar">
    <img src="/img/faesa.png" alt="Logo">
    <h3>Odontologia</h3>

    <ul>
        <li><a href="/odontologia/menu_agenda"><i class="fas fa-home"></i>Início</a></li>
        <li><a href="/odontologia/criaragenda"><i class="fas fa-calendar-plus"></i>Agendar</a></li>
        <li><a href="/odontologia/consultaragenda"><i class="fas fa-edit"></i>Consultar agenda</a></li>
        <li><a href="/odontologia/consultarpaciente"><i class="fas fa-users"></i>Paciente</a></li>
        <li><a href="/odontologia/consultarservico"><i class="fas fa-hammer"></i>Serviço</a></li>
        <li><a href="/odontologia/consultardisciplinabox"><i class="fas fa-file"></i>Disciplinas por box</a></li>
        <li><a href="/odontologia/consultarbox"><i class="fas fa-hospital"></i>Box de atendimento</a></li>
        <li><a href="/odontologia/relatorio"><i class="fas fa-chart-bar"></i>Relatório</a></li>
    </ul>

    <a id="logout" href="/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
</nav>