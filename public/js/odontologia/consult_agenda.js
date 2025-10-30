function maskTime(value) {
    return value
        .replace(/\D/g, '')                // Remove tudo que não é dígito
        .replace(/^(\d{2})(\d)/, '$1:$2')  // Insere o :
        .replace(/^(\d{2}):(\d{2}).*/, '$1:$2'); // Limita a HH:MM
}

function maskPhone(value) {
    return value
        .replace(/\D/g, '')
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d{1,4})/, '$1-$2')
        .replace(/(-\d{4})\d+?$/, '$1'); // Limita a 4 dígitos no final
}

function formatDateStr(value) {
    if (!value) return '';
    const parts = value.split('-');
    if (parts.length !== 3) return value;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
}

// Variáveis para paginação
let currentPage = 1;
const itemsPerPage = 10;
let allAgendamentos = [];

function updatePagination() {
    const totalPages = Math.ceil(allAgendamentos.length / itemsPerPage) || 1;
    
    // Atualiza o texto de informação da página
    $('#page-info').text(`Página ${currentPage} de ${totalPages}`);
    
    // Habilita/desabilita botões de navegação
    $('#prev-page').toggleClass('disabled', currentPage === 1);
    $('#next-page').toggleClass('disabled', currentPage === totalPages || allAgendamentos.length === 0);
    
    // Mostra mensagem quando não há agendamentos
    if (allAgendamentos.length === 0) {
        $('#no-appointments-message').show();
        $('#table-agenda').hide();
    } else {
        $('#no-appointments-message').hide();
        $('#table-agenda').show();
    }
}

function loadAgendamentosPage(page) {
    const $tbody = $('#table-agenda tbody');
    $tbody.empty();
    
    if (allAgendamentos.length === 0) {
        updatePagination();
        return;
    }
    
    const start = (page - 1) * itemsPerPage;
    const end = Math.min(start + itemsPerPage, allAgendamentos.length);
    
    for (let i = start; i < end; i++) {
        const agendamento = allAgendamentos[i];
        const html = `
            <tr>
                <td>${agendamento.NOME_COMPL_PACIENTE ?? ''}</td>
                <td>${formatDateStr(agendamento.DT_AGEND ?? '')}</td>
                <td>${maskTime(agendamento.HR_AGEND_INI ?? '')} - ${maskTime(agendamento.HR_AGEND_FIN ?? '')}</td>
                <td>${agendamento.SERVICO_CLINICA_DESC ? agendamento.SERVICO_CLINICA_DESC : ''}</td>
                <td>${agendamento.TURMA ? agendamento.TURMA : ''}</td>
                <td>${agendamento.FONE_PACIENTE ? maskPhone(agendamento.FONE_PACIENTE) : ''}</td>
                <td>
                    <button 
                        type="button" 
                        class="edit-agenda btn btn-link p-0 m-0 border-0" 
                        style="color: inherit;" 
                        data-id="${agendamento.ID_AGENDAMENTO}">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $tbody.append(html);
    }
    
    updatePagination();
}

function carregarTodosAgendamentos() {
    $.ajax({
        url: '/getAgenda',
        dataType: 'json',
        success: function (data) {
            allAgendamentos = data;
            currentPage = 1;
            
            // Carrega a primeira página
            loadAgendamentosPage(currentPage);
        },
        error: function () {
            alert("Erro ao buscar os agendamentos.");
        }
    });
    /*$select.val(null).trigger('change');*/
}

$(document).ready(function () {
    $.fn.select2.defaults.set("language", "pt-BR");

    $('#selectPatient').select2({
        placeholder: "Busque o paciente por nome ou CPF",
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function (args) {
                const remainingChars = args.minimum - args.input.length;
                return `Digite pelo menos mais ${remainingChars} caractere${remainingChars > 1 ? 's' : ''}...`;
            }
        }
    });
});

$(document).ready(function () {
    $.fn.select2.defaults.set("language", "pt-BR");

    $('#selectTurma').select2({
        placeholder: "Busque a turma",
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function (args) {
                const remainingChars = args.minimum - args.input.length;
                return `Digite pelo menos mais ${remainingChars} caractere${remainingChars > 1 ? 's' : ''}...`;
            }
        }
    });
});

$(document).ready(function () {
    const $select = $('#selectPatient');

    $select.select2({
        placeholder: "Busque o paciente por nome ou CPF",
        allowClear: true,
        minimumInputLength: 0, // permite digitar 0 caracteres para buscar
        ajax: {
            url: '/getPacientes',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    query: params.term || ''
                };
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

    /*$select.val(null).trigger('change');*/

    carregarTodosAgendamentos();

    // Foco automático ao abrir
    $select.on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });
    
    // Event listeners para os botões de paginação
    $('#prev-page').on('click', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;
        
        currentPage--;
        loadAgendamentosPage(currentPage);
    });
    
    $('#next-page').on('click', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;
        
        currentPage++;
        loadAgendamentosPage(currentPage);
    });
});
// Evento ao selecionar um paciente no select2
$('#selectPatient').on('select2:select', function (e) {
    const pacienteId = e.params.data.id;
    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/agenda/${pacienteId}`,
        type: 'GET',
        dataType: 'json',
        success: function (paciente) {
            allAgendamentos = paciente;
            currentPage = 1;
            
            if (paciente.length === 0) {
                $('#no-appointments-message').show();
                $('#table-agenda').hide();
            } else {
                $('#no-appointments-message').hide();
                $('#table-agenda').show();
                loadAgendamentosPage(currentPage);
            }
        },
        error: function () {
            alert("Erro ao buscar os dados do paciente.");
        }
    });
});

