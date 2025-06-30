function createNavBar() {
    const navbar = document.createElement('nav');
    navbar.innerHTML = `
  <style>
    nav {
      width: 224px;
      height: 100vh;
      background-color: #2980b9;
      color: white;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: fixed;
    }

    nav img {
      width: 60px;
      margin: 20px 0 10px;
    }

    nav h4 {
      color: #ecf0f1;
      font-size: 18px;
      margin-bottom: 20px;
      text-transform: uppercase;
    }

    nav ul {
      list-style: none;
      padding: 0;
      width: 100%;
    }

    nav ul li {
      width: 100%;
    }

    nav ul li a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      transition: background 0.3s;
    }

    nav ul li a:hover,
    nav ul li a.active {
      background-color:rgb(2, 167, 189);
    }

    nav ul li a i {
      width: 20px;
      text-align: center;
    }

    .logout-link {
      margin-top: auto;
      margin-bottom: 20px;
      padding: 10px 20px;
      color: #ecf0f1;
      text-decoration: none;
      display: block;
      width: 100%;
      text-align: center;
    }

    .logout-link:hover {
      background-color: #c0392b;
    }
  </style>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

  <nav>
    <img src="img/faesa.png" alt="Logo">
    <h4>Psicologia</h4>

    <ul>
      <li><a href="/psicologia/"><i class="fas fa-home"></i> Início</a></li>
      <li><a href="/psicologia/criar-agenda"><i class="fas fa-calendar-plus"></i> Incluir Agendamento</a></li>
      <li><a href="/psicologia/criar-agenda"><i class="fas fa-edit"></i>Consultar Agenda</a></li>
      <li><a href="/psicologia/criar-paciente"><i class="fas fa-user-plus"></i> Cadastrar Paciente</a></li>
      <li><a href="/psicologia/consultar-paciente/"><i class="fas fa-users"></i> Consultar Paciente</a></li>
      <li>
        <a href="/psicologia/criar-servico/">
          <i class="bi bi-hammer"></i>
          Cadastrar Serviço
        </a>
      </li>
      <li><a href="/psicologia/relatorio"><i class="fas fa-chart-bar"></i> Relatório</a></li>
    </ul>

    <a href="/psicologia/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
`;
    return navbar;
}

export { createNavBar };