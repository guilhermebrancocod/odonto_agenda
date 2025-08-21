<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Agendamentos</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f8f9fa;
        }
        #content-wrapper {
            height: calc(100vh - 56px); /* altura navbar */
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .report-container {
            width: 100%;
            max-width: 1200px;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .report-title {
            font-size: 28px;
            color: #333;
            margin: 0;
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <div class="report-container">
            <h2 class="report-title">Relatório de Agendamentos</h2>
            <!-- Se desejar inserir filtros, tabelas ou gráficos do relatório, adicione aqui -->
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/calendar.js"></script>
</body>

</html>
