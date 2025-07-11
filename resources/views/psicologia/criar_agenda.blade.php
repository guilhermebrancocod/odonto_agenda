<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon_faesa.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <style>
        html, body { height: 100%; margin: 0; }
        #content-wrapper {
            height: calc(100vh - 56px);
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        #servicos-list button {
            cursor: pointer;
        }
    </style>
</head>
<body>

@include('components.navbar')

<div id="content-wrapper" class="bg-light">
    <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 1000px;">

        <!-- TITULO -->
        <div class="text-center mb-3">
            <h2 class="fs-4 mb-0">Agendamento</h2>
        </div>

        <!-- FORM DE BUSCA DE PACIENTE -->
        <form id="search-form" class="d-flex flex-wrap gap-2 mb-3">
            <input id="search-input" name="search" type="search" class="form-control flex-fill" placeholder="Pesquisar paciente">
            <button type="submit" class="btn btn-primary">Pesquisar</button>
        </form>

        <!-- LISTA DE PACIENTES ENCONTRADOS PARA AGENDAMENTO -->
        <div id="pacientes-list" class="mb-3"></div>

        <!-- PACIENTE SELECIONADO -->
        <div id="paciente-selecionado" class="mb-3"></div>

        <!-- FORM DE AGENDAMENTO -->
        <form action="{{ route('criarAgendamento-Psicologia') }}" method="POST" id="agendamento-form" class="mt-2 w-100">
            @csrf

            <!-- ID DO USUARIO QUE SERÁ ENVIADO COM O FORMULARIO -->
            <input type="hidden" name="paciente_id" id="paciente_id" />

            <!-- ID DO SERVICO QUE SERÁ ENVIADO COM O FORMULARIO -->
            <input type="hidden" name="id_servico" id="id_servico" />

            <!-- HASH DA RECORRENCIA -->
            <input type="hidden" name="recorrencia" id="recorrencia" />

            <!-- STATUS DO AGENDAMENTO PADRÃO -->
            <input type="hidden" name="status_agend" value="Em aberto" />

            <!-- SUTBITULO DO FORMULARIO DE AGENDAMENTO -->
            <div class="mb-2">
                <h5 class="mb-0">Horário</h5>
                <hr class="mt-1">
            </div>

            <div class="row g-2">

                <!-- CHECKBOX TEM RECORRÊNCIA -->
                <div class="col-12 mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="temRecorrencia" name="tem_recorrencia">
                        <label class="form-check-label" for="temRecorrencia">
                            Tem recorrência?
                        </label>
                    </div>
                </div>

                <!-- DIA -->
                <div class="col-sm-6 col-md-2">
                    <label for="data" class="form-label">Dia</label>
                    <input type="date" id="data" name="dia_agend" class="form-control" required>
                </div>

                <!-- HORARIO INICIAL -->
                <div class="col-sm-6 col-md-2">
                    <label for="hr_ini" class="form-label">Horário Início</label>
                    <input type="time" id="hr_ini" name="hr_ini" class="form-control" required>
                </div>

                <!-- HORARIO FINAL -->
                <div class="col-sm-6 col-md-2">
                    <label for="hr_fim" class="form-label">Horário Fim</label>
                    <input type="time" id="hr_fim" name="hr_fim" class="form-control" required>
                </div>

                <!-- CAMPOS DE RECORRÊNCIA (INICIALMENTE OCULTOS) -->
                <div id="recorrenciaCampos" class="col-12 row g-2 mt-2" style="display: none;">
                    <!-- SELEÇÃO DE DIAS DA SEMANA -->
                    <div class="col-sm-6 col-md-6">
                        <label for="dias_semana" class="form-label">Dias da Semana</label>
                            <select id="dias_semana" name="dias_semana[]" class="form-select" multiple>
                                <option value="1">Segunda-feira</option>
                                <option value="2">Terça-feira</option>
                                <option value="3">Quarta-feira</option>
                                <option value="4">Quinta-feira</option>
                                <option value="5">Sexta-feira</option>
                                <option value="6">Sábado</option>
                                <option value="0">Domingo</option>
                            </select>
                            <small class="text-muted">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar vários dias.</small>
                    </div>

                    <!-- DATA FINAL DA RECORRÊNCIA -->
                    <div class="col-sm-6 col-md-4">
                        <label for="data_fim_recorrencia" class="form-label">Data Fim Recorrência</label>
                        <input type="date" id="data_fim_recorrencia" name="data_fim_recorrencia" class="form-control">
                    </div>
                </div>

                <!-- SERVICO -->
                <div class="col-sm-6 col-md-3 position-relative">
                    <label for="servico" class="form-label">Serviço</label>
                    <input type="text" id="servico" name="servico" class="form-control" autocomplete="off" required>
                    <!-- LISTA DE SERVICOS COM BASE NA PESQUISA -->
                    <div id="servicos-list" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                </div>

                <!-- VALOR -->
                <div class="col-sm-6 col-md-3">
                    <label for="valor_agend" class="form-label">Valor</label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" name="valor_agend" id="valor_agend" class="form-control" aria-label="Valor em reais com vírgula e duas casas decimais">
                    </div>
                </div>

                <!-- OBSERVACOES -->
                <div class="input-group">
                    <textarea name="observacoes" id="observacoes" class="form-control" placeholder="Observações..." rows="5" cols="50" style="height: 100px;"></textarea>
                </div>

                <!-- BOTAO DE SUBMIT -->
                <div class="col-12 text-end mt-2">
                    <button type="submit" class="btn btn-success">Agendar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

