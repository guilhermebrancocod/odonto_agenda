<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    @include('components.sidebar')
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Cadastro de usuario</legend>
        </fieldset>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($user) ? route('updateUser', $user->ID) : route('createUsuario') }}"
            method="POST">
            @csrf
            @if(isset($user))
            @method('PUT')
            @endif
            @csrf
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="input-group" style="flex: 1; flex-direction: column;">
                <div class="form-outline">
                    <select id="selectUserLyceum"style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option></option>
                    </select>
                </div>
            </div>
            <div class="row g-3 align-items-center" style="margin: 20px 0; display: flex;">
                <div style="flex: 1;">
                    <label for="nome" style="font-size: 14px; color: #666;">Nome</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                        value="{{ old('nome', $user->NOME ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
                <div style="flex: 0.5;">
                    <label for="winusuario" style="font-size: 14px; color: #666;">Usuario</label>
                    <input type="text" id="winusuario" name="winusuario" class="form-control"
                        value="{{ old('winusuario', $user->USUARIO ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
                <div style="flex: 0.5;">
                    <label for="pessoa" style="font-size: 14px; color: #666;">Cod Pessoa</label>
                    <input type="text" id="pessoa" name="pessoa" class="form-control"
                        value="{{ old('pessoa', $user->PESSOA ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
            </div>
            <div class="row g-3 align-items-center" style="margin: 20px 0; display: flex;">
                <div style="flex: 0.5;">
                    <label for="status" style="font-size: 14px; color: #666;">Status</label>
                    <select id="status" name="status" class="form-select"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="Ativo" {{ old('status', $user->ATIVO ?? '') == 'S' ? 'selected' : '' }}>Ativo</option>
                        <option value="Inativo" {{ old('status', $user->ATIVO ?? '') == 'N' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                <div style="flex: 0.5;">
                    <label for="tipo" style="font-size: 14px; color: #666;">Tipo</label>
                    <select id="tipo" name="tipo" class="form-select"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="Admin" {{ old('TIPO', $user->ATIVO ?? '') == 'Admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="Usuario" {{ old('TIPO', $user->ATIVO ?? '') == 'Usuario' ? 'selected' : '' }}>Usuario</option>
                        <option value="Coordenador" {{ old('TIPO', $user->ATIVO ?? '') == 'Coordenador' ? 'selected' : '' }}>Coordenador</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button id="voltar" name="voltar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Voltar
                </button>
                <button type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Salvar
                </button>
            </div>
        </form>
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
        }).then(() => {
            window.location.href = "{{ url('odontologia/consultarusuario') }}";
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
    
    <!-- Select2 principal -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Idioma português (DEPOIS do Select2 principal) -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/pt-BR.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_user.js"></script>
</body>

</html>