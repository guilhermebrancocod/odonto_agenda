import { createNavBar } from '/js/odontologia/navbar.js';

const add = document.getElementById('add');
const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

document.addEventListener('DOMContentLoaded', function () {
    fetch('/odontologia/disciplinas')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('disciplina');


            select.innerHTML = '<option value="">Selecione a disciplina</option>';

            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.DISCIPLINA;
                option.textContent = item.DISCIPLINA;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar disciplinas:', error);
        });

    fetch('/odontologia/boxes')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('boxes-container');
            container.innerHTML = ''; // Limpa o conteúdo anterior

            data.forEach(item => {
                const div = document.createElement('div');

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'boxes[]';
                checkbox.value = item.DESCRICAO;
                checkbox.id = `box_${item.ID_BOX_CLINICA.replace(/\s+/g, '_')}`;

                const label = document.createElement('label');
                label.style.marginLeft = '6px';
                label.htmlFor = checkbox.id;
                label.textContent = ' ' + item.DESCRICAO;

                div.appendChild(checkbox);
                div.appendChild(label);
                container.appendChild(div);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar boxes:', error);
        });
});