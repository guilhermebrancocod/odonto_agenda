<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Serviço</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        #content-wrapper {
            height: calc(100vh - 56px); /* altura navbar */
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: center; /* Centraliza verticalmente */
            background-color: #f8f9fa;
        }
        .form-container {
            width: 100%;
            max-width: 700px;
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <main class="form-container">
            <div class="text-center mb-4">
                <h2 class="fs-4 mb-0" style="color: #333;">Cadastro de Serviço</h2>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
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

            <form action="{{ route('criarServico-Psicologia') }}" method="POST" class="needs-validation" novalidate>
                @csrf

                <input type="hidden" name="ID_CLINICA" value="1">

                <h5>Dados Serviço</h5>
                <hr />

                <div class="mb-3">
                    <label for="cod-interno-servico" class="form-label text-muted" style="font-size: 14px;">
                        Código Controle Interno
                    </label>
                    <input type="text"
                           id="cod-interno-servico"
                           name="COD_INTERNO_SERVICO_CLINICA"
                           class="form-control"
                           placeholder="Ex: 1234-5678"
                           pattern="[0-9\-]+"
                           title="Digite apenas números e traços" />
                </div>

                <div class="mb-3">
                    <label for="nome-servico" class="form-label text-muted" style="font-size: 14px;">
                        Nome Serviço
                    </label>
                    <input type="text"
                           id="nome-servico"
                           name="SERVICO_CLINICA_DESC"
                           class="form-control" />
                </div>

                <div class="text-end">
                    <button id="salvar" type="submit" class="btn btn-primary px-4">
                        Salvar
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.getElementById('cod-interno-servico').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9\-]/g, '');
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>
