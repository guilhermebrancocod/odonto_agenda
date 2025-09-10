<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consulta de Paciente</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- BOOTSTRAP ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        /* ADICIONA SCROLL CASO EXCEDA O MÁXIMO DE ALTURA DEFINIDO */
        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }
        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex-direction: column;
            overflow-y: auto;
            border: 1.8px solid #dee2e6;
        }
        .table-responsive {
            overflow-x: auto;
            overflow-y: auto;
        }
        tr {
            max-height: 10px; /* ajuste conforme a altura das linhas da tabela original */
        }
        .modal-xxl {
          min-width: 95vw;
          max-width: 95vw;
          min-height: 95vh;
          max-height: 95vh;
        }
    </style>
</head>

<body>
    @include('components.navbar')

    <!-- CONTEUDO PRINCIPAL -->
    <div id="content-wrapper">

        <!-- INFORMA ERROS DE VALIDAÇÃO DO BACKEND EM CASOS DE SUBMISSÃO DE FORMULÁRIO -->
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- MOSTRA MENSAGEM DE SUCESSO AO USUARIO APÓS UMA AÇÃO BEM SUCEDIDA -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <main>

            <div class="bg-white p-4 rounded shadow-sm w-100">  

                <h2>Consultar aluno</h2>

                
                
            </div>
               
        </main>

    </div>
</body>
</html>
