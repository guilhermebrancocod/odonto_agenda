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

        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; color: #333;">Pesquisando</h2>
        </div>
        
        <div class="linha-com-titulo">
            <h5>Paciente</h5>
            <div class="linha-flex"></div>
        </div>

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
                    <th>CPF</th>
                    <th>Data de Nascimento</th>
                    <th>Sexo</th>
                    <th>Telefone</th>
                    <th>Email</th>
                    <th>Ações</th>
                </thead>
                <tbody>
                    @if(isset($pacientes) && count($pacientes) > 0)
                        @foreach($pacientes as $paciente)
                            <tr>
                                <td>{{ $paciente->ID_PACIENTE }}</td>
                                <td>{{ $paciente->NOME_COMPL_PACIENTE }}</td>
                                <td>{{ $paciente->CPF_PACIENTE }}</td>
                                <td>{{ $paciente->DT_NASC_PACIENTE ? $paciente->DT_NASC_PACIENTE->format('d/m/Y') : '' }}</td>
                                <td>{{ $paciente->SEXO_PACIENTE }}</td>
                                <td>{{ $paciente->FONE_PACIENTE ?? '-' }}</td>
                                <td>{{ $paciente->E_MAIL_PACIENTE ?? '-' }}</td>
                                <td><a href="#" class="btn btn-sm btn-primary">Editar</a></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center">Nenhum paciente encontrado.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>
</html>
