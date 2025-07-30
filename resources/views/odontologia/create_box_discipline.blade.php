<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
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
            <legend class="w-auto px-2">Vinculo de box com disciplina</legend>
        </fieldset>
        <form id="form" class="row g-3 needs-validation"
            action="{{ isset($BoxDiscipline) ? route('updateBoxDiscipline', $BoxDiscipline->ID_BOX_DISCIPLINA) : route('createBoxDiscipline') }}"
            method="POST">

            @csrf

            @if(isset($BoxDiscipline))
            @method('PUT')
            @endif
            <div class="linha-com-titulo">
                <h5>Detalhes</h5>
                <div class="linha-flex"></div>
            </div>
            <div class="row fields-bloco" style="margin: 20px 0; display: flex; gap: 40px; align-items: flex-start;">
                <div class="col-esquerda" style="flex: 1;">
                    <div style="flex: 1; margin-bottom: 15px;">
                        <label for="disciplina" style="font-size: 14px; color: #666;">Disciplina</label>
                        <select id="disciplina" name="disciplina" class="form-select"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px">
                        </select>
                    </div>
                    <div style="display: flex; justify-content: space-between; gap: 10px; margin-bottom: 15px;">
                        <div style="flex: 0.5;">
                            <label for="dia_semana" style="font-size: 14px; color: #666;">Dia da semana</label>
                            <select id="dia_semana" name="dia_semana" class="form-select"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <option value=""></option>
                                <option value="segunda" {{ old('dia_semana', $BoxDiscipline->DIA_SEMANA ?? '') == 'segunda' ? 'selected' : '' }}>Segunda-feira</option>
                                <option value="terça" {{ old('dia_semana', $BoxDiscipline->DIA_SEMANA ?? '') == 'terça' ? 'selected' : '' }}>Terça-feira</option>
                                <option value="quarta" {{ old('dia_semana', $BoxDiscipline->DIA_SEMANA ?? '') == 'quarta' ? 'selected' : '' }}>Quarta-feira</option>
                                <option value="quinta" {{ old('dia_semana', $BoxDiscipline->DIA_SEMANA ?? '') == 'quinta' ? 'selected' : '' }}>Quinta-feira</option>
                                <option value="sexta" {{ old('dia_semana', $BoxDiscipline->DIA_SEMANA ?? '') == 'sexta' ? 'selected' : '' }}>Sexta-feira</option>
                            </select>
                        </div>
                        <div style="flex: 0.2;">
                            <label for="hr_inicio" style="font-size: 14px; color: #666;">Hora Inicial</label>
                            <input type="time" id="hr_inicio" name="hr_inicio" class="form-control"
                                value="{{ old('hr_inicio', isset($BoxDiscipline) ? substr($BoxDiscipline->HR_INICIO, 0, 5) : '') }}"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        </div>
                        <div style="flex: 0.2;">
                            <label for="hr_fim" style="font-size: 14px; color: #666;">Hora Final</label>
                            <input type="time" id="hr_fim" name="hr_fim" class="form-control"
                                value="{{ old('hr_fim', isset($BoxDiscipline) ? substr($BoxDiscipline->HR_FIM, 0, 5) : '') }}"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
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
    <script>
        const disciplinaSelecionada = @json($BoxDiscipline -> DISCIPLINA ?? '');
    </script>
    @php
    $disciplinas = old('disciplines');
    if (!$disciplinas && isset($servico)) {
    $disciplinas = DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
    ->where('ID_SERVICO_CLINICA', $servico->ID_SERVICO_CLINICA)
    ->pluck('DISCIPLINA')
    ->toArray();
    }
    @endphp
    <script>
        const disciplinasSelecionadas = @json($disciplinas);
    </script>
    @php
    $boxesSelecionados = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
    ->where('ID_BOX_DISCIPLINA', $BoxDiscipline->ID_BOX_DISCIPLINA ?? null)
    ->pluck('ID_BOX')
    ->toArray();
    @endphp

    <script>
        const boxesSelecionados = @json($boxesSelecionados);
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script type="module" src="/js/odontologia/create_box_discipline.js"></script>
</body>

</html>