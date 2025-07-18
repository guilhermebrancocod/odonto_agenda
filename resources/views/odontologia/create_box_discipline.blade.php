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
            action="{{ isset($box) ? route('updateBoxDiscipline', $box->ID_BOX_CLINICA) : route('createBoxDiscipline') }}"
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
            <div class="row fields-bloco" style="margin: 20px 0; display: flex; gap: 40px; align-items: flex-start;">
                <div class="col-esquerda" style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; gap: 10px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label for="disciplina" style="font-size: 14px; color: #666;">Disciplina</label>
                            <select id="disciplina" name="disciplina" class="form-select"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label for="dia_semana" style="font-size: 14px; color: #666;">Dia da semana</label>
                            <select id="dia_semana" name="dia_semana" class="form-select"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value=""></option>
                                <option value="segunda">Segunda-feira</option>
                                <option value="terça">Terça-feira</option>
                                <option value="quarta">Quarta-feira</option>
                                <option value="quinta">Quinta-feira</option>
                                <option value="sexta">Sexta-feira</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label for="hr_inicio" style="font-size: 14px; color: #666;">Hora Inicial</label>
                            <input type="time" id="hr_inicio" name="hr_inicio" class="form-control"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                                maxlength="10">
                        </div>
                        <div style="flex: 1;">
                            <label for="hr_fim" style="font-size: 14px; color: #666;">Hora Final</label>
                            <input type="time" id="hr_fim" name="hr_fim" class="form-control"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                                maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="col-direita" style="flex: 1;">
                    <label style="font-size: 14px; color: #666;">Selecionar Box</label>
                    <div id="boxes-container"
                        style="margin-top: 5px; border: 1px solid #ddd; border-radius: 6px; padding: 10px; max-height: 180px; overflow-y: auto; background-color: #f9f9f9;">
                    </div>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; gap: 10px;">
                <button id="voltar" name="voltar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Voltar
                </button>
                <button id="salvar" name="salvar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
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