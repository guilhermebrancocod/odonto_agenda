function maskTime(value) {
  return (value || '')
    .replace(/\D/g, '')
    .replace(/^(\d{2})(\d)/, '$1:$2')
    .replace(/^(\d{2}):(\d{2}).*/, '$1:$2');
}

function formatDateStr(value) {
  if (!value) return '';
  const parts = String(value).split('-');
  return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value;
}

let currentPage = 1;
const itemsPerPage = 500;
let allAgendamentos = [];

function updatePagination() {
  const totalPages = Math.max(1, Math.ceil(allAgendamentos.length / itemsPerPage));
  $('#page-info').text(`Página ${currentPage} de ${totalPages}`);
  $('#prev-page').toggleClass('disabled', currentPage <= 1);
  $('#next-page').toggleClass('disabled', currentPage >= totalPages || allAgendamentos.length === 0);
  $('#no-appointments-message').toggle(allAgendamentos.length === 0);
  $('#listaAgenda').toggle(allAgendamentos.length > 0);
}

function listaAgendamentos(page) {
  const $tbody = $('#table tbody');
  $tbody.empty();

  if (allAgendamentos.length === 0) {
    updatePagination();
    return;
  }

  const totalPages = Math.max(1, Math.ceil(allAgendamentos.length / itemsPerPage));
  currentPage = Math.min(Math.max(1, page), totalPages);

  const start = (currentPage - 1) * itemsPerPage;
  const end = Math.min(start + itemsPerPage, allAgendamentos.length);

  for (let i = start; i < end; i++) {
    const a = allAgendamentos[i];
    $tbody.append(`
      <tr>
        <td>${a.CODIGO ?? ''}</td>
        <td>${a.PACIENTE ?? ''}</td>
        <td>${formatDateStr(a.DATA ?? '')}</td>
        <td>${maskTime(a.HORA_INICIO ?? '')}</td>
        <td>${maskTime(a.HORA_FIM ?? '')}</td>
        <td>${a.STATUS ?? ''}</td>
      </tr>
    `);
  }

  updatePagination();
}

function carregarTodosAgendamentos() {
  // pegue os valores dos filtros
  const data_ini = $('#filtroDataInicio').val() || null; // YYYY-MM-DD
  const data_fim = $('#filtroDataFim').val() || null;    // YYYY-MM-DD
  const status = $('#filtroStatus').val() || null;
  const filtroPaciente = $('#filtroPaciente').val() || null;

  $.ajax({
    url: '/odontologia/relatorio/agendamentos',
    method: 'GET',
    dataType: 'json',
    data: { data_ini, data_fim, status, filtroPaciente },
    success: function (data) {
      allAgendamentos = data || [];
      listaAgendamentos(1);
    },
    error: function (xhr, s, e) {
      console.error('AJAX erro:', s, e, xhr?.responseText);
      alert('Erro ao buscar os agendamentos.');
    }
  });
}

$(function () {
  // paginação (um único binding)
  $('#prev-page').on('click', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return;
    listaAgendamentos(currentPage - 1);
  });

  $('#next-page').on('click', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return;
    listaAgendamentos(currentPage + 1);
  });

  // primeira carga (lista tudo)
  carregarTodosAgendamentos();
});

$('#form-search-service').on('submit', function (e) {
  e.preventDefault();
  carregarTodosAgendamentos();
});

// Limpar sem submeter
$('#btnLimpar').on('click', function () {
  $('#filtroDataInicio,#filtroDataFim').val('');
  $('#filtroStatus,#filtroPaciente').val('');
  carregarTodosAgendamentos();
});

// (opcional) impedir Enter de submeter
$('#form-search-service').on('keydown', function(e){
  if (e.key === 'Enter') { e.preventDefault(); }
});
