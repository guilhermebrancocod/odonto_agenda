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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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

                <h2>Criar aluno</h2>

                <form action="{{ route('psicologia.Criar_alunoPOST') }}" method="POST" class="needs-validation" id="pacienteForm">
                @csrf

                <input type="hidden" name="matricula_id" id="matricula_id" value="{{ old('matricula_id') }}">

                <!-- DADOS PESSOAIS -->
                <hr />
                <div class="row g-3 mb-4">

                    <!-- MATRÍCULA -->
                    <div class="col-md-2">
                        <label for="matricula" class="form-label">Matrícula</label>
                        <input type="text" id="matricula" name="MATRICULA" class="form-control" value="{{ old('MATRICULA') }}"/>

                        <div id="matricula-list" class="list-group position-absolute w-100" style="z-index: 1000; max-width: 20vw; overflow-y: auto; max-height: 20vh;"></div>
                    </div>
                    
                    <!-- NOME COMPLETO-->
                    <div class="col-md-4">
                        <label for="nome_compl" class="form-label">Nome Completo</label>
                        <input type="text" id="nome_compl" name="NOME_COMPL", class="form-control" value="{{ old('NOME_COMPL') }}" disabled />
                    </div>
                    
                    <!-- DATA NASCIMENTO -->
                    <div class="col-md-2">
                        <label for="dt_nasc" class="form-label">Dt Nascimento</label>
                        <input type="text" id="dt_nasc" name="DT_NASC_aluno" class="form-control" value="{{ old('DT_NASC_PACIENTE') }}" disabled/>
                    </div>
                    
                    <!-- CPF -->
                    <div class="col-md-2">
                        <label for="cpf_paciente" class="form-label">CPF</label>
                        <input type="text" id="cpf_paciente" name="CPF_aluno" class="form-control" value="{{ old('CPF_PACIENTE') }}" disabled/>
                    </div>
                    
                    <!-- SEXO -->
                    <div class="col-md-2">
                        <label for="sexo" class="form-label">Sexo</label>
                        <select id="sexo" name="SEXO_aluno" class="form-select" disabled>
                            <option value="" {{ old('SEXO_PACIENTE') == '' ? 'selected' : '' }}>Selecione</option>
                            <option value="M" {{ old('SEXO_PACIENTE') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('SEXO_PACIENTE') == 'F' ? 'selected' : '' }}>Feminino</option>
                            <option value="O" {{ old('SEXO_PACIENTE') == 'O' ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>
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
                    <input type="email" id="email" name="E_MAIL_aluno" class="form-control" value="{{ old('E_MAIL_PACIENTE') }}"/>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="FONE_aluno" class="form-control" value="{{ old('FONE_PACIENTE') }}"/>
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
            </div>
        </main>
    </div>

    {{-- BUSCA DE ALUNO - aluno --}}
    <script>
        const matriculaInput = document.getElementById('matricula');
        const matriculaList = document.getElementById('matricula-list');

        // CAMPOS DO FORMULÁRIO
        const nome_compl = document.getElementById('nome_compl');
        const dt_nasc = document.getElementById('dt_nasc');
        const cpf_paciente = document.getElementById('cpf_paciente');
        const sexo = document.getElementById('sexo');

        let timeout = null;
        
        matriculaInput.addEventListener('input', function() {
            const $query = matriculaInput.value.trim();

            if(!$query) return;

            clearTimeout(timeout);

            timeout = setTimeout(() => {

                fetch(`/psicologia/buscar-aluno/${encodeURIComponent($query)}`)
                    .then(response => response.json())
                    .then(matriculas => {
                        matriculaList.innerHTML = '';

                        if(matriculas.length === 0) {
                            matriculaList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhuma matrícula encontrada</button>`;
                            document.getElementById('id_aluno').value = '';
                            return;
                        } else {
                            matriculas.forEach(matricula => {
                                console.log(matricula);
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.classList.add('list-group-item', 'list-group-item-action');
                                item.textContent = matricula.ALUNO;

                                item.addEventListener('click', () => {
                                document.getElementById('matricula').value = matricula.ALUNO;
                                document.getElementById('matricula_id').value = matricula.ALUNO;
                                let matriculaList = document.getElementById('matricula-list');
                                matriculaList.innerHTML = '';

                                // PREENCHE VALORES DO FORMULÁRIO AUTOMATICAMENTE E BLOQUEIA ALTERAÇÕES
                                nome_compl.value = matricula.NOME_COMPL;
                                nome_compl.disabled = true;
                                dt_nasc.value = new Date(matricula.DT_NASC).toLocaleDateString('pt-BR');
                                dt_nasc.disabled = true;
                                cpf_paciente.value = matricula.CPF;
                                cpf_paciente.disabled = true;
                                sexo.value = matricula.SEXO;
                                sexo.disabled = true;

                                // PREENCHE CAMPOS DE ENDEREÇO


                                });
                                matriculaList.appendChild(item);
                            });
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        matriculaList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger" style="max-width: 15vw;">Erro ao buscar matrículas</button>`;
                    });
            }, 300);
        })

        matriculaInput.addEventListener('blur', function() {
            setTimeout(() => {
                matriculaList.innerHTML = '';
            }, 200);
        })
    </script>

</body>
</html>
