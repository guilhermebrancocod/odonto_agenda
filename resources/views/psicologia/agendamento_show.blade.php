<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
</head>
<body>
    @include('components.navbar')
    <div class="container mt-4">
    <h2>Detalhes do Agendamento #{{ $agendamento->ID_AGENDAMENTO }}</h2>

    <table class="table table-bordered">
        <tr>
            <th>Paciente</th>
            <td>{{ $agendamento->paciente->NOME_COMPL_PACIENTE ?? '-' }}</td>
        </tr>
        <tr>
            <th>Serviço</th>
            <td>{{ $agendamento->servico->SERVICO_CLINICA_DESC ?? '-' }}</td>
        </tr>
        <tr>
            <th>Clínica</th>
            <td>{{ $agendamento->clinica->NOME_CLINICA ?? '-' }}</td>
        </tr>
        <tr>
            <th>Data</th>
            <td>{{ \Carbon\Carbon::parse($agendamento->DT_AGEND)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <th>Hora Início</th>
            <td>{{ $agendamento->HR_AGEND_INI }}</td>
        </tr>
        <tr>
            <th>Hora Fim</th>
            <td>{{ $agendamento->HR_AGEND_FIN }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $agendamento->STATUS_AGEND }}</td>
        </tr>
        <tr>
            <th>Observações</th>
            <td>{{ $agendamento->OBSERVACOES ?? '-' }}</td>
        </tr>
    </table>

    <button class="btn btn-secondary" onclick="history.back()">Voltar</button>
</div>
</body>
</html>