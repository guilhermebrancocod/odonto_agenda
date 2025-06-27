<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>
    <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; color: #333;">Agendamento</h2>
        </div>
        <form class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Paciente</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <form action="{{ route('selectPatient') }}" method="GET">
                    <div class="input-group" style="flex: 1;">
                        <div class="form-outline" data-mdb-input-init>
                            <input id="search-input" type="search" class="form-control"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" />
                            <label class="form-label" for="search-input">Pesquisar</label>
                        </div>
                    </div>
                </form>
                <div style="flex-shrink: 0;">
                    <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                        Cadastrar
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Horário</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin: 20px 0;">
                <tr>
                    <div style="flex: 0.1">
                        <label for="data" style="font-size: 14px; color: #666;">Dia</label>
                        <input type="date" id="data" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="flex: 0.1">
                        <label for="data" style="font-size: 14px; color: #666;">Dia</label>
                        <input type="date" id="data" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="flex: 0.2">
                        <label for="hr_ini" style="font-size: 14px; color: #666;">Horário Início</label>
                        <input type="text" id="horario" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="flex: 0.2">
                        <label for="hr_fim" style="font-size: 14px; color: #666;">Horário Fim</label>
                        <input type="text" id="horario" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="flex: 0.2">
                        <label for="tipo" style="font-size: 14px; color: #666;">Tipo</label>
                        <input type="text" id="tipo" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                    </div>
                    <div style="text-align: right;flex:0.3">
                        <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                            Agendar
                        </button>
                    </div>
                </tr>
            </div>
            <div class="linha-com-titulo">
                <h5></h5>
                <div class="linha-flex"></div>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>