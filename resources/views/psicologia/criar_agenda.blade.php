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
        html, body {
            height: 100%;
            margin: 0;
        }
        #content-wrapper {
            height: calc(100vh - 56px); /* altura da navbar padrão Bootstrap */
            overflow-y: auto;
            padding: 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
    </style>
</head>
<body>

    @include('components.navbar')

    <div id="content-wrapper" class="bg-light">
        <div class="bg-white p-4 rounded shadow-sm w-100" style="max-width: 1000px;">
            <!-- Título -->
            <div class="text-center mb-3">
                <h2 class="fs-4 mb-0">Agendamento</h2>
            </div>

            <!-- Form de busca -->
            <form id="search-form" class="d-flex flex-wrap gap-2 mb-3">
                <input id="search-input" name="search" type="search" class="form-control flex-fill" placeholder="Pesquisar paciente">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
            </form>

            <!-- Pacientes encontrados -->
            <div id="pacientes-list" class="mb-3"></div>

            <!-- Paciente selecionado -->
            <div id="paciente-selecionado" class="mb-3"></div>

            <!-- Form de agendamento -->
            <form action="" method="POST" id="agendamento-form" class="mt-2 w-100">
                @csrf
                <input type="hidden" name="paciente_id" id="paciente_id" />

                <div class="mb-2">
                    <h5 class="mb-0">Horário</h5>
                    <hr class="mt-1">
                </div>

                <div class="row g-2">
                    <div class="col-sm-6 col-md-2">
                        <label for="data" class="form-label">Dia</label>
                        <input type="date" id="data" name="dia_agend" class="form-control" required>
                    </div>

                    <div class="col-sm-6 col-md-2">
                        <label for="hr_ini" class="form-label">Horário Início</label>
                        <input type="time" id="hr_ini" name="hr_ini" class="form-control" required>
                    </div>

                    <div class="col-sm-6 col-md-2">
                        <label for="hr_fim" class="form-label">Horário Fim</label>
                        <input type="time" id="hr_fim" name="hr_fim" class="form-control" required>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <input type="text" id="tipo" name="tipo_recorrencia" class="form-control" placeholder="Ex: Psicoterapia" required>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <label for="servico" class="form-label">Serviço</label>
                        <input type="text" id="servico" name="servico" class="form-control" required>
                    </div>

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
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const pacientesList = document.getElementById('pacientes-list');
        const pacienteSelecionadoDiv = document.getElementById('paciente-selecionado');
        const pacienteIdInput = document.getElementById('paciente_id');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nome = searchInput.value.trim();

            fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {
                    pacientesList.innerHTML = '';
                    pacienteSelecionadoDiv.innerHTML = '';
                    pacienteIdInput.value = '';

                    if (pacientes.length === 0) {
                        pacientesList.innerHTML = `<div class="alert alert-warning">Nenhum paciente encontrado.</div>`;
                        return;
                    }

                    const listGroup = document.createElement('div');
                    listGroup.classList.add('list-group');

                    pacientes.forEach(paciente => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.innerHTML = `
                            ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE}) - ${new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR')}
                        `;
                        item.addEventListener('click', () => {
                            pacienteSelecionadoDiv.innerHTML = `
                                <div class="alert alert-success">
                                    <strong>Paciente selecionado:</strong> ${paciente.NOME_COMPL_PACIENTE} (${paciente.CPF_PACIENTE})
                                </div>
                            `;
                            pacienteIdInput.value = paciente.ID_PACIENTE;
                            pacientesList.innerHTML = '';
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
    </script>
</body>
</html>
