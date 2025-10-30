// Variáveis de paginação
let currentPage = 1;
let itemsPerPage = 10;
let allBoxes = [];

function carregarTodosBox() {
    const $select = $('#selectBoxes');
    const $tbody = $('#table-box tbody');

    $.ajax({
        url: '/getBoxes',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            $tbody.empty();
            
            // Armazena todos os boxes
            allBoxes = data;

            // Adiciona ao select
            data.forEach(box => {
                const newOption = new Option(
                    box.DESCRICAO,
                    box.ID_BOX_CLINICA,
                    false,
                    false
                );
                $select.append(newOption);
            });

            $select.val(null).trigger('change');
            
            // Atualiza a paginação
            updatePagination();
            // Carrega a primeira página
            loadBoxesPage(currentPage);
        },
        error: function () {
            console.error('Erro ao carregar os serviços.');
        }
    });
}

// Função para atualizar a paginação
function updatePagination() {
    const totalPages = Math.ceil(allBoxes.length / itemsPerPage);
    $('#total-pages').text(totalPages);
    $('#current-page').text(currentPage);
    
    // Habilita/desabilita botões de navegação
    $('#prev-page').toggleClass('disabled', currentPage === 1);
    $('#next-page').toggleClass('disabled', currentPage === totalPages || totalPages === 0);
}

// Função para carregar uma página específica
function loadBoxesPage(page) {
    const $tbody = $('#table-box tbody');
    $tbody.empty();
    
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, allBoxes.length);
    
    // Exibe os boxes da página atual
    for (let i = startIndex; i < endIndex; i++) {
        const box = allBoxes[i];
        const html = `
            <tr>
                <td>${box.DESCRICAO}</td>
                <td>${box.ATIVO}</td>
                <td>
                    <button 
                        type="button" 
                        class="edit-box btn btn-link p-0 m-0 border-0" 
                        style="color: inherit;" 
                        data-id="${box.ID_BOX_CLINICA}">
                        <i class="fa fa-pencil-alt"></i>
                    </button>
                </td>
            </tr>
        `;
        $tbody.append(html);
    }
}

$(document).ready(function () {
    const $select = $('#selectBoxes');

    $select.select2({
        placeholder: "Busque o boxes",
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
    
    // Eventos de paginação
    $('#prev-page').on('click', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            loadBoxesPage(currentPage);
            updatePagination();
        }
    });
    
    $('#next-page').on('click', function(e) {
        e.preventDefault();
        const totalPages = Math.ceil(allBoxes.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            loadBoxesPage(currentPage);
            updatePagination();
        }
    });
    
    carregarTodosBox();
});

// Evento ao selecionar um paciente no select2
$('#selectBoxes').on('select2:select', function (e) {
    const boxId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/odontologia/boxes/${boxId}`, // boxId é a variável externa com o ID
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            // Normaliza o payload (objeto, array, ou objeto aninhado)
            const box = Array.isArray(data)
                ? data[0]
                : (data.box ?? data);

            if (!box) {
                console.warn('Resposta inesperada:', data);
                alert('Formato de resposta inesperado.');
                return;
            }

            const html = `
      <tr>
        <td>${box.DESCRICAO ?? ''}</td>
        <td>${box.ATIVO ?? ''}</td>
        <td>
          <button 
            type="button" 
            class="edit-box btn btn-link p-0 m-0 border-0" 
            style="color: inherit;" 
            data-id="${box.ID_BOX_CLINICA ?? ''}">
            <i class="fa fa-pencil-alt"></i>
          </button>
        </td>
      </tr>
    `;

            $('#table-box tbody').html(html);
        },
        error: function (xhr) {
            console.error('Erro', xhr);
            alert("Erro ao buscar os dados do box.");
        }
    });
});

// Evento para editar servico
$(document).on('click', '.edit-box', function (event) {
    event.preventDefault();
    const boxId = $(this).data('id');
    window.location.href = `/odontologia/criarbox/${boxId}`;
});

const addPatient = document.getElementById('add');

addPatient.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = '/odontologia/criarbox';
});

