import { createNavBar } from './navbar.js';

function carregarTodosServicos() {
    const $select = $('#selectService');
    const $tbody = $('#table-service tbody');

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
                    servico.DISCIPLINA,
                    false,
                    false
                );
                $select.append(newOption);

                const html = `
                    <tr>
                        <td>${servico.SERVICO_CLINICA_DESC}</td>
                        <td>${servico.VALOR_SERVICO != null && servico.VALOR_SERVICO !== '' ? 'R$ ' + parseFloat(servico.VALOR_SERVICO).toFixed(2) : ''}</td>
                        <td>${servico.DISCIPLINA ? servico.DISCIPLINA : ''}</td>
                        <td>${servico.ATIVO}</td>
                        <td>
                            <button 
                                type="button" 
                                class="editService btn btn-link p-0 m-0 border-0" 
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
                        id: p.ID_SERVICO_CLINICA,
                        text: p.SERVICO_CLINICA_DESC,
                        discipline: p.DISCIPLINA

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

    carregarTodosServicos();
});

// Evento ao selecionar um paciente no select2
$('#selectService').on('select2:select', function (e) {
    const servicoId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/editService/${servicoId}`,
        type: 'GET',
        dataType: 'json',
        success: function (servico) {
            const html = `
                    <tr>
                        <td>${servico.SERVICO_CLINICA_DESC}</td>
                        <td>${servico.VALOR_SERVICO != null && servico.VALOR_SERVICO !== '' ? 'R$ ' + parseFloat(servico.VALOR_SERVICO).toFixed(2) : ''}</td>
                        <td>${servico.DISCIPLINA}</td>
                        <td>${servico.ATIVO}</td>
                        <td>
                            <button 
                                type="button" 
                                class="edit-service btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${servico.ID_SERVICO_CLINICA}">
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

// Evento para editar servico
$(document).on('click', '.editService', function (event) {
    event.preventDefault();
    const servicoId = $(this).data('id');
    window.location.href = `/criarservico/${servicoId}`;
});

const addPatient = document.getElementById('add');

addPatient.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = 'criarservico';
});

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);