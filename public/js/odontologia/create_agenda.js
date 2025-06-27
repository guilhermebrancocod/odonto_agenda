import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

const agendar = document.getElementById('btn-agendar');

agendar.addEventListener('click', function () {
    const pagto = document.getElementById('pagto').value;

    if (pagto === 'S') {
        const modalCash = new Modal(document.getElementById('modal_cash'));
        modalCash.show();
    } else {
        // Aqui você pode chamar a função de agendar normal se for 'N'
        console.log('Agendamento sem pagamento.');
    }
});