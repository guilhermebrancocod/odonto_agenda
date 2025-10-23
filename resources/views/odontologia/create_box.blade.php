<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Box | FAESA</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    @include('partials.sidebar')
    <div style="margin-left:225px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Cadastro de box de atendimento</legend>
        </fieldset>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($box) ? route('updateBox', $box->ID_BOX_CLINICA) : route('createBox') }}"
            method="POST">
            @csrf
            @if(isset($box))
            @method('PUT')
            @endif
            @csrf
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row g-3 align-items-center" style="margin: 20px 0; display: flex;">
                <div style="flex: 1;">
                    <label for="descricao" style="font-size: 14px; color: #666;">Descrição</label>
                    <input type="text" id="descricao" name="descricao" class="form-control"
                        value="{{ old('descricao', $box->DESCRICAO ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
                <div style="flex: 0.5;">
                    <label for="status" style="font-size: 14px; color: #666;">Status</label>
                    <select id="status" name="status" class="form-select"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="S" {{ old('status', $servico->ATIVO ?? '') == 'S' ? 'selected' : '' }}>Ativo</option>
                        <option value="N" {{ old('status', $servico->ATIVO ?? '') == 'N' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="{{ url('odontologia/consultarbox') }}" class="btn btn-primary" id="voltar">
                    Voltar
                </a>
                <button type="submit" class="btn btn-primary">
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
            window.location.href = "{{ url('odontologia/consultarbox') }}";
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
    <script type="module" src="/js/odontologia/create_box.js"></script>
</body>

</html>