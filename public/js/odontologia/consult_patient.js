import { createNavBar } from './navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';

function maskPhone(value) {
    if (!value) return '';
    return value
        .replace(/\D/g, '')
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d{1,4})/, '$1-$2')
        .replace(/(-\d{4})\d+?$/, '$1'); // Limita a 4 dígitos no final
}

function formatCPF(value) {
    if (!value) return '';
    return value
        .replace(/\D/g, '')                            // Remove tudo que não é dígito
        .replace(/(\d{3})(\d)/, '$1.$2')               // Primeiro ponto
        .replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')   // Segundo ponto
        .replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4') // Hífen
        .slice(0, 14); // Garante no máximo 14 caracteres
}

function carregarTodosPacientes() {
    const $select = $('#selectPatient');
    const $tbody = $('#table-patient tbody'); // garante que você tem um <tbody>

    $.ajax({
        url: '/getPacientes',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            // Limpa a tabela e o select
            $select.empty();
            $tbody.empty();

            data.forEach(paciente => {
                // Adiciona ao select
                const newOption = new Option(paciente.NOME_COMPL_PACIENTE, paciente.ID_PACIENTE, false, false);

                $select.append(newOption);

                // Adiciona à tabela
                const html = `
                <tr>
                    <td>${formatCPF(paciente.CPF_PACIENTE)}</td>
                    <td>${paciente.NOME_COMPL_PACIENTE}</td>
                    <td>${paciente.E_MAIL_PACIENTE}</td>
                    <td>${maskPhone(paciente.FONE_PACIENTE)}</td>
                    <td>
                        <button 
                            type="button" 
                            class="edit-patient btn btn-link p-0 m-0 border-0" 
                            style="color: inherit;" 
                            data-id="${paciente.ID_PACIENTE}">
                            <i class="fa fa-pencil-alt"></i>
                        </button>
                    </td>
                </tr>
                `;

                $tbody.append(html);
            });

            $select.val(null).trigger('change');
        }
    });
}

$(document).ready(function () {
    const $select = $('#selectPatient');

    $select.select2({
        placeholder: "Busque o paciente por nome ou CPF",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/getPacientes',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_PACIENTE,
                        text: p.NOME_COMPL_PACIENTE
                        
                    }))
                };
            },
            cache: true
        }
    });

    // Foco automático ao abrir o select2
    $select.on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });

    // 👇 Única chamada necessária ao carregar
    carregarTodosPacientes();
});

// Evento ao selecionar um paciente no select2
$('#selectPatient').on('select2:select', function (e) {
    const pacienteId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/paciente/${pacienteId}`,
        type: 'GET',
        dataType: 'json',
        success: function (paciente) {
            const html = `
                <tr>
                    <td>${formatCPF(paciente.CPF_PACIENTE)}</td>
                    <td>${paciente.NOME_COMPL_PACIENTE}</td>
                    <td>${paciente.E_MAIL_PACIENTE}</td>
                    <td>${maskPhone(paciente.FONE_PACIENTE)}</td>
                    <td>
                        <button 
                            type="button" 
                            class="edit-patient btn btn-link p-0 m-0 border-0" 
                            style="color: inherit;" 
                            data-id="${paciente.ID_PACIENTE}">
                            <i class="fa fa-pencil-alt"></i>
                        </button>
                    </td>
                </tr>
            `;

            // Atualiza o corpo da tabela com apenas o paciente selecionado
            $('#table-patient tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados do paciente.");
        }
    });
});

// Evento para editar paciente (usando delegation para funcionar após atualização do DOM)
$(document).on('click', '.edit-patient', function (event) {
    event.preventDefault();
    const pacienteId = $(this).data('id');
    window.location.href = `/odontologia/criarpaciente/${pacienteId}`;
});

const addPatient = document.getElementById('add');

addPatient.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = '/odontologia/criarpaciente';
});

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);