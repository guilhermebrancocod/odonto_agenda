<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastro de Horários</title>
    <link rel="icon" type="image/png" href="{{ asset('faesa_favicon.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- FLATPICKR CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: "Montserrat", sans-serif;
            background-color: #f8f9fa;
        }
        #content-wrapper {
            width: 80vw;
            height: 90vh;
            margin: auto;
            display: flex;
            gap: 24px;
            overflow: hidden;
            align-items: stretch;
        }
        main {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 10px;
            width: 50%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1.8px solid #dee2e6;
        }
        form {
            flex-grow: 1;
            overflow-y: auto;
        }
        h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 16px;
        }
        h5 {
            margin-bottom: 12px;
            font-weight: 600;
        }
        #salvar {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        #salvar:hover {
            background-color: #0056b3;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        #servicos-lista {
            flex-grow: 1;
            overflow-y: auto;
            max-height: 100%;
        }

        table {
            border-collapse: separate;
            border-spacing: 0 12px;
            width: 100%;
        }
        thead tr th:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        thead tr th:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        tbody tr {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-radius: 12px;
            transition: box-shadow 0.3s ease;
        }
        tbody tr:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }
        tbody tr td:first-child {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        tbody tr td:last-child {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        @media (max-width: 768px) {
            #content-wrapper {
                flex-direction: column;
                width: 90vw;
                height: auto;
            }
            main {
                width: 100%;
            }
        }
        /* ALERT FIXO NO TOPO */
        #alert-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1055;
            width: auto;
            max-width: 90%;
            pointer-events: none;
        }
        .flatpickr-input {
            background-image: none !important;
        }
        .flatpickr-calendar-arrow {
            display: none !important;
        }
    </style>
</head>

<body>
@include('components.navbar')

<div id="alert-container"></div>

