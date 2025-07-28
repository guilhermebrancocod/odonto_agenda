<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Detalhes do Agendamento #{{ $agendamento->ID_AGENDAMENTO }}</title>
</head>
<body>
    @include('components.navbar')
    <div class="container mt-4">
        <h2 class="mb-4">Detalhes do Agendamento #{{ $agendamento->ID_AGENDAMENTO }}</h2>

        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th style="width: 20%;">Paciente</th>
                    <td class="text-break">{{ $agendamento->paciente->NOME_COMPL_PACIENTE ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Serviço</th>
                    <td class="text-break">{{ $agendamento->servico->SERVICO_CLINICA_DESC ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Clínica</th>
                    <td class="text-break">
                        @if ($agendamento->ID_CLINICA == 1)
                            Psicologia
                        @elseif ($agendamento->ID_CLINICA == 2)
                            Odontologia
                        @else
                            {{ $agendamento->clinica->NOME_CLINICA ?? '-' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Local</th>
                    <td class="text-break">{{ $agendamento->LOCAL ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Data</th>
                    <td>{{ \Carbon\Carbon::parse($agendamento->DT_AGEND)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Hora Início</th>
                    <td>{{ \Carbon\Carbon::parse($agendamento->HR_AGEND_INI)->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>Hora Fim</th>
                    <td>{{ \Carbon\Carbon::parse($agendamento->HR_AGEND_FIN)->format('H:i') }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $agendamento->STATUS_AGEND }}</td>
                </tr>
                <tr>
                    <th>Observações</th>
                    <td class="text-break">{{ $agendamento->OBSERVACOES ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <button class="btn btn-secondary" onclick="history.back()">Voltar</button>
    </div>
</body>
</html>
