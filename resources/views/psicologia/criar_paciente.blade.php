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
            <h2 style="margin: 0; font-size: 24px; color: #333;">Cadastro de Paciente</h2>
        </div>

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

        <form class="row g-3 needs-validation" action="{{ route('criarPaciente-Psicologia') }}" method="POST">
            @csrf

            <div class="linha-com-titulo">
                <h5>Dados Pessoais</h5>
                <div class="linha-flex"></div>
            </div>

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label for="nome" style="font-size: 14px; color: #666;">Nome Completo</label>
                    <input type="text" id="nome" name="NOME_COMPL_PACIENTE" class="form-control">
                </div>
                <div style="flex: 0.2;">
                    <label for="dt_nasc" style="font-size: 14px; color: #666;">Dt Nascimento</label>
                    <input type="date" id="dt_nasc" name="DT_NASC_PACIENTE" class="form-control">
                </div>
                <div style="flex: 0.2;">
                    <label for="cpf_paciente" style="font-size: 14px; color: #666;">CPF</label>
                    <input type="text" id="cpf_paciente" name="CPF_PACIENTE" class="form-control">
                </div>
                <div style="flex: 0.2;">
                    <label for="sexo" style="font-size: 14px; color: #666;">Sexo</label>
                        <select id="sexo" name="SEXO_PACIENTE" class="form-control">
                            <option value="">Selecione</option>
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                            <option value="O">Outro</option>
                        </select>
                    </div>
                </div>
            <div class="linha-com-titulo">
                <h5>Endereço</h5>
                <div class="linha-flex"></div>
            </div>

            <div style="display: flex; gap: 20px;">
                <div style="flex: 0.3;">
                    <label for="cep" style="font-size: 14px; color: #666;">CEP</label>
                    <input type="text" id="cep" name="CEP" class="form-control">
                </div>
                <div style="flex: 1.7;">
                    <label for="rua" style="font-size: 14px; color: #666;">Rua</label>
                    <input type="text" id="rua" name="ENDERECO" class="form-control">
                </div>
                <div style="flex: 0.5;">
                    <label for="numero" style="font-size: 14px; color: #666;">Número</label>
                    <input type="text" id="numero" name="END_NUM" class="form-control">
                </div>
            </div>

            <div>
                <label for="bairro" style="font-size: 14px; color: #666;">Bairro</label>
                <input type="text" id="bairro" name="BAIRRO" class="form-control">
            </div>

            <div>
                <label for="cidade" style="font-size: 14px; color: #666;">Cidade</label>
                <input type="text" id="cidade" name="END_COMPL" class="form-control">
            </div>

            <div>
                <label for="estado" style="font-size: 14px; color: #666;">Estado</label>
                <input type="text" id="estado" name="UF" class="form-control">
            </div>

            <div class="linha-com-titulo">
                <h5>Contato</h5>
                <div class="linha-flex"></div>
            </div>

            <div>
                <label for="email" style="font-size: 14px; color: #666;">Email</label>
                <input type="email" id="email" name="E_MAIL_PACIENTE" class="form-control">
            </div>

            <div>
                <label for="celular" style="font-size: 14px; color: #666;">Celular</label>
                <input type="text" id="celular" name="CELULAR_PACIENTE" class="form-control">
            </div>

            <div>
                <label for="telefone" style="font-size: 14px; color: #666;">Telefone</label>
                <input type="text" id="telefone" name="FONE_PACIENTE" class="form-control">
            </div>

            <div style="text-align: right; margin-top: 20px;">
                <button id="salvar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Salvar
                </button>
            </div>
        </form>
    </div>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/create.js"></script>
</body>

</html>