<div id="content-wrapper">
    <main>
        <div class="text-center">
            <h2>Cadastro de Horários</h2>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showAlert("{{ session('success') }}", 'success');
                });
            </script>
        @endif

        <!-- FORMULÁRIO DE CRIAÇÃO DE HORARI0 -->
        <form class="needs-validation" action="{{ route('criarHorario-Psicologia') }}" method="POST" novalidate>

            @csrf

            <!-- ID DA CLINICA - NO CASO PSICOLOGIA -->
            <input type="hidden" name="ID_CLINICA" value="1">

            <!-- TÍTULO -->
            <h5 class="mt-3">Informações</h5>

            <hr>

            <!-- SELEÇÃO DE HORARIO PERMISSAO OU HORAIRO BLOQ -->
            <div class="mt-3 mb-1">
                <label for="TIPO_HORARIO" class="form-label">Tipo de Horário</label>
                <select id="TIPO_HORARIO" name="TIPO_HORARIO" class="form-select" required>
                    <option value="" disabled selected>Selecione o tipo de horário</option>
                    <option value="N">Horário de Atendimento</option>
                    <option value="S">Horário Bloqueado</option>
                </select>
                <div class="invalid-feedback">
                    Selecione se o horário é de atendimento ou bloqueado.
                </div>
            </div>

            <!-- DATA INÍCIO -->
            <div class="mt-2">
                <label for="DATA_HORARIO_INICIAL" class="form-label">Data Inicial</label>
                <input type="text" id="DATA_HORARIO_INICIAL" name="DATA_HORARIO_INICIAL" class="form-control" value="{{ old('DATA_HORARIO_INICIAL') }}">
            </div>

            <!-- DATA FINAL -->
            <div class="mt-2">
                <label for="DATA_HORARIO_FINAL" class="form-label">Data Final</label>
            <input type="text" id="DATA_HORARIO_FINAL" name="DATA_HORARIO_FINAL" class="form-control" value="{{ old('DATA_HORARIO_FINAL') }}">
            </div>

            <!-- HORÁRIO INÍCIO -->
            <div class="mt-2">
                <label for="HR_HORARIO_INICIAL" class="form-label">Horário Inicial</label>
                <input type="time" id="HR_HORARIO_INICIAL" name="HR_HORARIO_INICIAL" class="form-control" value="{{ old('HR_HORARIO_INICIAL') }}">
            </div>

             <!-- HORÁRIO FINAL -->
            <div class="mt-2">
                <label for="HR_HORARIO_FINAL" class="form-label">Horário Final</label>
                <input type="time" id="HR_HORARIO_FINAL" name="HR_HORARIO_FINAL" class="form-control" value="{{ old('HR_HORARIO_FINAL') }}">
            </div>

            <!-- DESCRICAO -->
            <div class="mt-2">
                <label for="DESCRICAO_HORARIO" class="form-label">Descrição</label>
                <input type="text" id="DESCRICAO_HORARIO" name="DESCRICAO_HORARIO" class="form-control" value="{{ old('DESCRICAO_HORARIO') }}" required>
            </div>

            <!-- OBSERVACAO -->
                <div class=" mt-2">
                    <label for="OBSERVACAO" class="form-label">Observações</label>
                    <textarea name="OBSERVACAO" id="OBSERVACAO" class="form-control" placeholder="Observações..." rows="3">{{ old('OBSERVACAO') }}</textarea>
                </div> 

            <!-- BOTÃO DE SALVAR | SUBMIT -->
            <div class="text-end mt-4">
                <button id="salvar" type="submit">Salvar</button>
            </div>
            
        </form>
    </main>

    <!-- LISTAGEM DE HORÁRIOS  -->
    <main style="overflow-y:auto; max-height: 90vh;">

        <!-- TITULO LISTAGEM DE HORARIOS -->
        <h2 class="text-center mb-4">Consulta e Edição de Horários</h2>

        <!-- INPUT DE BUSCA -->
        <input type="text" id="search-horario" class="form-control mb-3" placeholder="Buscar horario por descrição..." />

        <!-- LISTAGEM DE HORARIOS -->
        <div id="horarios-lista" style="max-height: 65vh; overflow-y:auto;">
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Tipo</th>
                </tr>
                </thead>
                <tbody id="horarios-tbody">
                <tr><td colspan="5" class="text-center">Carregando...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL DE EDIÇÃO DE HORÁRIO -->
        <div class="modal fade" id="editarHorarioModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div id="modal-alert-container"></div>
                        <form id="form-editar-horario">

                            <!-- HEADER DO MODAL -->
                            <div class="modal-header">
                                <h5 class="modal-title">Editar Horário</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <!-- CORPO DO MODAL -->
                            <div class="modal-body">

                                <input type="hidden" id="edit-horario-id" name="ID_HORARIO" />

                                <!-- EDIÇÃO DE DESCRIÇÃO DO HORÁRIO -->
                                <div class="mb-3">
                                    <label class="form-label">Descrição</label>
                                    <input type="text" id="edit-horario-desc" name="HORARIO" class="form-control" required />
                                </div>

                                <!-- EDIÇÃO DE TIPO DE HORÁRIO -->
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Horário</label>
                                    <select id="edit-tipo-horario" name="TIPO_HORARIO" class="form-select" required>
                                        <option value="" disabled selected>Selecione o tipo de horário</option>
                                        <option value="N">Horário de Atendimento</option>
                                        <option value="S">Horário Bloqueado</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Selecione se o horário é de atendimento ou bloqueado.
                                    </div>
                                </div>

                                <!-- EDIÇÃO DE DATA INICIAL -->
                                <div class="mb-3">
                                    <label class="form-label">Data Inicial</label>
                                    <input type="text" id="edit-data-horario-inicial" name="DATA_HORARIO_INICIAL" class="form-control" required />
                                </div>

                                <!-- EDIÇÃO DE DATA FINAL -->
                                <div class="mb-3">
                                    <label class="form-label">Data Final</label>
                                    <input type="text" id="edit-data-horario-final" name="DATA_HORARIO_FINAL" class="form-control" required />
                                </div>

                                <!-- EDIÇÃO DE HORÁRIO INICIAL -->
                                <div class="mb-3">
                                    <label class="form-label">Horário Inicial</label>
                                    <input type="time" id="edit-hr-horario-inicial" name="HR_HORARIO_INICIAL" class="form-control" required />
                                </div>

                                <!-- EDIÇÃO DE HORÁRIO FINAL -->
                                <div class="mb-3">
                                    <label class="form-label">Horário Final</label>
                                    <input type="time" id="edit-hr-horario-final" name="HR_HORARIO_FINAL" class="form-control" required />
                                </div>

                                <!-- EDIÇÃO DE OBSERVAÇÃO -->
                                <div class="mb-3">
                                    <label class="form-label">Observações</label>
                                    <textarea id="edit-observacao" name="OBSERVACAO" class="form-control" rows="3">{{ old('OBSERVACAO') }}</textarea>
                                </div>
                                
                            <!-- RODAPÉ DO MODAL -->
                            <div class="modal-footer d-flex justify-content-between">
                                <button type="button" class="btn btn-danger" id="btn-deletar-horario">Excluir</button>
                                <div>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>

