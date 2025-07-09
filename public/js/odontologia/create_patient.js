import { createNavBar } from '/js/odontologia/navbar.js';

$('.datepicker').datepicker({
    format: 'dd/mm/yyyy',
    language: 'pt-BR',
    autoclose: true,
    todayHighlight: true
});

$(document).ready(function () {
    $('#cpf').mask('000.000.000-00');
});

$(document).ready(function () {
    $('#celular').mask('(00)00000-0000');
});

$(document).ready(function () {
    $('#cep').mask('00000-000');
});

$(document).ready(function(){
    $('#dt_nasc').mask('00/00/0000');
});

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

document.getElementById('voltar').addEventListener('click', function (event) {
    event.preventDefault(); // previne submit padrão
    window.location.href = 'consultarpaciente'; // substitua pela rota que deseja
});
