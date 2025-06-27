import { createNavBar } from '/js/odontologia/navbar.js';

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);

document.getElementById('cep').addEventListener('blur', async function () {
    const cep = this.value.replace(/\D/g, '');
    if (cep.length === 8) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            if (!data.erro) {
                document.getElementById('rua').value = data.logradouro;
                document.getElementById('bairro').value = data.bairro;
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('estado').value = data.uf;
            } else {
                alert('CEP não encontrado!');
            }
        } catch (error) {
            alert('Erro ao consultar o CEP');
        }
    }
});