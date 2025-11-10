<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Agenda | FAESA</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    @include('partials.sidebar')
    <div style="margin-left:225px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Buscando agendamento</legend>
        </fieldset>
        <form id="form-search-patient" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Pesquisar</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="
  display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end; margin:20px 0;
  background:#f8f9fa; border:1px solid #e5e7eb; border-radius:10px; padding:12px 16px;
  box-shadow:0 1px 2px rgba(0,0,0,.04);
">
                <!-- Turma -->
                <div style="flex:1 1 220px; min-width:200px;">
                    <label for="selectTurma" style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">
                        Turma
                    </label>
                    <select id="selectTurma" name="selectTurma"
                        style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; background:#fff; outline:none;">
                        <option></option>
                    </select>
                </div>

                <!-- Paciente -->
                <div style="flex:2 1 340px; min-width:260px;">
                    <label for="selectPatient" style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">
                        Paciente
                    </label>
                    <select id="selectPatient" name="selectPatient"
                        style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; background:#fff; outline:none;">
                        <option></option>
                    </select>
                </div>

                <!-- Botões -->
                <div style="display:flex; gap:10px; flex-shrink:0; margin-left:auto;">
                    <button type="submit" id="reload" title="Limpar"
                        style="display:inline-flex; align-items:center; justify-content:center; gap:8px;
             background:#ffffff; color:#374151; border:1px solid #d1d5db; padding:0 14px; height:40px;
             font-size:14px; font-weight:500; border-radius:8px; cursor:pointer;">
                        <iconify-icon icon="streamline:arrow-round-left-solid" style="font-size:18px;"></iconify-icon>
                        <span style="display:inline-block;">Limpar</span>
                    </button>

                    <button type="submit" id="add" title="Adicionar paciente"
                        style="display:inline-flex; align-items:center; justify-content:center; gap:8px;
             background:#0d6efd; color:#fff; border:1px solid #0d6efd; padding:0 14px; height:40px;
             font-size:14px; font-weight:500; border-radius:8px; cursor:pointer;">
                        <iconify-icon icon="ix:add-circle-filled" style="font-size:18px;"></iconify-icon>
                        <span style="display:inline-block;">Adicionar</span>
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Resultado</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="datatable" style="margin-top:15px">
                <table class="table datatable-table" id="table-agenda">
                    <thead class="datatable-header">
                        <tr style="padding-left: 1rem;">
                            <th>Nome</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Serviço</th>
                            <th>Turma</th>
                            <th>Telefone</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="no-appointments-message" style="display: none; text-align: center; padding: 20px; font-size: 16px;">
                    Sem agendamentos
                </div>
                <div class="pagination-container">
                    <nav aria-label="Navegação de página">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled" id="prev-page">
                                <a class="page-link" href="#" tabindex="-1">&laquo;</a>
                            </li>
                            <li class="page-item" id="next-page">
                                <a class="page-link" href="#">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                    <div id="page-info" class="text-center mt-2">Página 1 de 1</div>
                </div>
            </div>
        </form>
    </div>
    <!-- jQuery primeiro -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 principal -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Idioma português (DEPOIS do Select2 principal) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>

    <!-- Outros scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script type="module" src="/js/odontologia/consult_agenda.js"></script>

    <script>

    </script>
</body>

</html>