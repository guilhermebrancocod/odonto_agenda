<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Paciente</title>
    
    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />

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
        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex-direction: row;
            overflow-y: auto;
            border: 1.8px solid #dee2e6;
        }

    /* CSS DA MENSAGEM DE SUCESSO */
    @keyframes slideDownFadeOut {
        0% {
            opacity: 0;
            transform: translateY(-100%);
        }
        10% {
            opacity: 1;
            transform: translateY(0);
        }
        90% {
            opacity: 1;
            transform: translateY(0);
        }
        100% {
            opacity: 0;
            transform: translateY(-100%);
        }
    }

    .animate-slide-down {
        animation: slideDownFadeOut 5s ease forwards;
        z-index: 9999;
    }
    </style>
</head>

<body>
    <!-- COMPONENT NAVBAR -->
    @include('components.navbar')

    <div id="content-wrapper">
        <main>
            <div class="text-center mb-4">
                <h2 class="fs-4 mb-0" style="color: #333;">Cadastro de Paciente</h2>
            </div>

            <!-- INFORMA ERROS DE VALIDAÇÃO DO BACKEND EM CASOS DE SUBMISSÃO DE FORMULÁRIO -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- MOSTRA MENSAGEM DE SUCESSO AO USUARIO APÓS UMA AÇÃO BEM SUCEDIDA -->
            @if(session('success'))
                <div id="success-alert" class="alert alert-success fixed-top text-center mx-auto w-50 shadow animate-slide-down">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger fixed-top text-center mx-auto w-50 shadow animate-slide-down">
                    {{ session('error') }}
                </div>
                
            @endif

            <form action="{{ route('criarPaciente-Psicologia') }}" method="POST" class="needs-validation" id="pacienteForm">
                @csrf

                <!-- DADOS PESSOAIS -->
                <h5>Dados Pessoais</h5>
                <hr />
                <div class="row g-3 mb-4">

                    <!-- NOME COMPLETO -->
                    <div class="col-md-6">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" id="nome" name="NOME_COMPL_PACIENTE" class="form-control" value="{{ old('NOME_COMPL_PACIENTE') }}"/>
                    </div>
                    
                    <!-- DATA NASCIMENTO -->
                    <div class="col-md-2">
                        <label for="dt_nasc" class="form-label">Dt Nascimento</label>
                        <input type="text" id="dt_nasc" name="DT_NASC_PACIENTE" class="form-control" value="{{ old('DT_NASC_PACIENTE') }}"/>
                    </div>
                    
                    <!-- CPF -->
                    <div class="col-md-2">
                        <label for="cpf_paciente" class="form-label">CPF</label>
                        <input type="text" id="cpf_paciente" name="CPF_PACIENTE" class="form-control" value="{{ old('CPF_PACIENTE') }}"/>
                    </div>
                    
                    <!-- SEXO -->
                    <div class="col-md-2">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select id="sexo" name="SEXO_PACIENTE" class="form-select">
                            <option value="" {{ old('SEXO_PACIENTE') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="M" {{ old('SEXO_PACIENTE') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('SEXO_PACIENTE') == 'F' ? 'selected' : '' }}>Feminino</option>
                            <option value="O" {{ old('SEXO_PACIENTE') == 'O' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>

                    {{-- CÓDIGO SUS --}}
                    <div class='col-md-2' id='div-check-sus'>
                        <input type="checkbox" name="cod_sus_check" id="cod_sus_check">
                        <label for="cod_sus_check">Possui cartão do SUS</label>
                    </div>

                    {{-- CAMPO DE PREENCHIMENTO --}}
                    <div class="col-md-4" id='cod-sus-div'></div>

                </div>

                <!-- DADOS DO RESPONSAVEL -->
                <h5>Dados do Responsável</h5>
                <hr />
                <div class="row g-3 mb-4">

                    <!-- NOME COMPLETO -->
                    <div class="col-md-6">
                        <label for="nome_responsavel" class="form-label">Nome Completo</label>
                        <input type="text" id="nome_responsavel" name="NOME_RESPONSAVEL" class="form-control" value="{{ old('NOME_RESPONSAVEL') }}"/>
                    </div>
                    
                    <!-- CPF -->
                    <div class="col-md-2">
                        <label for="cpf_responsavel" class="form-label">CPF</label>
                        <input type="text" id="cpf_responsavel" name="CPF_RESPONSAVEL" class="form-control" value="{{ old('CPF_RESPONSAVEL') }}"/>
                    </div>

                <!-- ENDEREÇO -->
                <h5>Endereço</h5>

                <hr />

                <div class="row g-3 mb-4">

                    <!-- CEP -->
                    <div class="col-md-3">
                        <label for="cep" class="form-label">CEP</label>
                        <input type="text" id="cep" name="CEP" class="form-control" value="{{ old('CEP') }}"/>
                    </div>

                    <!-- RUA - LOGRADOURO -->
                    <div class="col-md-6">
                        <label for="rua" class="form-label">Rua</label>
                        <input type="text" id="rua" name="ENDERECO" class="form-control" value="{{ old('ENDERECO') }}"/>
                    </div>
                    
                    <!-- NÚMERO -->
                    <div class="col-md-3">
                        <label for="numero" class="form-label">Número</label>
                        <input type="text" id="numero" name="END_NUM" class="form-control" value="{{ old('END_NUM') }}"/>
                    </div>

                    <!-- COMPLEMENTO -->
                    <div class="col-md-6">
                        <label for="complemento" class="form-label">Complemento</label>
                        <input type="text" id="complemento" name="COMPLEMENTO" class="form-control" value="{{ old('COMPLEMENTO') }}">
                    </div>
                    
                </div>

                <!-- BAIRRO -->
                <div class="mb-4">
                    <label for="bairro" class="form-label">Bairro</label>
                    <input type="text" id="bairro" name="BAIRRO" class="form-control" value="{{ old('BAIRRO') }}"/>
                </div>
                
                <!-- MUNICIPIO -->
                <div class="mb-4">
                    <label for="municipio" class="form-label">Municipio</label>
                    <input type="text" id="municipio" name="municipio" class="form-control" value="{{ old('municipio') }}"/>
                </div>

                <!-- ESTADO -->
                <div class="mb-4">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="UF" class="form-select">
                        <option value="" selected disabled>Selecione o estado</option>
                        <option value="AC" {{ old('UF') == 'AC' ? 'selected' : '' }}>Acre</option>
                        <option value="AL" {{ old('UF') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                        <option value="AP" {{ old('UF') == 'AP' ? 'selected' : '' }}>Amapá</option>
                        <option value="AM" {{ old('UF') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                        <option value="BA" {{ old('UF') == 'BA' ? 'selected' : '' }}>Bahia</option>
                        <option value="CE" {{ old('UF') == 'CE' ? 'selected' : '' }}>Ceará</option>
                        <option value="DF" {{ old('UF') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                        <option value="ES" {{ old('UF') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                        <option value="GO" {{ old('UF') == 'GO' ? 'selected' : '' }}>Goiás</option>
                        <option value="MA" {{ old('UF') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                        <option value="MT" {{ old('UF') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                        <option value="MS" {{ old('UF') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                        <option value="MG" {{ old('UF') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                        <option value="PA" {{ old('UF') == 'PA' ? 'selected' : '' }}>Pará</option>
                        <option value="PB" {{ old('UF') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                        <option value="PR" {{ old('UF') == 'PR' ? 'selected' : '' }}>Paraná</option>
                        <option value="PE" {{ old('UF') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                        <option value="PI" {{ old('UF') == 'PI' ? 'selected' : '' }}>Piauí</option>
                        <option value="RJ" {{ old('UF') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                        <option value="RN" {{ old('UF') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                        <option value="RS" {{ old('UF') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                        <option value="RO" {{ old('UF') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                        <option value="RR" {{ old('UF') == 'RR' ? 'selected' : '' }}>Roraima</option>
                        <option value="SC" {{ old('UF') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                        <option value="SP" {{ old('UF') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                        <option value="SE" {{ old('UF') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                        <option value="TO" {{ old('UF') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                    </select>
                </div>

                <!-- CONTATO -->
                <h5>Contato</h5>
                <hr />
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="E_MAIL_PACIENTE" class="form-control" value="{{ old('E_MAIL_PACIENTE') }}"/>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="FONE_PACIENTE" class="form-control" value="{{ old('FONE_PACIENTE') }}"/>
                </div>

                <!-- CAMPO DE OBSERVAÇÃO -->
                <div class="mb-3">
                    <label for="observacao" class="form-label">Observações</label>
                    <textarea id="observacao" name="OBSERVACAO" class="form-control" rows="4">{{ old('OBSERVACAO') }}</textarea>
                </div>

                <!-- STATUS PACIENTE -->
                <input type="hidden" name="STATUS" value="Em espera">

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        Salvar
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

    <!-- FLATPICKR -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

    <script>
        const inputCheckSus = document.getElementById('cod_sus_check');
        const codSusDiv = document.getElementById('cod-sus-div');

        inputCheckSus.addEventListener('input',  function () {
            if (inputCheckSus.checked) {
                codSusDiv.innerHTML += `
                    <div class="input-group mb-3">
                        <span class="input-group-text">Cod. SUS</span>
                        <input type="text" class="form-control" name="COD_SUS">
                    </div>
                    `
            } else {
                codSusDiv.innerHTML = ''
            }
        })
    </script>

    <!-- API DOS CORREIOS PARA PREENCHER AUTOMATICAMENTE DADOS DE ENDEREÇO AO INFORMAR O CEP -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cepInput = document.getElementById('cep');
            const ruaInput = document.getElementById('rua');
            const bairroInput = document.getElementById('bairro');
            const municipioInput = document.getElementById('municipio');
            const estadoSelect = document.getElementById('estado');

            cepInput.addEventListener('blur', function() {
                let cep = cepInput.value.replace(/\D/g, '');

                // precisa colocar traço
                if (cep.length !== 8) {
                    alert('CEP inválido. Digite 8 números.');
                    return;
                }

                // LIMPA OS CAMPOS ENQUANTO BUSCA
                ruaInput.value = 'Carregando...';
                bairroInput.value = 'Carregando...';
                municipioInput.value = 'Carregando...';
                estadoSelect.value = '';

                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if ('erro' in data) {
                            alert('CEP não encontrado.');
                            ruaInput.value = '';
                            bairroInput.value = '';
                            municipioInput.value = '';
                            estadoSelect.value = '';
                            return;
                        }

                        ruaInput.value = data.logradouro || '';
                        bairroInput.value = data.bairro || '';
                        municipioInput.value = data.localidade || '';
                        estadoSelect.value = data.uf || '';
                    })
                    .catch(() => {
                        alert('Erro ao consultar o CEP.');
                        ruaInput.value = '';
                        bairroInput.value = '';
                        municipioInput.value = '';
                        estadoSelect.value = '';
                    });
            });
        });
    </script>

    <!-- SCRIPT DO FLATPICKR -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#dt_nasc", {
            dateFormat: "d-m-Y",
            maxDate: "today",
            locale: "pt",
            allowInput: true,
            defaultDate: "{{ old('DT_NASC_PACIENTE') ?? '' }}"
            });
        });
    </script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const alert = document.getElementById("success-alert");
            if (alert) {
                setTimeout(() => {
                    alert.remove();
                }, 5000); // remove após a animação de 5 segundos
            }
        });
    </script>

    <!-- NÃO PERMITE ENVIO DE FORMULÁRIO DE PACIENTE AO CLICAR EM ENTER -->
    <script>
        const form = document.getElementById('pacienteForm');

        form.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && event.target.tagName.toLowerCase() !== 'textarea') {
                event.preventDefault();
            }
        });
    </script>

    <!-- FORMATADOR DO CAMPO DE CPF -->
    <script>
        document.getElementById('cpf_paciente').addEventListener('input', function (e) {
            let value = e.target.value;

            // Remove tudo que não é número
            value = value.replace(/\D/g, '');

            // Limita a 11 dígitos
            value = value.slice(0, 11);

            // Aplica a máscara de CPF
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }

            e.target.value = value;
        });
    </script>
    
    <!-- FORMATADOR DO CAMPO DE CELULAR -->
    <script>
    document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value;

        // Remove tudo que não for número
        value = value.replace(/\D/g, '');

        // Limita a 11 dígitos (DDD + número)
        value = value.slice(0, 11);

        // Aplica a máscara (99) 99999-9999
        if(value.length > 0){
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');  // (99) ...
            if(value.length <= 10){
                value = value.replace(/(\d{4})(\d)/, '$1-$2');    // até 9999-9999
            } else {
                value = value.replace(/(\d{5})(\d)/, '$1-$2');    // 99999-9999
            }
        }

        e.target.value = value;
    });
    </script>
</body>
</html>
