<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Procedimento</title>
    <link rel="icon" type="image/png" href="/img/faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    @include('components.sidebar')
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <fieldset class="border p-3 rounded mb-3">
            <legend class="w-auto px-2">Cadastro de Procedimento</legend>
        </fieldset>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($servico) ? route('updateProcedures', $servico->ID_SERVICO_CLINICA) : route('createProcedures') }}"
            method="POST">
            @csrf
            @if(isset($servico))
            @method('PUT')
            @endif
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 20px; margin: 15px 0; flex-wrap: wrap;">

                <div style="flex: 1; min-width: 250px;">
                    <label for="descricao" style="display: block; font-size: 16px; color: #666; margin-bottom: 6px;">Descrição</label>
                    <input type="text" id="descricao" name="descricao" class="form-control"
                        value="{{ old('descricao', $servico->SERVICO_CLINICA_DESC ?? '') }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;"
                        maxlength="255" />
                </div>

                <div style="width: 150px; min-width: 120px;">
                    <label for="valor" style="display: block; font-size: 16px; color: #666; margin-bottom: 6px;">Valor</label>
                    <input type="text" id="valor" name="valor" class="form-control"
                        value="{{ old('valor', $servico->VALOR_SERVICO ?? '') }}"
                        style="width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;"
                        maxlength="10" />
                </div>
                <div style="width: 120px; min-width: 120px;">
                    <label for="ativo" style="display: block; font-size: 16px; color: #666; margin-bottom: 6px;">Ativo</label>
                    <select id="ativo" name="ativo" class="form-select"
                        style="width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;">
                        <option value=""></option>
                        <option value="S" {{ old('ativo', $servico->ATIVO ?? '') == 'S' ? 'selected' : '' }}>Sim</option>
                        <option value="N" {{ old('ativo', $servico->ATIVO ?? '') == 'N' ? 'selected' : '' }}>Não</option>
                    </select>
                </div>
            </div>
            <div class="linha-com-titulo" style="margin-bottom: 5px">
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button id="voltar" name="voltar" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Voltar
                </button>
                <button style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Salvar
                </button>
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
            window.location.href = "{{ url('odontologia/consultarservico') }}";
        });
    </script>
    @endif

    @if ($errors->any())
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Erro ao validar dados',
            html: `
            <ul style="text-align:left;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        `,
        });
    </script>
    @endif
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_service.js"></script>
</body>

</html>