$(document).ready(function () {
    const $select = $('#selectTurma');

    $select.select2({
        placeholder: "Busque a turma",
        allowClear: true,
        minimumInputLength: 0, // permite digitar 0 caracteres para buscar
        ajax: {
            url: '/odontologia/turmasAgendadas/',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    query: params.term || ''
                };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.TURMA,
                        text: p.TURMA,
                        agendaId: p.ID_AGENDAMENTO
                    }))
                };
            },
            cache: true
        }
    });

    /*$select.val(null).trigger('change');*/

    carregarTodosAgendamentos();

    // Foco automático ao abrir
    $select.on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });
});

$('#selectTurma').on('select2:select', function (e) {
    const turmaSelecionada = e.params.data.id;
    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/odontologia/turmasAgendadas/${turmaSelecionada}`,
        type: 'GET',
        dataType: 'json',
        success: function (turmaSelecionada) {
            let html =''
            // Monta o HTML da linha da tabela
            turmaSelecionada.forEach(function(turmaSelecionada){
                html += `
                <tr>
                    <td>${turmaSelecionada.NOME_COMPL_PACIENTE}</td>
                    <td>${formatDateStr(turmaSelecionada.DT_AGEND)}</td>
                    <td>${maskTime(turmaSelecionada.HR_AGEND_INI)} - ${maskTime(turmaSelecionada.HR_AGEND_FIN)}</td>
                    <td>${turmaSelecionada.SERVICO_CLINICA_DESC}</td>
                    <td>${turmaSelecionada.TURMA}</td>
                    <td>${maskPhone(turmaSelecionada.FONE_PACIENTE)}</td>
                    <td>
                        <button 
                            type="button" 
                            class="edit-agenda btn btn-link p-0 m-0 border-0" 
                            style="color: inherit;" 
                            data-id="${turmaSelecionada.ID_AGENDAMENTO}">
                            <i class="fa fa-pencil-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            })

            // Atualiza o corpo da tabela
            $('#table-agenda tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados do paciente.");
        }
    });
});

// Evento para editar agenda (usando delegation para funcionar após atualização do DOM)
$(document).on('click', '.edit-agenda', function (event) {
    event.preventDefault();
    const agendaId = $(this).data('id');
    window.location.href = `/odontologia/criaragenda/${agendaId}`;
});

$(document).on('click', '#add', function (event) {
    event.preventDefault();
    window.location.href = `/odontologia/criaragenda`;
});