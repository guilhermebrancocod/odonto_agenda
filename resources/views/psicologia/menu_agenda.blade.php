<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Menu</title>

    <!-- FAVICON - IMAGEM DA GUIA -->
    <link rel="icon" type="image/png" href="/favicon_faesa.png">

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootsrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous">
    </script>
</head >
    
<body>
    
    @include('components.navbar')

    <!-- CALENDÁRIO -->
    <div id="calendar" class="flex-grow-1 p-3 overflow-auto"></div>

    <!-- MODAL PARA DETALHES DO AGENDAMENTO -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1" aria-labelledby="agendamentoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                
            <!-- CABEÇALHO MODAL -->
                <div class="modal-header">
                    <h5 class="modal-title" id="agendamentoModalLabel">Detalhes do Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <!-- CORPO MODAL -->
                <div class="modal-body">
                    <p><strong>Paciente:</strong> <span id="modalPaciente"></span></p>
                    <p><strong>Data e Horário:</strong> <span id="modalDataHora"></span></p>
                    <p><strong>Serviço:</strong> <span id="modalServico"></span></p>
                    <div class="mb-3">
                    <label for="modalStatusSelect" class="form-label"><strong>Status:</strong></label>
                    <select class="form-select" id="modalStatusSelect">
                        <option value="Agendado">Agendado</option>
                        <option value="Presente">Presente</option>
                        <option value="Cancelado">Cancelado</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                    </div>
                    <p><strong>Local:</strong> <span id="modalLocal"></span></p>
                    <p><strong>Observações:</strong> <span id="modalObservacoes"></span></p>
                </div>
                
                <!-- RODAPÉ MODAL -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnSalvarStatus">Salvar Status</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL DE MOTIVO DE CANCELAMENTO -->
    <div class="modal fade" id="motivoCancelamentoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Motivo do Cancelamento</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="text-cancelamento" class="form-label">Motivo:</label>
                        <input type="text" class="form-control" id="text-cancelamento">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="btnMensagemCancelamento">Salvar</button>
                </div>
            </div>
        </div>
    </div>
    
</body >

<!-- FULLCALENDAR SCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    let calendar; // variável global para poder destruir e recriar

    function getCalendarOptions(screenWidth) {
        return {
            initialView: screenWidth <= 600 ? "dayGridDay" : "dayGridMonth",
            timeZone: 'local',
            headerToolbar: screenWidth <= 600
                ? {
                    left: 'prev,next',
                    center: '',
                    right: 'dayGridDay,timeGridWeek,dayGridMonth',
                }
                : {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridDay,timeGridWeek,dayGridMonth',
                },
            slotMinTime: "08:00:00",
            slotMaxTime: "20:30:00",
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: "08:00",
                endTime: "23:00",
            },
            buttonText: {
                today: "Hoje",
                month: "Mês",
                week: "Semana",
                day: "Dia",
                list: "Lista",
            },
            locale: "pt-br",
            selectable: true,
            editable: false,
            select: function (info) {
                alert("Selecionado de " + info.startStr + " até " + info.endStr);
            },
            eventDidMount: function(info) {
                info.el.style.backgroundColor = info.event.backgroundColor;
                info.el.style.borderColor = info.event.backgroundColor;
                info.el.style.color = 'white';
            },
            events: '/psicologia/agendamentos-calendar',
            eventClick: function(info) {
                const event = info.event;
                const start = event.start;
                const end = event.end;
                const options = { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                const dataHoraStr = start.toLocaleString('pt-BR', options) + " - " + (end ? end.toLocaleString('pt-BR', options) : '');

                document.getElementById('modalPaciente').textContent = event.title;
                document.getElementById('modalDataHora').textContent = dataHoraStr;
                document.getElementById('modalObservacoes').textContent = event.extendedProps.description || 'Nenhuma observação';
                document.getElementById('modalStatusSelect').value = event.extendedProps.status || 'Agendado';
                document.getElementById('modalLocal').textContent = event.extendedProps.local || 'Não informado';
                document.getElementById('modalServico').textContent = event.extendedProps.servico || 'Não informado';

                const modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                document.getElementById('btnSalvarStatus').setAttribute('data-event-id', event.id);
                document.getElementById('btnMensagemCancelamento').setAttribute('data-event-id', event.id);
                modal.show();
            }
        };
    }

    function renderCalendar() {
        const calendarEl = document.getElementById("calendar");

        // Destroi o calendário anterior, se já existir
        if (calendar) {
            calendar.destroy();
        }

        // Cria nova instância com base na largura atual
        const screenWidth = window.innerWidth;
        const options = getCalendarOptions(screenWidth);

        calendar = new FullCalendar.Calendar(calendarEl, options);
        calendar.render();
    }

    // Renderiza na primeira vez
    document.addEventListener('DOMContentLoaded', function () {
        renderCalendar();
    });

    // Recria o calendário ao redimensionar (com debounce)
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            renderCalendar();
        }, 300); // espera 300ms após parar de redimensionar
    });

    // SCRIPT PARA MUDANÇA DE STATUS DE AGENDAMENTO
    document.getElementById('btnSalvarStatus').addEventListener('click', function () {
        const eventId = this.getAttribute('data-event-id');
        const novoStatus = document.getElementById('modalStatusSelect').value;

        if (novoStatus === "Cancelado") {
            bootstrap.Modal.getInstance(document.getElementById('agendamentoModal')).hide();
            const modal = new bootstrap.Modal((document.getElementById('motivoCancelamentoModal')));
            modal.show();
        } else {
            fetch(`/psicologia/agendamentos/${eventId}/status`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: novoStatus })
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao atualizar status.');
                return response.json();
            })
            .then(data => {
                calendar.refetchEvents();
                bootstrap.Modal.getInstance(document.getElementById('agendamentoModal')).hide();
            })
            .catch(error => {
                alert('Erro: ' + error.message);
            });
        }
    });

</script>

<!-- MODAL DE CANCELAMENTO -->
<script>
    document.getElementById("btnMensagemCancelamento").addEventListener('click', function() {

        const content = document.getElementById('text-cancelamento').value;
        const eventId = this.getAttribute('data-event-id');

        if (!content) {
            alert("Insira um motivo para poder continuar")
        } else {
            fetch(`/psicologia/agendamentos/${eventId}/mensagem-cancelamento`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mensagem : content, id : eventId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao atualizar agendamento.');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('text-cancelamento').value = '';
                calendar.refetchEvents();
                bootstrap.Modal.getInstance(document.getElementById('motivoCancelamentoModal')).hide();
            })
            .catch(error => {
                alert('Erro: ' + error.message);
            });
        }


    })
</script>

</html>
