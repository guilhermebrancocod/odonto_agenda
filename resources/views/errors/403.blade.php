<!-- PÁGINA DE ERRO CASO NÃO ACHE -->


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    
    <title>Erro</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            text-align: center;
        }

        
        #faesa-logo-navbar {
            width: 130px;
            height: 47px;
            margin-right: 40px;
        }
            </style>
    <script>
        let count = 5;
        function updateCountdown() {
            document.getElementById("countdown").innerText = count;
            if (count > 1) {
                count--;
                setTimeout(updateCountdown, 1000);
            } else {
                // VOLTA PARA A PÁGINA NA QUAL O USUÁRIO ESTAVA ANTES
                window.history.back();
            }
        }
        window.onload = updateCountdown;
    </script>
</head>
<body style="background-color: #ecf5f9">
    <div class="container d-flex justify-content-center align-items-center flex-column">

        <img class="m-0 p-0 m-4" src="{{ asset('faesa.png') }}" alt="FAESA LOGO" id="faesa-logo-navbar">
        
        <h3 class=" p-3 bg-info bg-opacity-10 border border-info rounded-3">
            Ops!
        </h3>

        <span>Você não tem permissão para acessar essa página</span>
        <span>Você será redirecionado de volta em <span id="countdown">5</span> segundos...</span>

    </div>
</body>
</html>