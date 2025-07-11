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
      width: 100px;
      margin: 20px 0 10px;
    }

    nav h3 {
      color: #ecf0f1;
      font-size: 18px;
      margin-bottom: 20px;
      text-transform: uppercase;
    }

  h3 {
    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
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
      width: 70%;
      text-align: center;
      border-radius: 8px;
    }

    .logout-link:hover {
      background-color: #c0392b;
    }
  </style>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <nav>
    <img src="/img/faesa.png" alt="Logo">
    <h3>Odontologia</h3>

    <ul>
      <li><a href="/odontologia/menu_agenda_odontologia"><i class="fas fa-home"></i>Início</a></li>
      <li><a href="/odontologia/criaragenda"><i class="fas fa-calendar-plus"></i>Agendar</a></li>
      <li><a href="/odontologia/consultaragenda"><i class="fas fa-edit"></i>Consultar agenda</a></li>
      <li><a href="/odontologia/consultarpaciente"><i class="fas fa-users"></i>Paciente</a></li>
      <li><a href="/odontologia/relatorio"><i class="fas fa-chart-bar"></i>Relatório</a></li>
      <li><a href="/odontologia/criarservico"><i class="fas fa-hammer"></i>Serviço</a></li>
    </ul>

    <a href="/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
`;
  return navbar;
}

export { createNavBar };