<script>

// FORMULÁRIO DE BUSCA DE PACIENTES
const searchForm = document.getElementById('search-form');

// INPUT DO NOME/CPF DO PACIENTE
const searchInput = document.getElementById('search-input');

// LISTA DE PACIENTES ENCONTRADOS PARA AGENDAMENTO
const pacientesList = document.getElementById('pacientes-list');

// PACIENTE SELECIONADO APÓS BUSCA
const pacienteSelecionadoDiv = document.getElementById('paciente-selecionado');


const pacienteIdInput = document.getElementById('paciente_id');


// PESQUISA PACIENTE - FUNCIONALIDADES
searchForm.addEventListener('submit', function(e) {

    e.preventDefault(); // EVITA RECARREGAMENTO DA PÁGINA

    // RESGATA O NOME DO PACINETE | REMOVE OS ESPAÇOS EM BRANCO COM A FUNÇÃO TRIM()
    const nome = searchInput.value.trim();

    fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
        .then(response => response.json())
        .then(pacientes => {

            // AO BUSCAR, OS VALORES ABAIXO SÃO ZERADOS
            pacientesList.innerHTML = '';
            pacienteSelecionadoDiv.innerHTML = '';
            pacienteIdInput.value = '';

            // CASO NENHUM PACIENTE SEJA ENCONTRADO - MOSTRA MENSAGEM
            if (pacientes.length === 0) {
                pacientesList.innerHTML = `<div class="alert alert-warning">Nenhum paciente encontrado.</div>`;
                return;
            }

            // CRIA ELEMENTO HTML
            const listGroup = document.createElement('div');
            listGroup.classList.add('list-group');

            pacientes.forEach(paciente => {
                const item = document.createElement('button');
                item.type = 'button';
                item.classList.add('list-group-item', 'list-group-item-action');
                item.textContent = `${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE}) - ${new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR')}`;
                item.addEventListener('click', () => {
                    pacienteSelecionadoDiv.innerHTML = 
                    `<div class="alert alert-success d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Paciente selecionado:</strong> ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE})
                        </div>
                        <button type="button" id="cancelar-paciente" class="btn btn-sm btn-outline-danger ms-2">Cancelar</button>
                    </div>
                    `;
                    
                    pacienteIdInput.value = paciente.ID_PACIENTE;
                    pacientesList.innerHTML = '';

                    // LISTENER DO BOTÃO DE CANCELAR
                    document.getElementById('cancelar-paciente').addEventListener('click', () => {
                        pacienteSelecionadoDiv.innerHTML = '';
                        pacienteIdInput = '';
                    })
                });
                listGroup.appendChild(item);
            });

            pacientesList.appendChild(listGroup);
        })
        .catch(error => {
            pacientesList.innerHTML = `<div class="alert alert-danger">Erro ao buscar pacientes.</div>`;
            console.error(error);
        });
});

