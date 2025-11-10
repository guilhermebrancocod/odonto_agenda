<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Pacientes | FAESA</title>
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
            <legend class="w-auto px-2">Buscando pacientes</legend>
        </fieldset>
        <form id="form-search-patient" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Pesquisar</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="
  display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap; margin:20px 0;
  background:#f8f9fa; border:1px solid #e5e7eb; border-radius:10px; padding:12px 16px;
  box-shadow:0 1px 2px rgba(0,0,0,.04);
">
                <div class="input-group" style="flex:1 1 320px; min-width:260px; flex-direction:column;">
                    <label for="selectPatient" style="display:block; font-size:12px; color:#6b7280; margin-bottom:6px;">
                        Paciente
                    </label>
                    <div class="form-outline">
                        <select id="selectPatient" name="selectPatient"
                            style="width:100%; height:40px; padding:0 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; background:#fff; outline:none;">
                            <option></option>
                        </select>
                    </div>
                </div>

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
                <table class="table datatable-table" id="table-patient">
                    <thead class="datatable-header">
                        <tr style="padding-left: 1rem;">
                            <th>CPF</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div class="pagination-container" style="display: flex; justify-content: center; margin-top: 20px;">
                    <nav aria-label="Navegação de página">
                        <ul class="pagination">
                            <li class="page-item" id="prev-page">
                                <a class="page-link" href="#" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item" id="next-page">
                                <a class="page-link" href="#" aria-label="Próximo">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="ml-3 d-flex align-items-center">
                        <span id="page-info" style="margin-left: 10px;">Página <span id="current-page">1</span> de <span id="total-pages">1</span></span>
                    </div>
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
    <script type="module" src="/js/odontologia/consult_patient.js"></script>
</body>

</html>