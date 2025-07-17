import { createNavBar } from '/js/odontologia/navbar.js';
import { Modal } from 'https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.es.min.js';


function carregarTodosBox() {
    const $select = $('#selectBoxDiscipline');
    const $tbody = $('#box-discipline tbody');

    $.ajax({
        url: '/getBoxDisciplines',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            $tbody.empty();

            data.forEach(disciplines => {
                // Adiciona ao select
                const newOption = new Option(
                    disciplines.DISCIPLINA,
                    disciplines.ID_BOX,
                    false,
                    false
                );
                $select.append(newOption);

                // Adiciona à tabela
                const html = `
                    <tr>
                        <td>${disciplines.DISCIPLINA}</td>
                        <td>${disciplines.ID_BOX}</td>
                        <td>${disciplines.DIA_SEMANA}</td>
                        <td>${disciplines.HR_INICIO}</td>
                        <td>${disciplines.HR_FIM}</td>
                        <td>
                            <button 
                                type="button" 
                                class="edit-patient btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${disciplines.ID_BOX_DISCIPLINA}">
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
    const $select = $('#selectBoxDiscipline');

    $select.select2({
        placeholder: "Busque por box, disciplinas, dia de semana ou hora",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/getBoxes',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_BOX_CLINICA,
                        text: p.DESCRICAO

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
    carregarTodosBox();
});

// Evento ao selecionar um paciente no select2
$('#selectService').on('select2:select', function (e) {
    const boxId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/servicos/${servicoId}`,
        type: 'GET',
        dataType: 'json',
        success: function (servico) {
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
            // Atualiza o corpo da tabela com apenas o paciente selecionado
            $('#table-patient tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados do paciente.");
        }
    });
});

// Evento para editar servico
$(document).on('click', '.edit-patient', function (event) {
    event.preventDefault();
    const servicoId = $(this).data('id');
    window.location.href = `/odontologia/criarservico/${servicoId}`;
});

const addPatient = document.getElementById('add');

addPatient.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = '/odontologia/criarboxdisciplina';
});

const navbarContainer = document.getElementById('navbar-container');
const navbar = createNavBar();
navbarContainer.appendChild(navbar);