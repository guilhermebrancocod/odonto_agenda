$(document).ready(function () {
    const $select = $('#selectUserLyceum');

    $select.select2({
        placeholder: "Busque os Usuários no Lyceum",
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: '/getUserLyceum',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.PESSOA,
                        text: p.NOME_COMPL,
                        person: p.PESSOA,
                        user: p.WINUSUARIO
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

     $select.on('select2:select', function (e) {
        const d = e.params.data || {};
        console.log('Selecionado no select2:', d);
        $('#nome').val(d.text || '');
        $('#winusuario').val(d.user || '');
        $('#pessoa').val(d.person || ''); // usa o id correto do input (veja abaixo)
    });

    // Limpar campos ao limpar o select
    $select.on('select2:clear', function () {
        $('#nome, #winusuario, #pessoa').val('');
    });
});

$('#selectUserLyceum').on('select2:select', function (e) {
    const userId = e.params.data.id;

    // Busca os dados completos do paciente via AJAX
    $.ajax({
        url: `/getUserLyceum/${userId}`,
        type: 'GET',
        dataType: 'json',
        success: function (user) {
            const html = `
                <tr>
                    <td>${user.NOME_COMPL}</td>
                    <td>${user.WINUSUARIO} ?? ''</td>
                    <td>${user.PESSOA}</td>
                </tr>
            `;

            // Atualiza o corpo da tabela com apenas o paciente selecionado
            $('#table-user tbody').html(html);
        },
        error: function () {
            alert("Erro ao buscar os dados do usuario.");
        }
    });
});
