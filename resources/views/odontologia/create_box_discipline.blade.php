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
    <div style="margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h3 style="margin: 0; font-size: 24px; color: #333;">Vincular box com disciplina</h3>
        </div>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($box) ? route('updateBox', $box->ID_BOX_CLINICA) : route('createBox') }}"
            method="POST">
            @csrf
            @if(isset($paciente))
            @method('PUT')
            @endif
            @csrf
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row g-3" style="margin: 20px 0; display: flex; align-items: flex-end;">
                <div style="flex: 0.5;">
                    <label for="box" style="font-size: 14px; color: #666;">Box</label>
                    <select id="box" name="box" class="form-select"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="S">Ativo</option>
                        <option value="N">Inativo</option>
                    </select>
                </div>
                <div style="flex: 0.5;">
                    <label for="disciplina" style="font-size: 14px; color: #666;">Disciplina</label>
                    <select id="disciplina" name="disciplina" class="form-select"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="S">Ativo</option>
                        <option value="N">Inativo</option>
                    </select>
                </div>
                <div style="flex: 0.2;">
                    <label for="hr_ini" style="font-size: 14px; color: #666;">Hora Inicial</label>
                    <input type="text" id="hr_ini" name="hr_ini" class="form-control"
                        value="{{ old('descricao', $servico->SERVICO_CLINICA_DESC ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
                <div style="flex: 0.2;">
                    <label for="hr_fim" style="font-size: 14px; color: #666;">Hora Final</label>
                    <input type="text" id="hr_fim" name="hr_fim" class="form-control"
                        value="{{ old('descricao', $servico->SERVICO_CLINICA_DESC ?? '') }}"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" maxlength="25">
                </div>
                <div style="flex: 0.1;">
                    <!-- Empurra o botão pra baixo -->
                    <button id="adicionar" name="adicionar" type="button"
                        style="background-color: #007bff; color: #fff; border: none; 
                    padding: 10px 15px; font-size: 12px; 
                    border-radius: 6px; cursor: pointer;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button id="voltar" name="voltar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Voltar
                </button>
                <button id="salvar" name="salvar" type="submit" onclick="saveGroupData()" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
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
    <script type="module" src="/js/odontologia/create_box_discipline.js"></script>
</body>

</html>