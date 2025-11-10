function maskTime(value) {
    return value
        .replace(/\D/g, '')
        .replace(/^(\d{2})(\d)/, '$1:$2')
        .replace(/^(\d{2}):(\d{2}).*/, '$1:$2')
}

function diaSemana(value) {
    switch (value) {
        case "1": return 'Domingo';
        case "2": return 'Segunda-feira';
        case "3": return 'Terça-feira';
        case "4": return 'Quarta-feira';
        case "5": return 'Quinta-feira';
        case "6": return 'Sexta-feira';
        case "7": return 'Sábado';
        default: return 'Valor inválido';
    }
}

// Variáveis para paginação
let currentPage = 1;
const itemsPerPage = 10;
let allDisciplines = [];

function updatePagination() {
    const totalPages = Math.ceil(allDisciplines.length / itemsPerPage) || 1;

    // Atualiza o texto de informação da página
    $('#page-info').text(`Página ${currentPage} de ${totalPages}`);

    // Habilita/desabilita botões de navegação
    $('#prev-page').toggleClass('disabled', currentPage === 1);
    $('#next-page').toggleClass('disabled', currentPage === totalPages || allDisciplines.length === 0);

    // Mostra mensagem quando não há disciplinas
    if (allDisciplines.length === 0) {
        $('#no-disciplines-message').show();
        $('#box-discipline').hide();
    } else {
        $('#no-disciplines-message').hide();
        $('#box-discipline').show();
    }
}

function loadDisciplinesPage(page) {
    const $tbody = $('#box-discipline tbody');
    $tbody.empty();

    if (allDisciplines.length === 0) {
        updatePagination();
        return;
    }

    const start = (page - 1) * itemsPerPage;
    const end = Math.min(start + itemsPerPage, allDisciplines.length);

    for (let i = start; i < end; i++) {
        const disciplines = allDisciplines[i];
        const html = `
            <tr>
                <td>${disciplines.NOME}</td>
                <td>${disciplines.DESCRICAO}</td>
                <td>${disciplines.ALUNO}</td>
                <td>${disciplines.TURMA}</td>
                <td>${diaSemana(disciplines.DIA_SEMANA)}</td>
                <td>${maskTime(disciplines.HR_INICIO)}</td>
                <td>${maskTime(disciplines.HR_FIM)}</td>
                <td>
                    <button 
                        type="button" 
                        class="edit-boxdisciplines btn btn-link p-0 m-0 border-0" 
                        style="color: inherit;" 
                        data-id="${disciplines.ID_BOX_DISCIPLINA}">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                </td>
                <td>
                    <button 
                        type="button" 
                        class="delete-boxdisciplines btn btn-link p-0 m-0 border-0" 
                        style="color: inherit;" 
                        data-id="${disciplines.ID_BOX_DISCIPLINA}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $tbody.append(html);
    }

    updatePagination();
}

function carregarTodosBoxDiscipline() {
    const $select = $('#selectBoxDiscipline');

    $.ajax({
        url: '/getBoxDisciplines',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            allDisciplines = data;
            currentPage = 1;

            // Adiciona ao select
            data.forEach(disciplines => {
                const newOption = new Option(
                    disciplines.DISCIPLINA,
                    disciplines.ID_BOX_DISCIPLINA,
                    disciplines.NOME,
                    false
                );
                $select.append(newOption);
            });

            // Carrega a primeira página
            loadDisciplinesPage(currentPage);
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
            url: '/getBoxDisciplines',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID_BOX_DISCIPLINA,
                        text: `${p.DISCIPLINA} - ${p.NOME} - ${p.DESCRICAO}`
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

    // Event listeners para os botões de paginação
    $('#prev-page').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        currentPage--;
        loadDisciplinesPage(currentPage);
    });

    $('#next-page').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        currentPage++;
        loadDisciplinesPage(currentPage);
    });

    carregarTodosBoxDiscipline();
});

// Evento ao selecionar um paciente no select2
$('#selectBoxDiscipline').on('select2:select', function (e) {
    const id = e.params.data.id;
    $.ajax({
        url: `/getBoxDisciplines/${id}`,
        type: 'GET',
        success: function (data) {
            $select.empty();
            allDisciplines = data;
            currentPage = 1;

            // Adiciona ao select
            data.forEach(disciplines => {
                const newOption = new Option(
                    disciplines.DISCIPLINA,
                    disciplines.ID_BOX_DISCIPLINA,
                    disciplines.NOME,
                    false
                );
                $select.append(newOption);
            });

            // Carrega a primeira página
            loadDisciplinesPage(currentPage);
            $select.val(null).trigger('change');
        },
        error: function (error) {
            console.error('Erro ao buscar box discipline:', error);
            $('#no-disciplines-message').show();
            $('#box-discipline').hide();
            $('.pagination-container').hide();
        }
    });
});

// Evento para editar servico
$(document).on('click', '.edit-boxdisciplines', function (event) {
    event.preventDefault();
    const idBoxDiscipline = $(this).data('id');
    window.location.href = `/odontologia/criarboxdisciplina/${idBoxDiscipline}`;
});

$(document).on('click', '.delete-boxdisciplines', function (event) {
    event.preventDefault();
    const idBoxDiscipline = $(this).data('id');
    window.location.href = `/odontologia/deleteboxdisciplina/${idBoxDiscipline}`;
});

const addPatient = document.getElementById('add');

addPatient.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = '/odontologia/criarboxdisciplina';
});

