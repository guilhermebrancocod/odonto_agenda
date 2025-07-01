<!DOCTYPE html>
<html lang="pt-br">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/loginScreen.css') }}" />
    <title>Login</title>
    <link rel="icon" type="image/png" href="faesa_favicon.png">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
    <style>

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 10px 40px 10px 5px;
            font-size: 16px;
            border: none;
            border-bottom: 1px solid #ccc;
            transition: all 0.3s ease;
            background: transparent;
        }

        .input-group label {
            position: absolute;
            top: 10px;
            left: 5px;
            font-size: 16px;
            color: gray;
            pointer-events: none;
            transition: 0.3s ease all;
        }

        .input-group input:focus ~ label,
        .input-group input:not(:placeholder-shown) ~ label {
            top: -10px;
            font-size: 12px;
            color: #2596be;
        }

        .input-group input:focus {
            border-bottom: 2px solid #2596be;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #999;
            user-select: none;
        }



        /* ESQUECEU A SENHA? */
        .forgot-password-link a {
            display: inline-block;
            color: #2596be;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .forgot-password-link a:hover {
            color:rgb(6, 91, 128);
            transform: translateX(5px);
        }



        /* BOÃO SUBMETER FORMULÁRIO */
        input[type="submit"] {
            background-color: #2596be;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        input[type="submit"]:hover {
            background-color: #1b6e91;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        input[type="submit"]:active {
            transform: translateY(1px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }



    </style>



<body>
    <div class="container">

        
        <!-- LOGO FAESA -->
        <img src="faesa.png" alt="Logo">
        
        
        <form action="" method="POST">
            @csrf

            <!-- USUARIO -->
            <div class="input-group">
                <input type="text" id="login" name="login" required placeholder=" ">
                <label for="login">Usuário</label>
            </div>

            
            @error('login')
            <div class="error">{{ $message }}</div>
            @enderror
            


            <div class="input-group">
                <input type="password" id="senha" name="senha" required placeholder=" ">
                <label for="senha">Senha</label>
                <span class="toggle-password" onclick="togglePasswordVisibility(this)">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
            
            
            @error('senha')
            <div class="error">{{ $message }}</div>
            @enderror

            @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif

            
            <input type="submit" value="Entrar">

            
            <!-- ESQUECEU A SENHA? -->
            <div class="forgot-password-link">
                <a href="https://acesso.faesa.br/#/auth-user/forgot-password">Esqueceu a senha?</a>
            </div>



        </form>
    </div>

</body>

    <script>
        function togglePasswordVisibility(element) {
            const input = document.getElementById("senha");
            const icon = element.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    </script>



</html>