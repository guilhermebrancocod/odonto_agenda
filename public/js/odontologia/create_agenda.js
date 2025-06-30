import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

const add_simple = document.getElementById('add');

add_simple.addEventListener('click', function (event) {
    event.preventDefault();
    const modalCash = new Modal(document.getElementById('modal_add_patient'));
    modalCash.show();
});

const pagto = document.getElementById('pagto');
const valor = document.getElementById('valor');

pagto.addEventListener('change', function () {
    if (pagto.value === 'S') {
        valor.disabled = false;
    }else {
        valor.disabled = true;
    }
})