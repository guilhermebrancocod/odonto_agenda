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
    <div id="navbar-container">
    <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

        <!-- TITULO - PESQUISANDO -->
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; color: #333;">Pesquisando</h2>
        </div>
        
        <div class="linha-com-titulo">
            <h5>Paciente</h5>
            <div class="linha-flex"></div>
        </div>

        <!-- PESQUISA DE PACIENTE PARA CONSULTA DE AGENDAMENTOS -->
        <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
            <form action="{{ route('getPaciente') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap" style="margin:20px 0;">
                <div style="flex: 1;">
                    <input 
                        id="search-input" 
                        name="search" 
                        type="search" 
                        class="form-control" 
                        placeholder="Pesquisar paciente"
                        style="padding:8px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                        required
                    />
                </div>

                <div style="flex-shrink: 0;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size:14px; border-radius:6px;">
                        Pesquisar
                    </button>
                </div>
            </form>
        </div>
        
        <div class="linha-com-titulo">
            <h5>Resultado</h5>
            <div class="linha-flex"></div>
        </div>
        <div class="datatable" style="margin-top:25px">
            <table class="table datatable-table">
                <thead class="datatable-header">
                    <th>Cod</th>
                    <th>Nome</th>
                    <th>Serviço</th>
                    <th>Observação cadastral</th>
                    <th>Data/hora</th>
                    <th>Status</th>
                    <th>Editar</th>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Guilherme Pereira</td>
                        <td>Retirar ciso</td>
                        <td>Chega 10 min atrasado</td>
                        <td>26/08/2025 - 18h30</td>
                        <td>Agendado</td>
                        <td><i class="fa fa-pencil-alt"></i></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Roberto de Campos</td>
                        <td>Limpeza</td>
                        <td></td>
                        <td>26/07/2025 - 10h30</td>
                        <td>Agendado</td>
                        <td><i class="fa fa-pencil-alt"></i></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Roberto de Campos</td>
                        <td>Limpeza</td>
                        <td></td>
                        <td>26/05/2025 - 10h30</td>
                        <td>Concluido</td>
                        <td><i class="fa fa-pencil-alt"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>