const $select = $('#listaEncaminhamentos');
const $tbody = $('#table-service tbody');

function carregarTodosEncaminhamentos() {

    $.ajax({
        url: '/odontologia/encaminhamentos',
        dataType: 'json',
        data: { query: '' },
        success: function (data) {
            $select.empty();
            $tbody.empty();

            data.forEach(encaminhamento => {
                // Adiciona ao select
                const newOption = new Option(
                    encaminhamento.DISCIPLINA,
                    encaminhamento.ID,
                    false,
                    false
                );
                $select.append(newOption);
                const html = `
                    <tr class="row-enc data-id="${encaminhamento.ID}">
                        <td>${encaminhamento.DISCIPLINA}</td>
                        <td>${encaminhamento.ID_AGENDAMENTO}</td>
                        <td>${encaminhamento.STATUS}</td>
                        <td>
                            <button 
                                type="button" 
                                class="criarEncaminhamento btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${encaminhamento.ID}">
                                <i class="fa-solid fa-square-caret-right"></i>
                            </button>
                        </td>
                        <td>
                            <button 
                                type="button"
                                class="cancelaEncaminhamento btn btn-link p-0 m-0 border-0" 
                                style="color: inherit;" 
                                data-id="${encaminhamento.ID}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(html);
            });

            $select.val(null).trigger('change');
        },
        error: function () {
            console.error('Erro ao carregar os encaminhamentos.');
        }
    });
}

$(document).ready(function () {
    const $select = $('#listaEncaminhamentos');

    $select.select2({
        placeholder: "Selecione o encaminhamento",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/odontologia/encaminhamentos',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { query: params.term || '' };
            },
            processResults: function (data) {
                return {
                    results: data.map(p => ({
                        id: p.ID,
                        text: p.DISCIPLINA,

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

    carregarTodosEncaminhamentos();
});

// Evento ao selecionar um paciente no select2
$('#listaEncaminhamentos').on('select2:select', function (e) {
    const servicoId = e.params.data.id;

});

// mini-form inline (ajuste campos conforme sua regra)
function inlineFormTemplate({ id, disciplina }) {
    return `
    <tr class="inline-form" data-parent="${id}">
      <td colspan="5">
        <form class="form-inline-enc d-flex flex-wrap align-items-end gap-2" data-disciplina="${disciplina}">
          <input type="hidden" name="id" value="${id}">
          <div>
            <label class="form-label mb-1">Disciplina</label>
            <input type="text" class="form-control form-control-sm" name="disciplina" value="${disciplina}" readonly>
          </div>
          <div>
            <label class="form-label mb-1">Box</label>
            <select class="form-select form-select-sm" name="box" required>
              <option value="">Selecione…</option>
            </select>
          </div>
          <div>
            <label class="form-label mb-1">Dia da Semana</label>
            <select class="form-select form-select-sm" name="diasemana" required>
              <option value="">Selecione…</option>
            </select>
          </div>
          <div>
            <label class="form-label mb-1">Turma</label>
            <select class="form-select form-select-sm" name="turma" required>
              <option value="">Selecione…</option>
            </select>
          </div>
          <div class="flex-grow-1">
            <label class="form-label mb-1">Alunos</label>
            <select class="form-select form-select-sm" name="aluno" required>
              <option value="">Selecione…</option>
            </select>
          </div>
          <div class="flex-grow-1">
            <label class="form-label mb-1">Dia</label>
            <input type="text" class="form-control form-control-sm" name="obs" placeholder="">
          </div>
          <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-light btn-sm fecharInline">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-sm">Salvar</button>
          </div>
        </form>
      </td>
    </tr>
  `;
}

// 1) Clique: expandir/fechar mini-form
$tbody.on('click', '.criarEncaminhamento', function () {
    const $tr = $(this).closest('tr');
    const id = $(this).data('id');

    const $aberto = $tbody.find('tr.inline-form');
    if ($aberto.length && $aberto.prev()[0] !== $tr[0]) $aberto.remove();

    const $next = $tr.next('.inline-form');
    if ($next.length) { $next.remove(); return; }

    const disciplina = $tr.children().eq(0).text().trim();
    $tr.after(inlineFormTemplate({ id, disciplina }));

    const $form = $tr.next('.inline-form').find('form');

    const pBoxes = carregarBoxesPorDisciplina($form);
    const pSemana = carregarDiaDaSemanaPorDisciplina($form);
    const pTurma = carregarTurmaPorDisciplina($form);

    $.when(pBoxes, pSemana, pTurma).done(function () {
          $form.on('change', 'select[name="turma"], select[name="box"]', () => carregarAlunos($form));
    });
});

// 3) Submit do mini-form (efetivar encaminhamento)
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$tbody.on('submit', '.form-inline-enc', function (e) {
    e.preventDefault();
    const $form = $(this);
    const dados = Object.fromEntries(new FormData(this).entries());
    const id = dados.id;

    $.ajax({
        url: `/odontologia/encaminhamentos/${id}/gerar-agendamento`, // ajuste sua rota
        type: 'POST',
        dataType: 'json',
        data: dados,
        beforeSend: () => $form.find('button[type=submit]').prop('disabled', true).text('Salvando…'),
        success: (resp) => {
            // atualizar status na linha principal e fechar o inline
            const $row = $tbody.find(`tr.row-enc[data-id="${id}"]`);
            $row.find('.cell-status').text(resp?.STATUS ?? 'ACEITO');
            $row.next('.inline-form').remove();
        },
        error: (xhr) => {
            alert(xhr?.responseJSON?.error || 'Erro ao salvar o encaminhamento.');
        },
        complete: () => $form.find('button[type=submit]').prop('disabled', false).text('Salvar')
    });
});

function carregarBoxesPorDisciplina($form) {

    const disciplina = $form.find('[name="disciplina"]').val();
    const $box = $form.find('select[name="box"]');
    $box.prop('disabled', true).html('<option value="">Carregando…</option>');

    return $.getJSON(`/odontologia/disciplinas/${encodeURIComponent(disciplina)}/boxes`)
        .done(lista => {
            if (!Array.isArray(lista) || lista.length === 0) {
                $box.html('<option value="">Sem BOX para a disciplina</option>');
                return;
            }
            $box.empty().append('<option value="">Selecione…</option>');
            lista.forEach(item => $box.append(new Option(item.text, item.id)));
            $box.prop('disabled', false);
            if (lista.length === 1) $box.val(lista[0].id);
        })
        .fail(() => $box.html('<option value="">Erro ao carregar</option>'));
}

function carregarDiaDaSemanaPorDisciplina($form) {

    const disciplina = $form.find('[name="disciplina"]').val();
    const $diaSemana = $form.find('[name="diasemana"]');
    $diaSemana.prop('disabled', true).html('<option value="">Carregando…</option>');

    return $.getJSON(`/odontologia/disciplinas/${encodeURIComponent(disciplina)}/diasemana`)
        .done(lista => {
            $diaSemana.empty().append('<option value="">Selecione…</option>');
            (lista || []).forEach(item => $diaSemana.append(new Option(item.DIA_SEMANA)));
            $diaSemana.prop('disabled', false);
        })
        .fail(() => $diaSemana.html('<option value="">Erro ao carregar</option>'));
}

function carregarTurmaPorDisciplina($form) {

    const disciplina = $form.find('[name="disciplina"]').val();
    const $turma = $form.find('select[name="turma"]');
    $turma.prop('disabled', true).html('<option value="">Carregando…</option>');

    return $.getJSON(`/odontologia/disciplinas/${encodeURIComponent(disciplina)}/turmas`)
        .done(lista => {
            if (!Array.isArray(lista) || lista.length === 0) {
                $turma.html('<option value="">Sem TURMA para a disciplina</option>');
                return;
            }
            $turma.empty().append('<option value="">Selecione…</option>');
            lista.forEach(item => $turma.append(new Option(item.TURMA)));
            $turma.prop('disabled', false);
            if (lista.length === 1) $turma.val(lista[0].TURMA);
        })
        .fail(() => $turma.html('<option value="">Erro ao carregar</option>'));
}

function carregarAlunos($form) {
    const disciplina = $form.find('[name="disciplina"]').val();
    const turma = $form.find('select[name="turma"]').val();
    const box = $form.find('select[name="box"]').val();
    const $aluno = $form.find('select[name="aluno"]');

    console.log('disciplina:', $form.find('[name="disciplina"]').val());
    console.log('turma:', $form.find('select[name="turma"]').val());
    console.log('box:', $form.find('select[name="box"]').val());
    console.log('tem select aluno?', $form.find('select[name="aluno"]').length);

    if (!disciplina || !turma || !box) {
        $aluno.prop('disabled', true).html('<option value="">Selecione turma e box…</option>');
        return $.Deferred().resolve().promise();
    }

    $aluno.prop('disabled', true).html('<option value="">Carregando…</option>');

    const url = `/odontologia/disciplinas/${encodeURIComponent(disciplina)}/${encodeURIComponent(turma)}/${encodeURIComponent(box)}/alunos`;

    $.getJSON(url)
        .done(lista => {
            if (!Array.isArray(lista) || lista.length === 0) {
                $aluno.html('<option value="">Sem alunos para os filtros</option>');
                return;
            }
            $aluno.empty().append('<option value="">Selecione…</option>');

            const vistos = new Set();
            lista.forEach(item => {
                const id = item.ID ?? item.id ?? item.MATRICULA ?? item.aluno_id;
                const nome = item.NOME ?? item.nome ?? item.text;
                if (id != null && nome && !vistos.has(id)) {
                    $aluno.append(new Option(String(nome).trim(), String(id)));
                    vistos.add(id);
                }
            });

            $aluno.prop('disabled', false);
            if ($aluno.find('option').length === 2) $aluno.prop('selectedIndex', 1);
        })
        .fail(() => $aluno.html('<option value="">Erro ao carregar</option>'));
}

// 2) Botão "Cancelar" do mini-form
$tbody.on('click', '.fecharInline', function () {
    $(this).closest('tr.inline-form').remove();
});