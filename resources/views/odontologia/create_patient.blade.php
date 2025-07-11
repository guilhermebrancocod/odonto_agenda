<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>
    <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="margin: 0; font-size: 24px; color: #333;">Cadastro Paciente</h3>
        </div>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($paciente) ? route('updatePatient', $paciente->ID_PACIENTE) : route('createPatient') }}"
            method="POST">
            @csrf
            @if(isset($paciente))
            @method('PUT')
            @endif
            @csrf
            <div class="linha-com-titulo">
                <h5>Dados Pessoais</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row g-3" style="margin: 20px 0;">
                <div style="flex: 0.2;">
                    <label for="cpf" style="font-size: 14px; color: #666;">CPF</label>
                    <input type="text" id="cpf" name="cpf" class="form-control"
                        value="{{ old('cpf', $paciente->CPF_PACIENTE ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="10" minlength="11">
                </div>
                <div style="flex: 1;">
                    <label for="nome" style="font-size: 14px; color: #666;">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                        value="{{ old('nome', $paciente->NOME_COMPL_PACIENTE ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="255">
                </div>
                <div style="flex: 0.2;">
                    <label for="dt_nasc" style="font-size: 14px; color: #666;">Dt Nascimento</label>
                    <input type="text" id="dt_nasc" name="dt_nasc" class="form-control datepicker"
                        value="{{ old('dt_nasc', isset($paciente->DT_NASC_PACIENTE) ? \Carbon\Carbon::parse($paciente->DT_NASC_PACIENTE)->format('d/m/Y') : '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="flex: 0.2;">
                    <label for="sexo" style="font-size: 14px; color: #666;">Sexo</label>
                    <select type="text" id="sexo" name="sexo" class="selectpicker"
                        value="{{ old('sexo', $paciente->SEXO_PACIENTE ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                </div>
            </div>

            <div class="linha-com-titulo">
                <h5>Endereço</h5>
                <div class="linha-flex"></div>
            </div>
            <tr scope="row">
                <div class="row g-3" style="margin: 20px 0;">
                    <div style="flex: 0.3;">
                        <label for="cep" style="font-size: 14px; color: #666;">CEP</label>
                        <input type="text" id="cep" name="cep" class="form-control"
                            value="{{ old('cep', $paciente->CEP ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="10">
                    </div>
                    <div style="flex: 1.7;">
                        <label for="rua" style="font-size: 14px; color: #666;">Rua</label>
                        <input type="text" id="rua" name="rua" class="form-control"
                            value="{{ old('rua', $paciente->ENDERECO ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="255">
                    </div>
                    <div style="flex: 0.5;">
                        <label for="numero" style="font-size: 14px; color: #666;">Número</label>
                        <input type="text" id="numero" name="numero" class="form-control"
                            value="{{ old('numero', $paciente->END_NUM ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="10">
                    </div>
                </div>

                <div style="flex:1.7">
                    <label for="bairro" style="font-size: 14px; color: #666;">Bairro</label>
                    <input type="text" id="bairro" name="bairro" class="form-control"
                        value="{{ old('bairro', $paciente->BAIRRO ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="100">
                </div>

                <div style="flex:0.5">
                    <label for="complemento" style="font-size: 14px; color: #666;">Completmento</label>
                    <input type="text" id="complemento" name="complemento" class="form-control"
                        value="{{ old('complemento', $paciente->COMPLEMENTO ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="100">
                </div>

                <div style="flex:0.5">
                    <label for="cidade" style="font-size: 14px; color: #666;">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-control"
                        value="{{ old('cidade', $paciente->MUNICIPIO ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="100">
                </div>

                <div style="flex:0.2">
                    <label for="estado" style="font-size: 14px; color: #666;">Estado</label>
                    <input type="text" id="estado" name="estado" class="form-control"
                        value="{{ old('estado', $paciente->UF ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="2">
                </div>

                <div class="linha-com-titulo">
                    <h5>Contato</h5>
                    <div class="linha-flex"></div>
                </div>

                <div style="flex: 1">
                    <label for="email" style="font-size: 14px; color: #666;">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="{{ old('email', $paciente->E_MAIL_PACIENTE ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="100">
                </div>
                <div style="flex: 1;">
                    <label for="celular" style="font-size: 14px; color: #666;">Celular</label>
                    <input type="text" id="celular" name="celular" class="form-control"
                        value="{{ old('celular', $paciente->FONE_PACIENTE ?? '') }}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="20">
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 10px;">

                    <div>
                        <button id="voltar" name="voltar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                            Voltar
                        </button>
                    </div>
                    <div>
                        <button id="salvar" name="salvar" type="submit" onclick="saveGroupData()" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                            Salvar
                        </button>
                    </div>
                </div>
        </form>
    </div>
    @if (session('success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: "{{ session('success') }}",
        });
    </script>
    @endif
    @if (session('alert'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Atenção!',
            text: "{{ session('alert') }}",
        });
    </script>
    @endif
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_patient.js"></script>
</body>

</html>