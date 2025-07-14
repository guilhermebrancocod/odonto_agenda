import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';


function carregarTodosServicos() {
    const $select = $('#selectService');
    const $tbody = $('#table-patient tbody');

    $.ajax({
        url: '/getServices',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            $tbody.empty();

            data.forEach(servico => {
                // Adiciona ao select
                const newOption = new Option(
                    servico.SERVICO_CLINICA_DESC,
                    servico.ID_SERVICO_CLINICA,
                    false,
                    false
                );
                $select.append(newOption);

                // Adiciona à tabela
                const html = `
                    <tr>
                        <td>${servico.SERVICO_CLINICA_DESC}</td>
                        <td>${servico.VALOR_SERVICO != null && servico.VALOR_SERVICO !== '' ? 'R$ ' + parseFloat(servico.VALOR_SERVICO).toFixed(2) : ''}</td>
                        <td>${servico.PERMITE_ATENDIMENTO_SIMULTANEO == 1 ? 'Sim' : 'Não'}</td>
                        <td>
                            <button 
                                type="button" 
                                class="edit-patient btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${servico.ID_SERVICO_CLINICA}">
                                <i class="fa fa-pencil-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(html);
            });

            $select.val(null).trigger('change');
        },
        error: function () {
            console.error('Erro ao carregar os serviços.');
        }
    });
}

$(document).ready(function () {
    const $select = $('#selectService');

    $select.select2({
        placeholder: "Busque o serviço",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/getServices',
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
    carregarTodosServicos();
});

// Evento ao selecionar um paciente no select2
$('#selectService').on('select2:select', function (e) {
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
    window.location.href = '/odontologia/criarservico';
});

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);