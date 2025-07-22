function createNavBar() {
  const navbar = document.createElement('div');
  navbar.innerHTML = `
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    .sidebar {
      width: 224px;
      height: 100vh;
      background-color: #2980b9;
      color: white;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 24px;
      position: fixed;
      box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
      z-index: 1030;
    }

    .sidebar img {
      width: 180px;
      margin: 25px 0 15px;
    }

    .sidebar h3 {
      color: #ffffff;
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 25px;
      text-transform: uppercase;
      border-bottom: 2px solid rgba(255, 255, 255, 0.3);
      padding-bottom: 8px;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
      width: 100%;
    }

    .sidebar ul li {
      width: 100%;
    }

    .sidebar ul li a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 20px;
      color: white;
      text-decoration: none;
      font-size: 17px;
      border-radius: 6px;
      transition: background-color 0.2s ease, transform 0.1s ease;
    }

    .sidebar ul li a:hover {
      background-color: rgba(255, 255, 255, 0.08);
      transform: translateX(1px);
    }

    .sidebar ul li a i {
      width: 20px;
      text-align: center;
      font-size: 16px;
    }

    .logout-link {
      margin-top: auto;
      margin-bottom: 30px;
      padding: 12px 24px;
      color: #ecf0f1;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      width: 70%;
      text-align: center;
      border-radius: 8px;
      background-color: rgba(192, 57, 43, 0.9);
      transition: background 0.3s;
    }

    .logout-link:hover {
      background-color: #c0392b;
    }

    /* Responsivo */
    @media (max-width: 991px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .menu-toggle {
        position: fixed;
        top: 16px;
        left: 16px;
        z-index: 1040;
        background-color: #2980b9;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 20px;
      }
    }
  </style>

  <button class="menu-toggle d-lg-none" onclick="document.querySelector('.sidebar').classList.toggle('open')">
    <i class="fas fa-bars"></i>
  </button>

  <nav class="sidebar">
    <img src="/img/faesa.png" alt="Logo">
    <h3>Odontologia</h3>

    <ul>
      <li><a href="/odontologia/menu_agenda_odontologia"><i class="fas fa-home"></i>Inícios</a></li>
      <li><a href="/odontologia/criaragenda"><i class="fas fa-calendar-plus"></i>Agendar</a></li>
      <li><a href="/odontologia/consultaragenda"><i class="fas fa-edit"></i>Consultar agenda</a></li>
      <li><a href="/odontologia/consultarpaciente"><i class="fas fa-users"></i>Paciente</a></li>
      <li><a href="/odontologia/consultarservico"><i class="fas fa-hammer"></i>Serviço</a></li>
      <li><a href="/odontologia/consultarbox"><i class="fas fa-hospital"></i>Box de atendimento</a></li>
      <li><a href="/odontologia/consultardisciplinabox"><i class="fas fa-file"></i>Disciplinas por box</a></li>
      <li><a href="/odontologia/relatorio"><i class="fas fa-chart-bar"></i>Relatório</a></li>
    </ul>

    <a href="/logout" class="logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a>
  </nav>
`;
  return navbar;
}

export { createNavBar };