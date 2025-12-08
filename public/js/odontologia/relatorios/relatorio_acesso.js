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
const itemsPerPage = 10;
let allAcessos = [];

function updatePagination() {
  const totalPages = Math.max(1, Math.ceil(allAcessos.length / itemsPerPage));
  $('#page-info').text(`Página ${currentPage} de ${totalPages}`);
  $('#prev-page').toggleClass('disabled', currentPage <= 1);
  $('#next-page').toggleClass('disabled', currentPage >= totalPages || allAcessos.length === 0);
  $('#no-appointments-message').toggle(allAcessos.length === 0);
  $('#listaAcessos').toggle(allAcessos.length > 0);
}

function listaAcessos(page) {
  const $tbody = $('#table tbody');
  $tbody.empty();

  if (allAcessos.length === 0) {
    updatePagination();
    return;
  }

  const totalPages = Math.max(1, Math.ceil(allAcessos.length / itemsPerPage));
  currentPage = Math.min(Math.max(1, page), totalPages);

  const start = (currentPage - 1) * itemsPerPage;
  const end = Math.min(start + itemsPerPage, allAcessos.length);

  for (let i = start; i < end; i++) {
    const a = allAcessos[i];
    $tbody.append(`
      <tr>
        <td>${a.CODIGO_USUARIO ?? ''}</td>
        <td>${a.USER_NAME ?? ''}</td>
        <td>${a.NOME ?? ''}</td>
        <td>${a.EVENT ?? ''}</td>
        <td>${formatDateStr(a.DATA_LOGIN ?? a.DATA_LOGOUT)}</td>
        <td>${maskTime(a.HORA_LOGIN ?? a.HORA_LOGOUT)}</td>
        <td>${a.STATUS ?? ''}</td>
      </tr>
    `);
  }

  updatePagination();
}

function carregarTodosAcessos() {
  // pegue os valores dos filtros
  const data_ini = $('#filtroDataInicio').val() || null; // YYYY-MM-DD
  const data_fim = $('#filtroDataFim').val() || null;    // YYYY-MM-DD
  const status = $('#filtroStatus').val() || null;
  const filtroUsuario = $('#filtroUsuario').val() || null;

  $.ajax({
    url: '/odontologia/relatorio/acessos',
    method: 'GET',
    dataType: 'json',
    data: { data_ini, data_fim, status, filtroUsuario },
    success: function (data) {
      allAcessos = data || [];
      listaAcessos(1);
    },
    error: function (xhr, s, e) {
      console.error('AJAX erro:', s, e, xhr?.responseText);
      alert('Erro ao buscar os acessos.'); 
    }
  });
}

$(function () {
  // paginação (um único binding)
  $('#prev-page').on('click', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return;
    listaAcessos(currentPage - 1);
  });

  $('#next-page').on('click', function (e) {
    e.preventDefault();
    if ($(this).hasClass('disabled')) return;
    listaAcessos(currentPage + 1);
  });

  // primeira carga (lista tudo)
  carregarTodosAcessos();
});

$('#form-search-service').on('submit', function (e) {
  e.preventDefault();
  carregarTodosAcessos(); 
});

// Limpar sem submeter
$('#btnLimpar').on('click', function () {
  $('#filtroDataInicio,#filtroDataFim').val('');
  $('#filtroStatus,#filtroPaciente').val('');
  carregarTodosAcessos();
});

// (opcional) impedir Enter de submeter
$('#form-search-service').on('keydown', function(e){
  if (e.key === 'Enter') { e.preventDefault(); }
});
