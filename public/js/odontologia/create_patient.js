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

$(document).ready(function () {
    $('#dt_nasc').mask('00/00/0000');
});

function valid_nascimento() {
    const nascEl = document.getElementById('dt_nasc');
    const respEl = document.getElementById('nome_responsavel'); // confira o id

    if (!nascEl) return true;

    const v = (nascEl.value || '').trim();   // ex.: "24/06/2020"
    if (!v) return true;

    const parts = v.split('/');
    if (parts.length !== 3) return true;     // ou return false, se quiser obrigar

    const [dStr, mStr, yStr] = parts;
    const d = parseInt(dStr, 10);
    const m = parseInt(mStr, 10);
    const y = parseInt(yStr, 10);

    // monta a data (valida dia/mês/ano)
    const dob = new Date(y, m - 1, d);
    const invalida =
        isNaN(dob.getTime()) ||
        dob.getFullYear() !== y ||
        dob.getMonth() !== (m - 1) ||
        dob.getDate() !== d;

    if (invalida) return false;

    // calcula idade
    const today = new Date();
    let age = today.getFullYear() - y;
    const fezAniversarioEsteAno =
        today.getMonth() > (m - 1) ||
        (today.getMonth() === (m - 1) && today.getDate() >= d);
    if (!fezAniversarioEsteAno) age--;

    if (age < 18) {
        respEl && respEl.focus();   // foca no campo do responsável
        return false;               // bloqueia se for usado no submit
    }

    return true;
}

document.getElementById('dt_nasc').addEventListener('change', valid_nascimento);

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
