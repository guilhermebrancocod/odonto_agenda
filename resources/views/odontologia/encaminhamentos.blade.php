<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encaminhamentos</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @include('components.sidebar')
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Encaminhamentos</legend>
        </fieldset>
        <form id="form-search-service" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Pesquisar</h5>
                <div class="linha-flex"></div>
            </div>
            <!-- Card de Filtros -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <!-- Select 1 -->
                        <div class="col-12 col-md-6">
                            <label for="filtroAgendamento" class="form-label mb-1">Agendamento</label>
                            <select id="filtroAgendamento" name="filtroAgendamento" class="form-select form-select-sm">
                                <option value="">Selecione…</option>
                                <!-- opções via JS -->
                            </select>
                            <div class="form-text">Escolha o agendamento desejado.</div>
                        </div>

                        <!-- Select 2 (duplas/alunos/outro filtro) -->
                        <div class="col-12 col-md-6">
                            <label for="filtroDupla" class="form-label mb-1">Dupla/Alunos</label>
                            <select id="filtroDupla" name="filtroDupla" class="form-select form-select-sm">
                                <option value="">Selecione…</option>
                                <!-- opções via JS -->
                            </select>
                            <div class="form-text">Mostra as duplas disponíveis para o filtro.</div>
                        </div>

                        <!-- Grupo de status como “pílulas” -->
                        <div class="col-12">
                            <label class="form-label d-block mb-1">Status</label>

                            <div class="btn-group" role="group" aria-label="Filtro de status">
                                <input type="radio" class="btn-check" name="statusEncaminhamento" id="statusDisp" value="DISPONIVEL" autocomplete="off" checked>
                                <label class="btn btn-outline-primary btn-sm" for="statusDisp">
                                    <i class="fa-solid fa-check me-1"></i>Disponível
                                </label>

                                <input type="radio" class="btn-check" name="statusEncaminhamento" id="statusReag" value="REAGENDADO" autocomplete="off">
                                <label class="btn btn-outline-primary btn-sm" for="statusReag">
                                    <i class="fa-solid fa-arrows-rotate me-1"></i>Reagendado
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="datatable" style="margin-top:15px">
                <table class="table datatable-table" id="table">
                    <thead class="datatable-header">
                        <tr style="padding-left: 1rem;">
                            <th>Disciplina</th>
                            <th>Agendamento Origem</th>
                            <th>Status</th>
                            <th>Encaminhar</th>
                            <th>Cancelar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="row-empty">
                            <td colspan="5" class="text-center text-muted">Carregando encaminhamentos...</td>
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
    <script type="module" src="/js/odontologia/encaminhamentos.js"></script>
</body>

</html>