import { createNavBar } from '/js/odontologia/navbar.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

document.addEventListener('DOMContentLoaded', function () {
    fetch('/odontologia/disciplinas')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('boxes-discipline');
            container.innerHTML = '';

            data.forEach(item => {
                const div = document.createElement('div');

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'disciplines[]';
                checkbox.value = item.DISCIPLINA;
                checkbox.id = `${item.DISCIPLINA.replace(/\s+/g, '_')}`;

                const label = document.createElement('label');
                label.style.marginLeft = '6px';
                label.htmlFor = checkbox.id;
                label.textContent = ' ' + '(' + item.DISCIPLINA + ')' + ' ' + item.NOME;

                if (disciplinasSelecionadas.includes(item.DISCIPLINA)) {
                    checkbox.checked = true;
                }

                div.appendChild(checkbox);
                div.appendChild(label);
                container.appendChild(div);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar disciplinas:', error);
        });
});
