<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultando Procedimentos</title>
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
            <legend class="w-auto px-2">Buscando procedimentos</legend>
        </fieldset>
        <form id="form-search-service" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Pesquisar</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <div class="input-group" style="flex: 1; flex-direction: column;">
                    <div class="form-outline">
                        <select id="selectService" name="selectService" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            <option></option>
                        </select>
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='reload' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Limpar">
                        <iconify-icon icon="streamline:arrow-round-left-solid"></iconify-icon>
                    </button>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='add' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Adicionar serviço">
                        <iconify-icon icon="ix:add-circle-filled"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Resultado</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="datatable" style="margin-top:15px">
                <table class="table datatable-table" id="table-service">
                    <thead class="datatable-header">
                        <tr style="padding-left: 1rem;">
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Ativo</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="row-empty">
                            <td colspan="4" class="text-center text-muted">Carregando procedimentos...</td>
                        </tr>
                    </tbody>
                </table>
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
    <script type="module" src="/js/odontologia/consult_service.js"></script>
</body>

</html>