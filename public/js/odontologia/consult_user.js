function carregarTodosBox() {
    const $select = $('#selectUser');
    const $tbody = $('#table-user tbody');

    $.ajax({
        url: '/getUser',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            $tbody.empty();

            data.forEach(user => {
                // Adiciona ao select
                const newOption = new Option(
                    user.NOME,
                    user.ID,
                    false,
                    false
                );
                $select.append(newOption);

                // Adiciona à tabela
                const html = `
                    <tr>
                        <td>${user.NOME}</td>
                        <td>${user.USUARIO}</td>
                        <td>${user.PESSOA || ''}</td>
                        <td>${user.TIPO || ''}</td>
                        <td>${user.STATUS}</td>
                        <td>
                            <button 
                                type="button" 
                                class="edit-user btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${user.ID}">
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
    const $select = $('#selectUser');

    $select.select2({
        placeholder: "Busque os Usuários",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/getUser',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID,
                        text: p.NOME

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
$('#selectUser').on('select2:select', function (e) {
    const userId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/odontologia/user/${userId}`,
        type: 'GET',
        dataType: 'json',
        success: function (userId) {
            const html = `
                    <tr>
                        <td>${userId.NOME}</td>
                        <td>${userId.USUARIO}</td>
                        <td>${userId.PESSOA}</td>
                        <td>${userId.TIPO}</td>
                        <td>${userId.STATUS}</td>
                        <td>
                            <button 
                                type="button" 
                                class="edit-user btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${userId.ID}">
                                <i class="fa fa-pencil-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            // Atualiza o corpo da tabela com apenas o paciente selecionado
            $('#table-box tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados do box.");
        }
    });
});

// Evento para editar servico
$(document).on('click', '.edit-user', function (event) {
    event.preventDefault();
    const userId = $(this).data('id');
    window.location.href = `/odontologia/criarusuario/${userId}`;
});

const addUser = document.getElementById('add');

addUser.addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = '/odontologia/criarusuario';
});

