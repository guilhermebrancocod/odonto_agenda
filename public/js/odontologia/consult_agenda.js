import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);
