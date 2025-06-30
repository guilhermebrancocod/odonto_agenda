<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet">
</head>

<body>
    <div id="navbar-container"></div>
    <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px; color: #333;">Agendamento</h2>
        </div>
        <form class="row g-3 needs-validation">
            <div class="linha-com-titulo">
                <h5>Paciente</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <form action="{{ route('selectPatient') }}" method="GET">
                    <div class="input-group" style="flex: 1;">
                        <div class="form-outline" data-mdb-input-init>
                            <input id="search-input" type="search" class="form-control"
                                style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" />
                            <label class="form-label" for="search-input">Pesquisar</label>
                        </div>
                    </div>
                </form>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='reload' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Limpar">
                        <iconify-icon icon="streamline:arrow-round-left-solid"></iconify-icon>
                    </button>
                </div>
                <div style="flex-shrink: 0;">
                    <button type="submit" id='add' style="background-color: #007bff; color: #fff; border: none; padding: 10px 15px; font-size: 14px; border-radius: 6px; cursor: pointer;" title="Adicionar paciente">
                        <iconify-icon icon="ix:add-circle-filled"></iconify-icon>
                    </button>
                </div>
            </div>
            <div class="linha-com-titulo">
                <h5>Horário</h5>
                <div class="linha-flex"></div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin: 20px 0;">
                <div style="flex: 0.1">
                    <label for="data" style="font-size: 14px; color: #666;">Dia</label>
                    <input type="date" id="data" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="flex: 0.2">
                    <label for="hr_ini" style="font-size: 14px; color: #666;">Horário Início</label>
                    <input type="text" id="hr_ini" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="flex: 0.2">
                    <label for="hr_fim" style="font-size: 14px; color: #666;">Horário Fim</label>
                    <input type="text" id="hr_fim" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                <div style="flex: 0.3;">
                    <label for="tipo" style="font-size: 14px; color: #666;">Recorrencia</label>
                    <select type="text" id="tipo" class="selectpicker" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value=""></option>
                        <option value="">Pontual</option>
                        <option value="">Semanal</option>
                        <option value="">Trimestral</option>
                        <option value="">Semestral</option>
                        <option value="">Anual</option>
                    </select>
                </div>
                <div style="flex: 0.3;">
                    <label for="tipo" style="font-size: 14px; color: #666;">Tipo</label>
                    <select type="text" id="tipo" class="selectpicker" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value=""></option>
                        <option value="">Segunda-feira</option>
                        <option value="">Terça-feira</option>
                        <option value="">Quarta-feira</option>
                        <option value="">Quinta-feira</option>
                        <option value="">Sexta-feira</option>
                        <option value="">Sabado</option>
                    </select>
                </div>
                <div style="flex: 0.2;">
                    <label for="pagto" style="font-size: 14px; color: #666;">Haverá Pagamento</label>
                    <select type="text" id="pagto" class="selectpicker" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value=""></option>
                        <option value="S">Sim</option>
                        <option value="N">Não</option>
                    </select>
                </div>
                <div style="flex: 0.2">
                    <label for="valor" style="font-size: 14px; color: #666;">Valor</label>
                    <input type="text" id="valor" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;" disabled>
                </div>
            </div>
            <div style="text-align: right;flex:0.3">
                <button id="btn-agendar" type="submit" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;">
                    Agendar
                </button>
            </div>
            <div class="linha-com-titulo">
                <h5></h5>
                <div class="linha-flex"></div>
            </div>
        </form>
    </div>
    @include('odontologia.modal.modal_add_patient')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script type="module" src="/js/odontologia/create_agenda.js"></script>
</body>

</html>