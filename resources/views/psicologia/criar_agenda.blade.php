<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Agendamento</title>
    <link rel="icon" type="img/png" href="favicon_faesa.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
</head>
<body>
    <div id="navbar-container">
        <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

            <!-- TÍTULO SEÇÃO CRIAÇÃO DE AGENDAMENTOS -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">Agendamento</h2>
            </div>

            <!-- FORMULÁRIO DE PESQUISA DE PACIENTE PARA CRIAÇÃO DE AGENDAMENTO -->
            <div style="margin-bottom: 20px;">
                <form id="search-form" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <div style="flex: 1;">
                        <input
                            id="search-input"
                            name="search"
                            type="search"
                            class="form-control"
                            placeholder="Pesquisar paciente"
                            style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;"
                        />
                    </div>
                    <button
                        type="submit"
                        style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;"
                    >
                        Pesquisar
                    </button>
                </form>
            </div>

            <!-- PACIENTES ENCONTRADOS - PREENCHIDO DINAMICAMENTE -->
            <div id="pacientes-list" style="margin-bottom: 20px;"></div>

            <!-- PACIENTE SELECIONADO -->
            <div id="paciente-selecionado" style="margin-bottom: 20px;"></div>

            <!-- FORMULÁRIO DE AGENDAMENTO -->
            <form action="" method="POST" id="agendamento-form">
                @csrf

                <input type="hidden" name="paciente_id" id="paciente_id" />

                <!-- TÍTULO DA SEÇÃO DE AGENDAMENTO -->
                <div class="linha-com-titulo">
                    <h5>Horário</h5>
                    <div class="linha-flex"></div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin: 20px 0;">

                    <!-- DIA DO AGENDAMENTO -->
                    <div style="flex: 0.2">
                        <label for="data" style="font-size: 14px; color: #666;">Dia</label>
                        <input type="date" id="data" name="dia_agend" class="form-control" required />
                    </div>

                    <!-- HORARIO INICIAL -->
                    <div style="flex: 0.2">
                        <label for="hr_ini" style="font-size: 14px; color: #666;">Horário Início</label>
                        <input type="time" id="hr_ini" name="hr_ini" class="form-control" required />
                    </div>

                    <!-- HORARIO FINAL -->
                    <div style="flex: 0.2">
                        <label for="hr_fim" style="font-size: 14px; color: #666;">Horário Fim</label>
                        <input type="time" id="hr_fim" name="hr_fim" class="form-control" required />
                    </div>

                    <!-- TIPO DE AGENDAMENTO -->
                    <div style="flex: 0.2">
                        <label for="tipo" style="font-size: 14px; color: #666;">Tipo</label>
                        <input
                            type="text"
                            id="tipo"
                            name="tipo_recorrencia"
                            class="form-control"
                            placeholder="Ex: Psicoterapia"
                            required
                        />
                    </div>

                    <div>
                        <label for="servico" style="font-size: 14px; color: #666;">Servico</label>
                        <input type="text" id="servico" name="servico" class="form-control" placeholder="" required>
                    </div>

                    <!-- BOTÃO DE AGENDAR -->
                    <div style="flex: 0.2; text-align: right;">
                        <button
                            type="submit"
                            style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 6px; cursor: pointer;"
                        >
                            Agendar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- JAVA SCRIPT -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script>
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const pacientesList = document.getElementById('pacientes-list');
        const pacienteSelecionadoDiv = document.getElementById('paciente-selecionado');
        const pacienteIdInput = document.getElementById('paciente_id');

        // Ao enviar o formulário de pesquisa
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault(); // NÃO PERMITE RECARREGAR A PÁGINA - deixa mais dinâmico

            const nome = searchInput.value.trim();

            fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {
                    // Limpa listas e selecionados
                    pacientesList.innerHTML = '';
                    pacienteSelecionadoDiv.innerHTML = '';
                    pacienteIdInput.value = '';

                    if (pacientes.length === 0) {
                        pacientesList.innerHTML = `<p style="color: red;">Nenhum paciente encontrado.</p>`;
                        return;
                    }

                    const ul = document.createElement('ul');
                    ul.style.listStyle = 'none';
                    ul.style.padding = '0';

                    // CASO SEJA MAIOR QUE 3, ADICIONA SCROLL
                    if (pacientes.length > 3) {
                        ul.style.maxHeight = '200px';
                        ul.style.overflowY = 'auto';
                        ul.style.border = '1px solid #ddd';
                        ul.style.padding = '10px';
                        ul.style.borderRadius = '6px';
                    }

                    pacientes.forEach(paciente => {
                        const li = document.createElement('li');
                        li.style.padding = '10px';
                        li.style.border = '1px solid #ddd';
                        li.style.borderRadius = '6px';
                        li.style.marginBottom = '10px';
                        li.style.display = 'flex';
                        li.style.justifyContent = 'space-between';
                        li.style.alignItems = 'center';

                        li.innerHTML = `
                            <span>
                                ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE}) - 
                                ${new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR')}
                            </span>
                            <button type="button" class="btn btn-sm btn-primary">Selecionar</button>
                        `;

                        li.querySelector('button').addEventListener('click', () => {
                            pacienteSelecionadoDiv.innerHTML = `
                                <div class="alert alert-success">
                                    <strong>Paciente selecionado:</strong> ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE})
                                </div>
                            `;
                            pacienteIdInput.value = paciente.ID_PACIENTE;
                            pacientesList.innerHTML = '';
                        });

                        ul.appendChild(li);
                    });

                    pacientesList.appendChild(ul);
                })
                // CASO DÊ ERRO AO BUSCAR PACIENTES
                .catch(error => {
                    pacientesList.innerHTML = `<p style="color: red;">Erro ao buscar pacientes.</p>`;
                    console.error(error);
                });
        });
    </script>
</body>
</html>