// AUTOCOMPLETE SERVIÇO
const servicoInput = document.getElementById("servico");
const servicosList = document.getElementById("servicos-list");
let timeout = null;

servicoInput.addEventListener('input', () => {

    clearTimeout(timeout); // Cancela o temporizador anterior, caso exista.

    timeout = setTimeout(() => {

        // RESGATA O VALOR DE BUSCA E TIRA ESPAÇOS EM BRANCO
        const query = servicoInput.value.trim();

        if (!query) {
            servicosList.innerHTML = '';
            return;
        }

        // CODIFICA O VALOR DA QUERY POR QUESTÕES DE SEGURANÇA
        fetch(`/psicologia/pesquisar-servico?search=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(servicos => {
                servicosList.innerHTML = '';
                
                // CASO NÃO ENCONTRE NENHUM SERVIÇO COM A BUSCA FEITA
                if (servicos.length === 0) {
                    servicosList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled">Nenhum serviço encontrado</button>`;
                    return;
                }
                
                servicos.forEach(servico => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.classList.add('list-group-item', 'list-group-item-action');
                    item.textContent = servico.SERVICO_CLINICA_DESC; // PEGA A DESCRIÇÃO DO SERVIÇO E MOSTRA NA CAIXA DE SELEÇÃO
                    item.addEventListener('click', () => {
                        servicoInput.value = servico.SERVICO_CLINICA_DESC;
                        document.getElementById('id_servico').value = servico.ID_SERVICO_CLINICA;
                        servicosList.innerHTML = '';
                    });
                    servicosList.appendChild(item);
                });

            })
            .catch(error => {
                console.error(error);
                servicosList.innerHTML = `<button type="button" class="list-group-item list-group-item-action disabled text-danger">Erro ao buscar serviços</button>`;
            });
    }, 300);
});

// FECHA A LISTA AO CLICAR FORA
// FUNÇÃO ACIONADA QUANDO O USUÁRIO CLICA EM QUALQUER PARTE DA PÁGINA(document)
document.addEventListener('click', (e) => {

    //  VERIFICA SE O CLIQUE FOI FORA DOS DOIS ELEMENTOS ESPECÍFICOS
    if (!servicoInput.contains(e.target) && !servicosList.contains(e.target)) {
        servicosList.innerHTML = '';
    }
});

// EXIBE OU OCULTA CAMPOS DE RECORRÊNCIA AO MARCAR O CHECKBOX
const temRecorrenciaCheckbox = document.getElementById('temRecorrencia');
const recorrenciaCampos = document.getElementById('recorrenciaCampos');

temRecorrenciaCheckbox.addEventListener('change', function() {
    if (this.checked) {
        recorrenciaCampos.style.display = 'flex'; // mostra os campos
    } else {
        recorrenciaCampos.style.display = 'none'; // esconde os campos
        // limpa os campos se desmarcar
        document.getElementById('dias_semana').selectedIndex = -1;
        document.getElementById('data_fim_recorrencia').value = '';
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // GERA HASH AO MARCAR RECORRÊNCIA
    const temRecorrenciaCheckbox = document.getElementById('temRecorrencia');
    const recorrenciaInput = document.getElementById('recorrencia');

    temRecorrenciaCheckbox.addEventListener('change', function() {
        if (this.checked) {
            recorrenciaInput.value = crypto.randomUUID();
        } else {
            recorrenciaInput.value = '';
        }
    });
});
</script>

</body>

</html>
