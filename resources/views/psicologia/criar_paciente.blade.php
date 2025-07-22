<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Paciente</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        #content-container {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            width: 100%;
        }
        main {
            max-width: 1600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <!-- Navbar fixa -->
    @include('components.navbar')

    <!-- Conteúdo rolável -->
    <div id="content-container" class="bg-light">
        <main class="bg-white p-4 rounded shadow-sm w-100" style="">
            <div class="text-center mb-4">
                <h2 class="fs-4 mb-0" style="color: #333;">Cadastro de Paciente</h2>
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

            <form action="{{ route('criarPaciente-Psicologia') }}" method="POST" class="needs-validation">
                @csrf

                <!-- Dados Pessoais -->
                <h5>Dados Pessoais</h5>
                <hr />
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" id="nome" name="NOME_COMPL_PACIENTE" class="form-control" required />
                    </div>
                    <div class="col-md-2">
                        <label for="dt_nasc" class="form-label">Dt Nascimento</label>
                        <input type="date" id="dt_nasc" name="DT_NASC_PACIENTE" class="form-control" required />
                    </div>
                    <div class="col-md-2">
                        <label for="cpf_paciente" class="form-label">CPF</label>
                        <input type="text" id="cpf_paciente" name="CPF_PACIENTE" class="form-control" required />
                    </div>
                    <div class="col-md-2">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select id="sexo" name="SEXO_PACIENTE" class="form-select" required>
                            <option value="" selected>Selecione</option>
                            <option value="M">Masculino</option>
                            <option value="F">Feminino</option>
                            <option value="O">Outro</option>
                        </select>
                    </div>
                </div>

                <!-- Endereço -->
                <h5>Endereço</h5>
                <hr />
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" id="cep" name="CEP" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label for="rua" class="form-label">Rua</label>
                        <input type="text" id="rua" name="ENDERECO" class="form-control" />
                    </div>
                    <div class="col-md-3">
                        <label for="numero" class="form-label">Número</label>
                        <input type="text" id="numero" name="END_NUM" class="form-control" />
                    </div>
                </div>
                <div class="mb-4">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" id="bairro" name="BAIRRO" class="form-control" />
                </div>
                <div class="mb-4">
                    <label for="municipio" class="form-label">Municipio</label>
                    <input type="text" id="municipio" name="municipio" class="form-control" />
                </div>
                <div class="mb-4">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" id="estado" name="UF" class="form-control" />
                </div>

                <!-- Contato -->
                <h5>Contato</h5>
                <hr />
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="E_MAIL_PACIENTE" class="form-control" required/>
                </div>
                <div class="mb-3">
                    <label for="celular" class="form-label">Celular</label>
                    <input type="text" id="celular" name="CELULAR_PACIENTE" class="form-control" required/>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="FONE_PACIENTE" class="form-control" />
                </div>

                <!-- Campos Observação e Status corrigidos -->
                <div class="mb-3">
                    <label for="observacao" class="form-label">Observações</label>
                    <textarea id="observacao" name="OBSERVACAO" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="STATUS" class="form-select" required>
                        <option value="" selected>Selecione</option>
                        <option value="Agendado">Agendado</option>
                        <option value="Em tratamento">Em tratamento</option>
                        <option value="Tratamento finalizado">Tratamento finalizado</option>
                    </select>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
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
