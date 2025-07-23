import { createNavBar } from './navbar.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);
