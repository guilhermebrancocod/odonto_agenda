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
            <h2 style="margin: 0; font-size: 24px; color: #333;">Cadastro de Serviço</h2>
        </div>

        <!-- SE DER ERRO, MOSTRA NA TELA -->
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form class="row g-3 needs-validation" action="{{ route('criarServico-Psicologia') }}" method="POST">
            @csrf

            <!-- Envia o ID_CLINICA da sessão -->
            <input type="hidden" name="ID_CLINICA" value="1">

            <!-- TÍTULO FORMULÁRIO SERVIÇO -->
            <div class="linha-com-titulo">
                <h5>Dados Serviço</h5>
                <div class="linha-flex"></div>
            </div>

            <!-- SCRIPT PARA CAMPO DE PREENCHIMENTO DE CÓDIGO INTERNO DE SERVIÇO -->
            <script>
                document.getElementById('cod-interno-servico').addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9\-]/g, '');
                });
            </script>

            <!-- NOME DO SERVIÇO (DB: SERVICO_CLINICA_DESC) -->
            <div class="mb-3">
                <label for="nome-servico" class="form-label text-muted" style="font-size: 14px;">
                    Nome Serviço
                </label>
                <input type="text"
                    id="nome-servico"
                    name="NOME-SERVICO"
                    class="form-control">
            </div>

            <button id="salvar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                Salvar
            </button>

        </form>
    </div>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>