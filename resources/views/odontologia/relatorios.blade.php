<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | FAESA</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @include('partials.sidebar')
    <div style="margin-left:225px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Relatorios</legend>
        </fieldset>
        <form id="form-search-service" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Pesquisar</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="datatable" style="margin-top:15px">
                <table class="table datatable-table" id="table">
                    <thead class="datatable-header">
                        <tr style="padding-left: 1rem;">
                            <th style="width: 70%;">Título</th>
                            <th style="width: 20%;">Módulo</th>
                            <th style="width: 10%;">Visualizar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="row-data">
                            <td>Relatório de Agendamento</td>
                            <td>Agendamento</td>
                            <td><a href="/relatorio/agendamentos"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>
                        <tr id="row-data">
                            <td>Relatório de Encaminhamento</td>
                            <td>Encaminhamento</td>
                            <td><a href="/relatorio/encaminhamentos"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>
                        <tr id="row-data">
                            <td>Relatório Financeiro</td>
                            <td>Financeiro</td>
                            <td><a href="/relatorio/financeiro"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>
                        <tr id="row-data">
                            <td>Relatório de Acesso</td>
                            <td>Controle</td>
                            <td><a href="/relatorio/acessos"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>
                        <!--<tr id="row-data">
                            <td>Relatório de Box</td>
                            <td>Cadastro</td>
                            <td><a href="/relatorio/box"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>
                        <tr id="row-data">
                            <td>Relatório de Usuário</td>
                            <td>Cadastro</td>
                            <td><a href="/relatorio/usuarios"><i class="fa-regular fa-eye"></i></a></td>
                        </tr>-->
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
    <script type="module" src="/js/odontologia/relatorios.js"></script>
</body>

</html>