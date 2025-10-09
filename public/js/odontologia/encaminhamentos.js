const $select = $('#filtroAgendamento');
const $tbody = $('#table tbody');

function renderTabela(lista = []) {
    $tbody.empty();

    if (!Array.isArray(lista) || lista.length === 0) {
        $tbody.append(`
      <tr><td colspan="5" class="text-center text-muted">Nenhum encaminhamento encontrado.</td></tr>
    `);
        return;
    }

    lista.forEach(enc => {
        const html = `
      <tr class="row-enc" data-id="${enc.ID}">
        <td>${enc.DISCIPLINA ?? ''}</td>
        <td>${enc.ID_AGENDAMENTO ?? ''}</td>
        <td>${enc.ID_NOVO_AGENDAMENTO ?? ''}</td>
        <td class="cell-status">${enc.STATUS ?? ''}</td>
        <td>
          <button type="button" class="criarEncaminhamento btn btn-link p-0 m-0 border-0" style="color:inherit" data-id="${enc.ID}">
            <i class="fa-solid fa-square-caret-right"></i>
          </button>
        </td>
        <td>
          <button type="button" class="cancelaEncaminhamento btn btn-link p-0 m-0 border-0" style="color:inherit" data-id="${enc.ID}">
            <i class="fa-solid fa-trash"></i>
          </button>
        </td>
      </tr>`;
        $tbody.append(html);
    });
}

function fetchEncaminhamentos() {
    // texto do item selecionado no select2 (se houver)
    const selected = $select.select2('data');
    const q = (selected?.[0]?.text || '').trim();

    // status (radio)
    const status = $('input[name="statusEncaminhamento"]:checked').val() || 'DISPONIVEL';

    // (se quiser, adicione outros filtros aqui: disciplina, turma, etc.)
    $.getJSON('/odontologia/encaminhamentos', {
        query: q,
        statusEncaminhamento: status
    })
        .done(renderTabela)
        .fail(() => {
            $tbody.html('<tr><td colspan="5" class="text-danger text-center">Erro ao carregar os encaminhamentos.</td></tr>');
        });
}

$(document).ready(function () {
    // Select2 remoto
    $select.select2({
        placeholder: "Busque por agendamentos…",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '/odontologia/encaminhamentos',
            dataType: 'json',
            delay: 250,
            // já envia o status junto na busca do select
            data: params => ({
                query: params.term || '',
                statusEncaminhamento: $('input[name="statusEncaminhamento"]:checked').val() || 'DISPONIVEL'
            }),
            processResults: data => ({
                results: data.map(p => ({ id: p.ID, text: p.DISCIPLINA }))
            }),
            cache: true
        }
    });

    // Foco no search do select2 ao abrir
    $select.on('select2:open', () => {
        setTimeout(() => {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        }, 0);
    });

    // Dispara busca quando:
    // - mudar o item do select
    // - limpar o select
    // - mudar o status
    $select.on('select2:select select2:clear', fetchEncaminhamentos);
    $('input[name="statusEncaminhamento"]').on('change', fetchEncaminhamentos);

    // Carga inicial
    fetchEncaminhamentos();
});

$(document).ready(function () {
    const $filtroAgendamento = $('#filtroAgendamento');

    $filtroAgendamento.select2({
        placeholder: "Busque por agendamentos…",
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

    fetchEncaminhamentos();
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
            <label class="form-label mb-1">Dupla de alunos</label>
            <select class="form-select form-select-sm" name="aluno" required>
              <option value="">Selecione…</option>
            </select>
          </div>
          <div class="flex-grow-1">
            <label class="form-label mb-1">Dia</label>
            <input type="date" class="form-control form-control-sm" name="data" placeholder="">
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
        url: `/odontologia/encaminhamentos/${id}/gerar-agendamento`,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
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

    if (!disciplina || !turma || !box) {
        $aluno.prop('disabled', true).html('<option value="">Selecione turma e box…</option>');
        // mantém compatibilidade com seu return "promessa"
        return $.Deferred().resolve().promise();
    }

    const url = `/odontologia/disciplinas/${encodeURIComponent(disciplina)}/${encodeURIComponent(turma)}/${encodeURIComponent(box)}/alunos`;
    const previous = $aluno.val(); // tenta preservar escolha depois

    $aluno.prop('disabled', true).html('<option value="">Carregando…</option>');

    return $.getJSON(url)
        .done(lista => {
            if (!Array.isArray(lista) || lista.length === 0) {
                $aluno.html('<option value="">Sem alunos para os filtros</option>');
                return;
            }

            $aluno.empty().append('<option value="">Selecione…</option>');

            const vistos = new Set();
            const addOption = (a, b) => {
                const idA = String(a.id).trim(), idB = String(b.id).trim();
                const nomeA = String(a.nome).trim(), nomeB = String(b.nome).trim();
                if (!idA || !idB || !nomeA || !nomeB) return;

                const key = [idA, idB].sort().join('|');        // chave estável
                const label = `${nomeA}, ${nomeB}`;               // exibição: "Aluno A, Aluno B"

                if (!vistos.has(key)) {
                    const opt = new Option(label, key);
                    opt.dataset.id1 = idA; opt.dataset.nome1 = nomeA;
                    opt.dataset.id2 = idB; opt.dataset.nome2 = nomeB;
                    $aluno.append(opt);
                    vistos.add(key);
                }
            };

            let countCenario1 = 0;

            if (vistos.size === 0) {
                // --------- Carregando de alunos por ordem de chegada (pares sequenciais) ----------
                let pendente = null;
                lista.forEach(item => {
                    const id = item.ID ?? item.id ?? item.MATRICULA ?? item.aluno_id;
                    const nome = item.NOME ?? item.nome ?? item.text;
                    if (!id || !nome) return;

                    const curr = { id, nome };
                    if (!pendente) {
                        pendente = curr;
                    } else {
                        addOption(pendente, curr);
                        pendente = null;
                    }
                });

                if (pendente) {
                    // sobrou 1 sem par — útil pra depurar backend
                    console.warn('Aluno sem par (sobrando):', pendente);
                }
            }

            // feedback e UX
            if ($aluno.find('option').length === 1) {
                $aluno.html('<option value="">Sem duplas formadas</option>');
            } else {
                // tenta restaurar seleção anterior, se ainda existir
                if (previous && $aluno.find(`option[value="${previous}"]`).length) {
                    $aluno.val(previous);
                } else if ($aluno.find('option').length === 2) {
                    $aluno.prop('selectedIndex', 1);
                }
            }

            $aluno.prop('disabled', false);

        })
        .fail(() => {
            $aluno.html('<option value="">Erro ao carregar</option>');
        });
}

// 2) Botão "Cancelar" do mini-form
$tbody.on('click', '.fecharInline', function () {
    $(this).closest('tr.inline-form').remove();
});