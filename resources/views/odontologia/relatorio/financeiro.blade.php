<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios | Financeiro | FAESA</title>
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
        <fieldset class="d-flex justify-content-betweenborder p-3 rounded mb-3">
            <legend class="w-auto px-2">Relatórios | Financeiro</legend>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary align-self-end" style="position: absolute; right: 30px;" id="btnImprimir"> Imprimir <i class="fa-solid fa-print"></i></button>
            </div>
        </fieldset>
        <form id="form-search-service" class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Filtros</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="card p-3 rounded mb-3">
                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div class="d-flex flex-column">
                        <label class="form-label mb-1" for="filtroDataIni">Data inicial</label>
                        <input id="filtroDataIni" type="date" class="form-control">
                    </div>

                    <div class="d-flex flex-column">
                        <label class="form-label mb-1" for="filtroDataFim">Data final</label>
                        <input id="filtroDataFim" type="date" class="form-control">
                    </div>

                    <div class="d-flex flex-column">
                        <label class="form-label mb-1" for="filtroStatus">Pago</label>
                        <select id="filtroStatus" class="form-select">
                            <option value="">Todos</option>
                            <option value = '1'>SIM</option>
                            <option value = '0'>NÃO</option>
                        </select>
                    </div>
                    <div class="d-flex flex-column">
                        <label class="form-label mb-1" for="filtroPaciente">Paciente</label>
                        <input id="filtroPaciente" type="text" class="form-control" placeholder="Todos">
                    </div>

                    <!-- empurra os botões para a direita -->
                    <div class="ms-auto d-flex gap-2">
                        <button id="btnFiltrar" type="submit" class="btn btn-primary">Filtrar</button>
                        <button id="btnLimpar" type="button" class="btn btn-outline-secondary">Limpar</button>
                    </div>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Resultado</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="datatable" style="margin-top:15px">
                <table class="table datatable-table" id="table">
                    <thead class="datatable-header">
                        <tr>
                            <th>Código</th>
                            <th>Paciente</th>
                            <th>Data de Vencimento</th>
                            <th>Valor</th>
                            <th>Condição de pagamento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div id="no-appointments-message" style="display: none; text-align: center; padding: 20px; font-size: 16px;">
                    Nenhum registro encontrado.
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
            <div class="d-flex justify-content-between">
                <a href="{{ url('odontologia/relatorios') }}" class="btn btn-primary" id="voltar">
                    Voltar
                </a>
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
    <script type="module" src="/js/odontologia/relatorios/relatorio_encaminhamento.js"></script>
</body>

</html>