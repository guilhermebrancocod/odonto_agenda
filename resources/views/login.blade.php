<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/favicon_faesa.png">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<style>
    :root {
        --brand-500: #1b6e91;
        /* hover */
        --brand-600: #2596be;
        /* principal */
        --brand-700: #0f5975;
        /* focos fortes */
        --bg-1: #eaf5ff;
        --bg-2: #d4eef7;
        --card-bg: rgba(255, 255, 255, .9);
        --border: rgba(37, 150, 190, .18);
        --error: #c62828;
        --ok: #0ea5a8;
    }

    * {
        box-sizing: border-box
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        background:
            radial-gradient(1200px 600px at 10% 10%, var(--bg-2), transparent 60%),
            radial-gradient(1000px 500px at 90% 20%, #f1fbff, transparent 55%),
            linear-gradient(180deg, var(--bg-1), #f8fdff);
        padding: 24px;
    }

    .container {
        width: min(420px, 92vw);
        background: var(--card-bg);
        backdrop-filter: blur(6px);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px 26px 26px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .08);
    }

    .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 18px;
    }

    .brand img {
        width: 150px;
        height: auto;
        display: block;
    }

    .clinic-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f0fbff;
        color: var(--brand-600);
        border: 1px solid var(--border);
        padding: 8px 12px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 14px;
        margin: 0 auto 8px;
    }

    .clinic-badge .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--ok);
        box-shadow: 0 0 0 4px rgba(14, 165, 168, .12);
    }

    h4 {
        text-align: center;
        margin: 6px 0 18px;
        font-size: 22px;
        color: #16475b;
        letter-spacing: .2px;
    }

    form {
        margin-top: 4px
    }

    .input-group {
        position: relative;
        margin: 18px 0;
    }

    .input-group .icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #6b8691;
        pointer-events: none;
    }

    .input-group input {
        width: 100%;
        height: 48px;
        padding: 14px 44px 0 38px;
        /* espaço p/ ícone + label flutuante */
        border-radius: 10px;
        border: 1px solid #d7e6ee;
        background: #fbfeff;
        font-size: 16px;
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
    }

    .input-group label {
        position: absolute;
        left: 38px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #7d97a3;
        background: transparent;
        pointer-events: none;
        transition: .2s;
    }

    .input-group input:focus {
        border-color: var(--brand-600);
        box-shadow: 0 0 0 4px rgba(37, 150, 190, .12);
        background: #fff;
    }

    .input-group input:focus+label,
    .input-group input:not(:placeholder-shown)+label {
        top: 8px;
        transform: none;
        font-size: 11px;
        color: var(--brand-600);
    }

    .toggle-password {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        color: #6b8691;
        user-select: none;
        padding: 6px;
        border-radius: 8px;
    }

    .toggle-password:hover {
        background: #eef7fb
    }

    .alert-danger,
    .error {
        margin-top: 6px;
        padding: 10px 12px;
        border-radius: 8px;
        background: #fdeaea;
        color: #7a0f0f;
        border: 1px solid #f5c2c7;
        font-size: 14px;
    }

    .actions {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .forgot-password-link a {
        color: var(--brand-600);
        text-decoration: none;
        font-size: 14px;
        transition: color .2s, transform .2s;
    }

    .forgot-password-link a:hover {
        color: var(--brand-500);
        transform: translateX(2px);
    }

    .btn-primary {
        width: 100%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 50px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        background: linear-gradient(180deg, var(--brand-600), var(--brand-500));
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: .2px;
        box-shadow: 0 6px 16px rgba(37, 150, 190, .25);
        transition: transform .12s ease, box-shadow .2s ease, filter .2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(37, 150, 190, .28);
        filter: brightness(1.02);
    }

    .btn-primary:active {
        transform: translateY(0)
    }

    /* Acessibilidade */
    .btn-primary:focus-visible,
    .forgot-password-link a:focus-visible,
    .toggle-password:focus-visible,
    .input-group input:focus-visible {
        outline: 3px solid rgba(37, 150, 190, .35);
        outline-offset: 2px;
        border-radius: 10px;
    }

    /* Rodapé sutil */
    .footer-note {
        margin-top: 14px;
        text-align: center;
        font-size: 12px;
        color: #7d97a3;
    }
</style>
</head>

<body>

    <div class="container">
        <div class="brand">
            <img src="{{ asset('faesa.png') }}" alt="FAESA">
        </div>

        <div class="clinic-badge" aria-hidden="true">
            <span class="dot"></span> Portal da Clínica
        </div>
        <h4>Odontologia</h4>

        <form action="{{ route('loginPOST') }}" method="POST" novalidate>
            @csrf

            <!-- Usuário -->
            <div class="input-group">
                <i class="bi bi-person icon" aria-hidden="true"></i>
                <input
                    type="text"
                    id="login"
                    name="usuario"
                    required
                    placeholder=" "
                    autocomplete="username"
                    aria-label="Usuário" />
                <label for="login">Usuário</label>
            </div>

            <!-- Senha -->
            <div class="input-group">
                <i class="bi bi-shield-lock icon" aria-hidden="true"></i>
                <input
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    placeholder=" "
                    autocomplete="current-password"
                    aria-label="Senha" />
                <label for="senha">Senha</label>
                <button class="toggle-password" type="button" aria-label="Mostrar/ocultar senha" onclick="togglePasswordVisibility(this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            @error('login')
            <div class="error">{{ $message }}</div>
            @enderror

            @error('senha')
            <div class="error">{{ $message }}</div>
            @enderror

            @if (session('error'))
            <div class="alert-danger">
                {{ session('error') }}
            </div>
            @endif

            <button type="submit" class="btn-primary">
                <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Entrar
            </button>

            <div class="actions">
                <div class="forgot-password-link">
                    <a href="https://acesso.faesa.br/#/auth-user/forgot-password">Esqueceu a senha?</a>
                </div>
            </div>

            <div class="footer-note">
                Acesso restrito a profissionais e alunos autorizados.
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