<!-- FLATPICKR -->
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script> <!-- Adiciona Linguagem em Português -->

<script>
    // === ALERTA GLOBAL ===
    function showAlert(message, type = 'success') {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alert.role = 'alert';
        alert.style.pointerEvents = 'auto';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alert);

        setTimeout(() => {
            alert.classList.remove('show');
            alert.classList.add('hide');
            setTimeout(() => alert.remove(), 300);
        }, 4000);
    }

    // === ALERTA DENTRO DO MODAL ===
    function showModalAlert(message, type = 'danger') {
        const modalAlertContainer = document.getElementById('modal-alert-container');
        modalAlertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show m-3 mb-0">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
    }

    document.addEventListener('DOMContentLoaded', () => {
        // === VARIÁVEIS GLOBAIS ===
        const horariosTbody = document.getElementById('horarios-tbody');
        const searchInput = document.getElementById('search-horario');
        const editarHorarioModal = new bootstrap.Modal(document.getElementById('editarHorarioModal'));
        const formEditarHorario = document.getElementById('form-editar-horario');

        // === CARREGAR HORÁRIOS ===
        function carregarHorarios(search = '') {
            fetch(`/psicologia/horarios/listar?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(horarios => {
                    horariosTbody.innerHTML = '';

                    if (horarios.length === 0) {
                        horariosTbody.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center">Nenhum horário encontrado</td>
                            </tr>
                        `;
                        return;
                    }

                    horarios.forEach(horario => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${horario.DESCRICAO_HORARIO}</td>
                            <td>${horario.BLOQUEADO === 'S' ? 'Bloqueio' : 'Atendimento'}</td>
                            <td>
                                <button class="btn btn-primary btn-sm btn-editar"
                                    data-id="${horario.ID_HORARIO}"
                                    data-desc="${horario.DESCRICAO_HORARIO}"
                                    data-tipo="${horario.BLOQUEADO}"
                                    data-dataInicial="${horario.DATA_HORARIO_INICIAL ?? ''}"
                                    data-dataFinal="${horario.DATA_HORARIO_FINAL ?? ''}"
                                    data-horarioInicial="${horario.HR_HORARIO_INICIAL ?? ''}"
                                    data-horarioFinal="${horario.HR_HORARIO_FINAL ?? ''}"
                                    data-observacao="${horario.OBSERVACAO ?? ''}">
                                    Editar
                                </button>
                            </td>
                        `;
                        horariosTbody.appendChild(tr);
                    });

                    document.querySelectorAll('.btn-editar').forEach(btn => {
                        btn.addEventListener('click', () => {
                            document.getElementById('edit-horario-id').value = btn.dataset.id;
                            document.getElementById('edit-horario-desc').value = btn.dataset.desc;
                            document.getElementById('edit-tipo-horario').value = btn.dataset.tipo;
                            document.getElementById('edit-data-horario-inicial').value = btn.dataset.dataInicial;
                            document.getElementById('edit-data-horario-final').value = btn.dataset.dataFinal;
                            document.getElementById('edit-hr-horario-inicial').value = btn.dataset.horarioInicial;
                            document.getElementById('edit-hr-horario-final').value = btn.dataset.horarioFinal;
                            document.getElementById('edit-observacao').value = btn.dataset.observacao ?? '';
                            editarHorarioModal.show();
                        });
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar horários:', error);
                    horariosTbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger">Erro ao carregar horários</td>
                        </tr>
                    `;
                });
        }   


        // === CHAMADA INICIAL ===
        carregarHorarios();

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit-horario--id').value = btn.dataset.id;
                document.getElementById('edit-horario-desc').value = btn.dataset.desc;

                // Se for '--', envia vazio para o campo para não quebrar a validação
                // VALORES ATUAIS PARA EDIÇÃO NO MODAL DE EDIÇÃO    
                document.getElementById('edit-tipo-horario').value = (btn.dataset.cod === '--') ? '' : btn.dataset.tipo;
                document.getElementById('edit-valor-servico').value = btn.dataset.valor;
                document.getElementById('edit-permite-simultaneo').checked = (btn.dataset.permite === 'S');
                document.getElementById('edit-observacao-servico').value = btn.dataset.observacao ?? '';
                document.getElementById('edit-tempo-recorrencia-meses').value = btn.dataset.tempo ?? '';
                editarServicoModal.show();
            });
        });

        // === FILTRAR PESQUISA ===
        searchInput.addEventListener('input', () => {
            carregarHorarios(searchInput.value);
        });

        // === DELETAR HORÁRIO ===
        document.getElementById('btn-deletar-horario').addEventListener('click', () => {
            if (!confirm('Tem certeza que deseja excluir este horário?')) return;

            const id = document.getElementById('edit-horario-id').value;

            fetch(`/psicologia/horarios/deletar/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            })
                .then(async res => {
                    if (!res.ok) {
                        const data = await res.json();
                        throw new Error(data.message || 'Erro ao excluir.');
                    }
                    return res.json();
                })
                .then(() => {
                    showAlert('Horário excluído com sucesso!', 'success');
                    editarHorarioModal.hide();
                    carregarHorarios(searchInput.value);
                })
                .catch(err => {
                    showModalAlert(err.message, 'warning');
                });
        });

        // === SUBMIT FORMULÁRIO DE EDIÇÃO ===
        formEditarHorario.addEventListener('submit', (e) => {
            e.preventDefault();

            const id = document.getElementById('edit-horario-id').value;
            const desc = document.getElementById('edit-horario-desc').value;
            const tipo = document.getElementById('edit-tipo-horario').value;
            const dataInicial = document.getElementById('edit-data-horario-inicial');
            const dataFinal = document.getElementById('edit-data-horario-final');
            const hrInicial = document.getElementById('edit-hr-horario-inicial');
            const hrFinal = document.getElementById('edit-hr-horario-final');
            const observacao = document.getElementById('edit-observacao').value;

            fetch(`/psicologia/horarios/atualizar/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    DESCRICAO_HORARIO: desc,
                    TIPO_HORARIO: tipo,
                    DATA_HORARIO_INICIAL: dataInicial.value,
                    DATA_HORARIO_FINAL: dataFinal.value,
                    HR_HORARIO_INICIAL: hrInicial.value,
                    HR_HORARIO_FINAL: hrFinal.value,
                    OBSERVACAO: observacao
                })
            })
                .then(res => {
                    if (!res.ok) throw new Error('Erro ao salvar');
                    return res.json();
                })
                .then(() => {
                    showAlert('Horário atualizado com sucesso!', 'success');
                    editarHorarioModal.hide();
                    carregarHorarios(searchInput.value);
                })
                .catch(() => {
                    showModalAlert('Erro ao atualizar horário. Verifique os campos ou tente novamente.', 'danger');
                });
        });
    });
</script>

<!-- FLATPICKR -->
<script>
        flatpickr("#DATA_HORARIO_INICIAL", {
            dateFormat: "Y-m-d",   
            altInput: true,
            altFormat: "d-m-Y",
            locale: "pt",
            minDate: "today",
            allowInput: true,
        });
        flatpickr("#DATA_HORARIO_FINAL", {
            dateFormat: "Y-m-d",   
            altInput: true,
            altFormat: "d-m-Y",
            locale: "pt",
            minDate: "today",
            allowInput: true,
        });
        flatpickr("#HR_HORARIO_INICIAL", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
        });
        flatpickr("#HR_HORARIO_FINAL", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
        });         
</script>

</body>
</html>
