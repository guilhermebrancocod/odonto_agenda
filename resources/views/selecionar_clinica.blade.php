<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- ESTILIZAÇÃO DA PÁGINA -->
    <style>
        body {
            background-color: #5CA4EA;
        }

        img {
            margin: 15px auto;
            display: block;
            width: 150px;
        }
    </style>

</head>

<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="text-center">
            <!-- LOGO FAESA -->
            <img src="{{ asset('faesa_branco.png') }}" alt="Logo" class="mb-4">

            <h3 style="color: white;">Selecione qual clínica desejar acessar</h3>

            <form action="{{ route('selecionar-clinica-post') }}" method="POST">
                @csrf

                <button type="submit" name="clinica" value="1" class="btn btn-light m-2">Psicologia</button>
                <button type="submit" name="clinica" value="2" class="btn btn-light m-2">Odontologia</button>

            </form>
        </div>
    </div>
</body>

</html>
