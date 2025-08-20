<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Document</title>
</head>

<style>
    html, body { height: 100%; margin: 0; }
        #content-wrapper {
            width: 85vw;
            height: 97vh;
            margin: auto;
            display: column;
            gap: 24px;
            overflow-y: auto;
            align-items: stretch;
        }
</style>

<body>
    @include('components.navbar')

    <div id="content-wrapper">

        <div class="bg-white p-4 rounded shadow-sm w-100">  

                <!-- TÍTULO -->
                <div class="text-center mb-5">
                    <h2 class="fs-4 mb-0">Psicólogos</h2>
                </div>

                <!-- FORMULÁIO DE PESQUISA -->
                <form id="search-form" class="w-100 mb-4">
                    <div class="row g-3">

                        <!-- PESQUISA POR NOME DO PSICOLOGO OU CPF -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input
                                    id="search-input"
                                    name="search"
                                    type="search"
                                    class="form-control"
                                    placeholder="Nome do Psicólogo"
                                />
                            </div>
                        </div>

                    <!-- DATA DE NASCIMENTO -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar-event"></i>
                            </span>
                            <input
                            id="DT_NASC_PACIENTE-input"
                            name="DT_NASC_PACIENTE"
                            type="text"
                            class="form-control"
                            placeholder="Data de Nascimento"
                            />
                        </div>
                    </div>

                    <!-- SEXO -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-arrow-down-circle"></i>
                            </span>
                            <select id="sexo" name="SEXO_PACIENTE" class="form-select form-select-sm">
                                <option value="">Sexo</option>
                                <option value="M">Masculino</option>
                                <option value="F">Feminino</option>
                                <option value="O">Outro</option>
                            </select>
                        </div>
                    </div>

                    <!-- TELEFONE -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-telephone"></i>
                            </span>
                            <input
                                id="telefone-input"
                                name="FONE_PACIENTE"
                                type="text"
                                class="form-control"
                                placeholder="Telefone"
                            />
                        </div>
                    </div>

                    <!-- BOTÃO DE PESQUISA -->
                    <div class="col-12 col-sm-6 col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm px-3 mb-2">Pesquisar</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 mb-2" id="btnCleanFilters">Limpar Filtros</button>
                    </div>

                </form>

                <hr>


                <!-- RESULTADOS - TABELA -->
                <div class="w-100">

                    <h5 class="mb-3">Resultados</h5>

                    <div class="table-responsive" style="max-height: 460px; overflow-x: auto;">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 150px;">Nome</th>
                                    <th class="text-nowrap">CPF</th>
                                    <th class="text-nowrap">Data de Nascimento</th>
                                    <th class="text-nowrap">Sexo</th>
                                    <th style="min-width: 120px;">Telefone</th>
                                    <th style="min-width: 180px;">Email</th>
                                    <th>Status</th>
                                    <th style="">Ações</th>
                                </tr>
                            </thead>

                            <!-- SEM PACIENTES -->
                            <tbody id="pacientes-tbody">
                                <tr>
                                    <td colspan="8" class="text-center">Nenhuma pesquisa realizada ainda.</td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <!-- SELEÇÃO DE TOTAL DE REGISTROS A SEREM MOSTRADOS -->
                    <div class="d-flex justify-content-between align-items-center mt-2">

                        <div id="contador-registros">

                        </div>
                    
                        <div id="limitador-registros">
                            <label for="limite-visualizacao" class="form-label me-2 mb-0">Mostrar:</label>
                            <select id="limite-visualizacao" class="form-select form-select-sm w-auto">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                </div>


            </div>  

    </div>

</body>
</html>