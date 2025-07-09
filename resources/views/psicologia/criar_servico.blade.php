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
            font-family: "Montserrat", sans-serif;
            background-color: #f8f9fa;
        }

        #content-wrapper {
            height: calc(100vh - 56px); /* altura da navbar */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 16px;
            overflow-y: auto;
        }

        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 16px;
        }

        h5 {
            margin-bottom: 12px;
            font-weight: 600;
        }

        #salvar {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        #salvar:hover {
            background-color: #0056b3;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <div id="content-wrapper">
        <main>
            <div class="text-center">
                <h2>Cadastro de Serviço</h2>
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

            <form class="needs-validation" action="{{ route('criarServico-Psicologia') }}" method="POST" novalidate>
                @csrf

                <input type="hidden" name="ID_CLINICA" value="1">

                <h5>Dados do Serviço</h5>
                <hr>

                <div class="mb-3">
                    <label for="nome-servico" class="form-label text-muted" style="font-size: 14px;">
                        Nome do Serviço
                    </label>
                    <input type="text"
                        id="nome-servico"
                        name="NOME-SERVICO"
                        class="form-control"
                        required>
                </div>

                <div class="text-end">
                    <button id="salvar" type="submit">
                        Salvar
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>
