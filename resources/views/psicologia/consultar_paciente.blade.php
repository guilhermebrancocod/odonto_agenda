<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro</title>
    <link rel="icon" type="img/png" href="faesa_favicon.png" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link href="/css/style.css" rel="stylesheet" />
</head>

<body>
    <div id="navbar-container">
        <div style="max-width: 1200px; margin-left:220px; padding: 30px; border-radius: 10px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

            <!-- TITULO DA SEÇÃO DE CONSULTA DE PACIENTE -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; font-size: 24px; color: #333;">Pesquisando</h2>
            </div>

            <div class="linha-com-titulo">
                <h5>Paciente</h5>
                <div class="linha-flex"></div>
            </div>

            <!-- INPUT DAS INFORMACOES DO PACIENTE PARA PESQUISA -->
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap; margin: 20px 0;">
                <form id="search-form" class="d-flex gap-3 align-items-end flex-wrap" style="margin:20px 0;">
                    <div style="flex: 1;">
                        <input
                            id="search-input"
                            name="search"
                            type="search"
                            class="form-control"
                            placeholder="Pesquisar paciente"
                            style="padding:8px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                        />
                    </div>
                    <div style="flex-shrink: 0;">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size:14px; border-radius:6px;">
                            Pesquisar
                        </button>
                    </div>
                </form>
            </div>

            <!-- TÍTULO DA SEÇÃO DE RESULTADOS DA PESQUISA DE PACIENTE -->
            <div class="linha-com-titulo">
                <h5>Resultado</h5>
                <div class="linha-flex"></div>
            </div>

            <!-- TABELA COM INFORMAÇÕES DO RESULTADO DA PESQUISA POR PACIENTE -->
            <div class="datatable" style="margin-top:25px">
                <table class="table datatable-table">
                    <thead class="datatable-header">
                        <tr>
                            <th>Cod</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data de Nascimento</th>
                            <th>Sexo</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="pacientes-tbody">
                        <tr>
                            <td colspan="8" class="text-center">Nenhuma pesquisa realizada ainda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JAVA SCRIPT -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script>
        const searchForm = document.getElementById('search-form');
        const searchInput = document.getElementById('search-input');
        const pacientesTbody = document.getElementById('pacientes-tbody');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const nome = searchInput.value.trim();

            fetch(`/psicologia/consultar-paciente/buscar?search=${encodeURIComponent(nome)}`)
                .then(response => response.json())
                .then(pacientes => {
                    pacientesTbody.innerHTML = '';

                    // CASO NÃO ACHE PACIENTE ALGUM
                    if (pacientes.length === 0) {
                        pacientesTbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">Nenhum paciente encontrado.</td>
                            </tr>
                        `;
                        return;
                    }

                    // PARA CADA PACIENTE CRIA UM REGISTRO (linha)
                    pacientes.forEach(paciente => {
                        const row = document.createElement('tr');

                        row.innerHTML = `
                            <td>${paciente.ID_PACIENTE ?? '-'}</td>
                            <td>${paciente.NOME_COMPL_PACIENTE ?? '-'}</td>
                            <td>${paciente.CPF_PACIENTE ?? '-'}</td>
                            <td>${paciente.DT_NASC_PACIENTE ? new Date(paciente.DT_NASC_PACIENTE).toLocaleDateString('pt-BR') : '-'}</td>
                            <td>${paciente.SEXO_PACIENTE ?? '-'}</td>
                            <td>${paciente.FONE_PACIENTE ?? '-'}</td>
                            <td>${paciente.E_MAIL_PACIENTE ?? '-'}</td>
                            <td><a href="#" class="btn btn-sm btn-primary">Editar</a></td>
                        `;
                        pacientesTbody.appendChild(row);
                    });
                })
                // EM CASO DE ERRO DURANTE PESQUISA
                .catch(error => {
                    console.error(error);
                    pacientesTbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-danger">Erro ao buscar pacientes.</td>
                        </tr>
                    `;
                });
        });
    </script>
</body>
</html>
