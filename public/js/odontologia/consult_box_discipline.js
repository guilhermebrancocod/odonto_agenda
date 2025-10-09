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

function carregarTodosBoxDiscipline() {
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
                    disciplines.ID_BOX_DISCIPLINA,
                    disciplines.NOME,
                    false
                );
                $select.append(newOption);

                // Adiciona à tabela
                const html = `
                    <tr>
                        <td>${disciplines.NOME}</td>
                        <td>${disciplines.DESCRICAO}</td>
                        <td>${disciplines.ALUNO}</td>
                        <td>${disciplines.TURMA}</td>
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
    carregarTodosBoxDiscipline();
});

// Evento ao selecionar um paciente no select2
$('#selectBoxDiscipline').on('select2:select', function (e) {
    const idBoxDiscipline = e.params.data.id;
    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/consultaboxdisciplina/${idBoxDiscipline}`,
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return { query: params.term || '' };
        },
        success: function (disciplines) {
            const html = `
                    <tr>
                        <td>${disciplines.DISCIPLINA}</td>
                        <td>${disciplines.DESCRICAO}</td>
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
            // Atualiza o corpo da tabela com apenas o paciente selecionado
            $('#box-discipline tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados box/disciplina.");
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

