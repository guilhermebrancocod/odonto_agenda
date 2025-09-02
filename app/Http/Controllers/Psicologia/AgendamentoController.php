<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaHorario;
use App\Http\Controllers\Psicologia\PacienteController;
use App\Models\FaesaClinicaPsicologo;
use App\Models\FaesaClinicaSala;
use App\Services\Psicologia\PacienteService;
use App\Services\Psicologia\AgendamentoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AgendamentoController extends Controller
{

    //  INJEÇÃO DE DEPENDÊNCIA
    private PacienteService $pacienteService;
    private AgendamentoService $agendamentoService;

    public function __construct(PacienteService $pacienteService, AgendamentoService $agendamentoService) 
    {
        $this->pacienteService = $pacienteService;
        $this->agendamentoService = $agendamentoService;
    }

    // GET AGENDAMENTO
    public function getAgendamento(Request $request)
    {
        $agendamentos = $this->agendamentoService->getAgendamento($request);
        return $agendamentos;
    }

    // RETORNA AGENDAMENTOS PARA O CALENDÁRIO
    public function getAgendamentosForCalendar()
    {
        $agendamentos = FaesaClinicaAgendamento::with('paciente', 'servico')
        ->where('ID_CLINICA', 1)
        ->where('STATUS_AGEND', '<>', 'Excluido')
        ->where('STATUS_AGEND', '<>', 'Remarcado')
        ->get();
        
        $events = $agendamentos
        ->map(function($agendamento) {
            $dateOnly = substr($agendamento->DT_AGEND, 0, 10);
            $horaInicio = substr($agendamento->HR_AGEND_INI, 0, 8);
            $horaFim = substr($agendamento->HR_AGEND_FIN, 0, 8);
            $status = $agendamento->STATUS_AGEND;
            $checkPagamento = $agendamento->STATUS_PAG;
            $valorPagamento = $agendamento->VALOR_PAG;

            $start = Carbon::parse("{$dateOnly} {$horaInicio}", 'America/Sao_Paulo')->toIso8601String();
            $end = Carbon::parse("{$dateOnly} {$horaFim}", 'America/Sao_Paulo')->toIso8601String();

            $cor = match($status) {
                'Agendado' => '#0d6efd',
                'Presente' => '#28a745',
                'Cancelado' => '#dc3545',
                default => '#6c757d',
            };

            return [
                'id' => $agendamento->ID_AGENDAMENTO,
                'title' => $agendamento->paciente 
                    ? $agendamento->paciente->NOME_COMPL_PACIENTE 
                    : 'Agendamento',
                'start' => $start,
                'end' => $end,
                'status' => $status,
                'checkPagamento' => $checkPagamento,
                'valorPagamento' => $valorPagamento,
                'servico' => $agendamento->servico->SERVICO_CLINICA_DESC ?? 'Serviço não informado',
                'description' => $agendamento->OBSERVACOES ?? '',
                'color' => $cor,
                'local' => $agendamento->LOCAL ?? 'Não informado',
            ];
        });

        return response()->json($events);
    }

    // RETORNA AGENDAMENTOS PARA O CALENDÁRIO DO PSICÓLOGO EM ESPECÍFICO
    public function getAgendamentosForCalendarPsicologo()
    {        
        $psicologo = session('psicologo');
        $agendamentos = FaesaClinicaAgendamento::with('paciente', 'servico')
        ->where('ID_CLINICA', 1)
        ->where('STATUS_AGEND', '<>', 'Excluido')
        ->where('STATUS_AGEND', '<>', 'Remarcado')
        ->where('ID_PSICOLOGO', $psicologo[1] ?? null)
        ->get();
        
        $events = $agendamentos
        ->map(function($agendamento) {
            $dateOnly = substr($agendamento->DT_AGEND, 0, 10);
            $horaInicio = substr($agendamento->HR_AGEND_INI, 0, 8);
            $horaFim = substr($agendamento->HR_AGEND_FIN, 0, 8);
            $status = $agendamento->STATUS_AGEND;
            $checkPagamento = $agendamento->STATUS_PAG;
            $valorPagamento = $agendamento->VALOR_PAG;

            $start = Carbon::parse("{$dateOnly} {$horaInicio}", 'America/Sao_Paulo')->toIso8601String();
            $end = Carbon::parse("{$dateOnly} {$horaFim}", 'America/Sao_Paulo')->toIso8601String();

            $cor = match($status) {
                'Agendado' => '#0d6efd',
                'Presente' => '#28a745',
                'Cancelado' => '#dc3545',
                default => '#6c757d',
            };

            return [
                'id' => $agendamento->ID_AGENDAMENTO,
                'title' => $agendamento->paciente 
                    ? $agendamento->paciente->NOME_COMPL_PACIENTE 
                    : 'Agendamento',
                'start' => $start,
                'end' => $end,
                'status' => $status,
                'checkPagamento' => $checkPagamento,
                'valorPagamento' => $valorPagamento,
                'servico' => $agendamento->servico->SERVICO_CLINICA_DESC ?? 'Serviço não informado',
                'description' => $agendamento->OBSERVACOES ?? '',
                'color' => $cor,
                'local' => $agendamento->LOCAL ?? 'Não informado',
            ];
        });
        return response()->json($events);
    }

    // RETORNA AGENDAMENTOS POR PACIENTE
    public function getAgendamentosByPaciente($idPaciente)
    {
        $agendamentos = FaesaClinicaAgendamento::with(['servico', 'clinica'])
            ->where('ID_CLINICA', 1)
            ->where('ID_PACIENTE', $idPaciente)
            ->where('STATUS_AGEND', '<>', 'Excluido')
            ->where('STATUS_AGEND', '<>', 'Remarcado')
            ->orderBy('DT_AGEND', 'desc')
            ->get();

        return response()->json($agendamentos);
    }

    // CRIAR AGENDAMENTO - ADM | RECEPÇÃO
   public function criarAgendamento(Request $request)
    {
        // ETAPA 1: VALIDAÇÃO E PREPARAÇÃO INICIAL
        // =================================================================

        $idClinica = 1;

        if ($request->has('valor_agend')) {
            $request->merge([
                'valor_agend' => str_replace(',', '.', $request->valor_agend),
            ]);
        }

        $request->validate([
            'paciente_id' => 'required|integer',
            'id_servico' => 'required|integer',
            'dia_agend' => 'required|date',
            'hr_ini' => 'required',
            'hr_fim' => 'required|after:hr_ini',
            'status_agend' => 'required|string',
            'id_agend_remarcado' => 'nullable|integer',
            'recorrencia' => 'nullable|string|max:64',
            'tem_recorrencia' => 'nullable|string',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'in:0,1,2,3,4,5,6',
            'data_fim_recorrencia' => 'nullable|date|after_or_equal:dia_agend',
            'duracao_meses_recorrencia' => 'nullable|integer|min:1|max:12',
            'local_agend' => 'nullable|string|max:255',
            'id_sala_clinica' => 'nullable|integer|exists:faesa_clinica_sala,ID_SALA_CLINICA',
            'id_psicologo' => 'nullable|integer',
        ], [
            // ... suas mensagens de validação personalizadas ...
            'paciente_id.required' => 'A seleção de paciente é obrigatória.',
            'id_servico.required' => 'A seleção de serviço obrigatória.',
            'dia_agend.required' => 'A data do agendamento é obrigatória.',
            'hr_ini.required' => 'A hora de início é obrigatória.',
            'hr_fim.required' => 'A hora de término é obrigatória.',
            'hr_fim.after' => 'A hora de término deve ser posterior à hora de início.',
            'id_sala_clinica.exists' => 'A sala selecionada não existe.',
        ]);

        $servico = FaesaClinicaServico::find($request->id_servico);
        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;

        if ($this->verificaFeriado($request->dia_agend)) {
            return redirect('/psicologia/criar-agendamento/')->with('error', 'Agendamento não foi criado devido a um feriado');
        }

        // ETAPA 2: GERAR A LISTA DE DATAS A SEREM AGENDADAS
        // =================================================================

        $datasParaAgendar = [];
        $dataInicio = Carbon::parse($request->dia_agend);

        if ($request->input('tem_recorrencia') === "1") {
            // Cenário 1: Recorrência Personalizada (marcada pelo usuário)
            $diasSemana = $request->input('dias_semana', []);
            $duracaoMesesRecorrencia = (int) $request->input('duracao_meses_recorrencia');
            $dataFim = $duracaoMesesRecorrencia
                ? $dataInicio->copy()->addMonths($duracaoMesesRecorrencia)
                : ($request->filled('data_fim_recorrencia')
                    ? Carbon::parse($request->data_fim_recorrencia)
                    : $dataInicio->copy()->addMonths(1));

            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                if (empty($diasSemana) || in_array($data->dayOfWeek, $diasSemana)) {
                    $datasParaAgendar[] = $data->copy();
                    if (empty($diasSemana)) $data->addDays(6); // Se nenhum dia específico foi marcado, pula 1 semana
                }
            }
        } elseif ($servico) {
            // Cenário 2: Recorrência Automática (baseada no tipo de serviço)
            $dataFim = null;
            if (in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['triagem', 'plantão'])) {
                $dataFim = $dataInicio->copy()->addWeeks(2);
            } elseif (strtolower($servico->SERVICO_CLINICA_DESC) === 'psicodiagnóstico') {
                $dataFim = $dataInicio->copy()->addMonths(6);
            } elseif (in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicoterapia', 'educação'])) {
                $dataFim = $dataInicio->copy()->addYear();
            } elseif ($servico->TEMPO_RECORRENCIA_MESES > 0) {
                $dataFim = $dataInicio->copy()->addMonths((int) $servico->TEMPO_RECORRENCIA_MESES);
            }

            if ($dataFim) {
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                    $datasParaAgendar[] = $data->copy();
                }
            }
        }

        // Cenário 3: Agendamento Simples
        if (empty($datasParaAgendar)) {
            $datasParaAgendar[] = $dataInicio;
        }

        // ETAPA 3: PROCESSAR CADA DATA COM A LÓGICA CENTRALIZADA
        // =================================================================

        $agendamentosCriados = 0;
        $diasComErro = [];

        foreach ($datasParaAgendar as $data) {
            $dados = [
                'ID_CLINICA'    => $idClinica,
                'ID_PACIENTE'   => $request->paciente_id,
                'ID_SERVICO'    => $request->id_servico,
                'DT_AGEND'      => $data->format('Y-m-d'),
                'HR_AGEND_INI'  => $request->hr_ini,
                'HR_AGEND_FIN'  => $request->hr_fim,
                'STATUS_AGEND'  => 'Agendado',
                'RECORRENCIA'   => $request->recorrencia,
                'VALOR_AGEND'   => $valorAgend,
                'OBSERVACOES'   => $request->observacoes,
                'ID_SALA'       => $request->id_sala_clinica,
                'LOCAL'         => $request->local_agend,
                'ID_PSICOLOGO'  => $request->id_psicologo,
            ];

            $motivoFalha = $this->validarECriarAgendamentoUnico($dados);

            if ($motivoFalha === null) {
                $agendamentosCriados++;
            } else {
                $diasComErro[$data->format('d/m/Y')] = $motivoFalha;
            }
        }

        // ETAPA 4: RETORNAR A RESPOSTA CORRETA PARA O USUÁRIO
        // =================================================================

        if ($agendamentosCriados > 0) {
            // Se pelo menos um agendamento foi criado, atualiza o status do paciente.
            $this->pacienteService->setEmAtendimento($request->paciente_id);
        }

        if ($agendamentosCriados === 0 && !empty($diasComErro)) {
            // Se NENHUM agendamento foi criado, mostra o erro detalhado do primeiro dia.
            $primeiraDataComErro = key($diasComErro);
            $primeiroMotivoErro = reset($diasComErro);
            return redirect()->back()->withInput()
                ->with('error', "Nenhum agendamento criado. Erro no dia $primeiraDataComErro: $primeiroMotivoErro");
        }

        if (!empty($diasComErro)) {
            // Se ALGUNS foram criados, mostra uma mensagem de sucesso parcial.
            $diasProblematicos = implode(', ', array_keys($diasComErro));
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', "Agendamentos criados, exceto para os dias: $diasProblematicos.");
        }

        // Se TODOS foram criados com sucesso.
        return redirect('/psicologia/criar-agendamento/')
            ->with('success', 'Todos os agendamentos foram criados com sucesso!');
    }

    // CRIAR AGENDAMENTO - PSICOLOGO
    public function criarAgendamentoPsicologo(Request $request)
    {
        // CLINICA FIXO
        $idClinica = 1;
        
        $validated = $request->validate([
            'paciente_id' => 'required|integer',
            'id_servico' => 'required|integer',
            'dia_agend' => 'required|date',
            'hr_ini' => 'required',
            'hr_fim' => 'required|after:hr_ini',
            'status_agend' => 'required|string',
            'observacoes' => 'nullable|string',
            'local_agend' => 'nullable|string|max:255',
            'id_sala_clinica' => 'nullable|integer|exists:faesa_clinica_sala,ID_SALA_CLINICA',
            'id_psicologo' => 'required|integer',
        ], [
            'paciente_id.required' => 'A seleção de paciente é obrigatória.',
            'id_servico.required' => 'A seleção de serviço obrigatória.',
            'dia_agend.required' => 'A data do agendamento é obrigatória.',
            'hr_ini.required' => 'A hora de início é obrigatória.',
            'hr_fim.required' => 'A hora de término é obrigatória.',
            'hr_fim.after' => 'A hora de término deve ser posterior à hora de início.',
            'id_sala_clinica.exists' => 'A sala selecionada não existe.',
            'observacoes.string' => 'As observações devem ser um texto.',
            'status_agend.required' => 'O status do agendamento é obrigatório.',
            'id_psicologo.integer' => 'A identificação do Psicólogo deve ser o número de matrícula',
            'id_psicologo.required' => 'A identificação do Psicólogo é necesária'
        ]);

        // BUSCA QUAL SERVIÇO PSICÓLOGO SELECIONOU
        $servico = FaesaClinicaServico::find($request->id_servico);
        
        // VERIFICA SE A DATA NAO CAI EM FERIADO
        if ($this->verificaFeriado($validated['dia_agend'])) {
            return redirect('/psicologia/criar-agendamento/')
                ->with('error', 'Agendamento não foi criado devido a um feriado');
        }

        // AGENDAMENTO SIMPLES
        if ($this->existeConflitoAgendamento($idClinica, $request->id_sala_clinica, $request->dia_agend, $request->hr_ini, $request->hr_fim, $request->paciente_id, idPsicologo: $request->id_psicologo)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['conflito' => 'Conflito de Agendamento identificado']);
        }

        $dados = [
            'ID_CLINICA' => $idClinica,
            'ID_PACIENTE' => $request->paciente_id,
            'ID_SERVICO' => $request->id_servico,
            'DT_AGEND' => $request->dia_agend,
            'HR_AGEND_INI' => $request->hr_ini,
            'HR_AGEND_FIN' => $request->hr_fim,
            'STATUS_AGEND' => 'Agendado',
            'OBSERVACOES' => $request->observacoes ?? null,
            'ID_SALA_CLINICA' => $request->id_sala_clinica,
            'LOCAL' => $request->local_agend,
            'ID_PSICOLOGO' => $request->id_psicologo,
        ];

        if (!$this->salaEstaAtiva($request->id_sala_clinica)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['sala_indisponivel' => 'Sala não está ativa.']);
        }

        if (!$this->horarioEstaDisponivel($idClinica, $request->dia_agend, $request->hr_ini, $request->hr_fim)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['horario_indisponivel' => 'O horário solicitado não está disponível.']);
        }

        FaesaClinicaAgendamento::create($dados);

        try {
            $this->pacienteService->setEmAtendimento($request->paciente_id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Paciente não encontrado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar o status do paciente.');
        }
        return redirect('/psicologo/criar-agendamento/')
            ->with('success', 'Agendamento criado com sucesso!');
    }

    // MOSTRA AGENDAMENTOS - Utiliza Injeção de Dependência
    public function showAgendamento($id, FaesaClinicaAgendamento $agendamentoModel)
    {
        $agendamento = $agendamentoModel->with([
            'paciente',
            'servico',
            'clinica',
            'agendamentoOriginal',
            'remarcacoes'
        ])->find($id);

        if (!$agendamento) {
            abort(404, 'Agendamento não encontrado');
        }

        return view('psicologia.adm.agendamento_show', compact('agendamento'));
    }

    // RETORNA VIEW DE EDIÇÃO DE AGENDAMENTO - Utiliza Injeção de Dependência
    public function editAgendamento($id, FaesaClinicaAgendamento $agendamentoModel)
    {
        $agendamento = $agendamentoModel->with('paciente', 'servico')->findOrFail($id);
        return view('psicologia.adm.editar_agendamento', compact('agendamento'));
    }

    // CONTROLLER DE EDIÇÃO DE PACIENTE - Utiliza Injeção de Dependência
    public function updateAgendamento(Request $request)
    {
        // CASO TENHA VALOR INFORMADO, ADICIONA | SOBRESCREVE VALOR NA REQUEST
        if ($request->has('valor_agend')) {
            $request->merge([
                'valor_agend' => str_replace(',', '.', $request->valor_agend),
            ]);
        }
        
        // VALIDATED DATA É UM ARRAY
        $validatedData = $request->validate([
            'id_agendamento' => 'required|integer',
            'id_servico' => 'required|integer',
            'id_clinica' => 'required|integer',
            'id_paciente' => 'required|integer',
            'id_agend_remarcado' => 'nullable|integer',
            'local' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|string',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'mensagem' => 'nullable|string',
        ],[
            'id_servico.required' => 'Informe o Serviço do Agendamento antes de prosseguir',
            'end_time.after' => 'O horário final deve ser igual ou posterior ao horário inicial',
        ]
        );

        $agendamento = FaesaClinicaAgendamento::findOrFail($request->input('id_agendamento'));

        $idClinica = $validatedData['id_clinica'];
        $idPaciente = $validatedData['id_paciente'];
        $idServico = $validatedData['id_servico'];
        $idAgendamento = $validatedData['id_agendamento'];
        $local = $validatedData['local'];
        $data = $validatedData['date'];
        $horaIni = $validatedData['start_time'];
        $horaFim = $validatedData['end_time'];
        $status = $validatedData['status'];
        $valor_agend = $validatedData['valor_agend'];
        $observacoes = $validatedData['observacoes'];
        $mensagem = $validatedData['mensagem'];

        // VERIFICA SE A DATA NAO CAI EM FERIADO
        if ($this->verificaFeriado($data)) {
            return redirect('/psicologia/agendamento/'.$idAgendamento.'/editar')
                ->with('error', 'Agendamento não foi criado devido a um feriado');
        }

        //Verifica se a sala está ativa
        if (!$this->salaEstaAtiva($request->id_sala_clinica)) 
        {
            return redirect()->back()
                ->withInput()
                ->withErrors(['sala_indisponivel' => 'Sala não está disponível.']);
        }

        // VALIDA CONFLITO DE AGENDAMENTO
        if ($this->existeConflitoAgendamento($idClinica, $local, $data, $horaIni, $horaFim, $idPaciente, $idAgendamento)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: outro agendamento no mesmo horário/local ou para o mesmo paciente.']);
        }

        // Valida conflito exclusivo do paciente
        if ($this->existeConflitoPaciente($idClinica, $idPaciente, $data, $horaIni, $horaFim, $idAgendamento)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: o paciente já possui agendamento neste horário.']);
        }

        if (!$this->horarioEstaDisponivel($idClinica, $data, $horaIni, $horaFim)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['horario_indisponivel' => 'O horário solicitado não está disponível.']);
        }

        if ($data != $agendamento->DT_AGEND->format('Y-m-d'))
        {
            $agendamento->STATUS_AGEND = "Remarcado";
            $agendamento->save();
            $agendamento = new FaesaClinicaAgendamento();
            $agendamento->ID_AGEND_REMARCADO = $idAgendamento;
        }
        $agendamento->ID_SERVICO = $idServico;
        $agendamento->ID_CLINICA = $idClinica;
        $agendamento->ID_PACIENTE = $idPaciente;
        $agendamento->DT_AGEND = $data;
        $agendamento->HR_AGEND_INI = $horaIni;
        $agendamento->HR_AGEND_FIN = $horaFim;
        $agendamento->VALOR_AGEND = $valor_agend;
        $agendamento->OBSERVACOES = $observacoes;
        $agendamento->STATUS_AGEND = $status;
        $agendamento->MENSAGEM = $mensagem;
        $agendamento->LOCAL = $local;

        $agendamento->save();

        return redirect()->route('listagem-agendamentos', $agendamento->ID_AGENDAMENTO)
            ->with('success', 'Agendamento atualizado com sucesso!');
    }

    // ATUALIZA STATUS DO AGENDAMENTO
    public function atualizarStatus(Request $request, $id)
    {
        $agendamento = FaesaClinicaAgendamento::find($id);

        // CASO NÃO ENCONTRE AGENDAMENTO
        if (!$agendamento) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        // ZERA O VALOR CASO O CHECK SEJA MARCADO COMO NÃO PAGO
        if($request->checkPagamento == 'N') {
            $request->merge(['valorPagamento' => 0.00]);
        }

        $request->validate([
            'status' => 'required|in:Agendado,Presente,Finalizado,Cancelado',
            'checkPagamento' => 'in:S,N',
            'valorPagamento' => 'required_if:checkPagamento,S|numeric',
        ], [
            'valorPagamento.required_if' => 'O campo valor do pagamento é obrigatório quando o pagamento está marcado como realizado.',
            'valorPagamento.numeric' => 'O campo valor do pagamento deve ser um número válido.',
        ]);

        // FORMATA VALOR DO PAGAMENTO CASO O TENHA
        if($request->has('valorPagamento')) {
            $request->merge([
                'valorPagamento' => str_replace(',', '.', $request->valorPagamento),
            ]);
        }

        $agendamento->STATUS_AGEND = $request->status;
        $agendamento->STATUS_PAG = $request->checkPagamento;
        $agendamento->VALOR_PAG = $request->valorPagamento;

        if ($request->status != "Cancelado") {
            $agendamento->MENSAGEM = null;
        }

        $agendamento->save();

        return response()->json(['message' => 'Agendamento atualizado com sucesso']);
    }

    // FUNÇÃO DE EXCLUSÃO DE AGENDAMENTP
    public function deleteAgendamento(Request $request, $id)
    {
        // Busca o agendamento pelo ID ou falha (404)
        $agendamento = FaesaClinicaAgendamento::findOrFail($id);

        // Altera o status para Excluido
        $agendamento->STATUS_AGEND = "Excluido";
        $agendamento->save();

        // Redireciona para a lista ou outra rota com mensagem de sucesso
        return redirect()->route('listagem-agendamentos')
                        ->with('success', 'Agendamento excluído com sucesso!');
    }

    /**
     * Verifica se já existe um agendamento conflitante.
     * Um conflito existe se, no mesmo horário, houver um agendamento para:
     * 1. O mesmo paciente (em qualquer lugar).
     * 2. A mesma sala (com qualquer pessoa).
     * 3. O mesmo psicólogo (com qualquer pessoa).
     *
     * @param int $idClinica
     * @param int|null $idSala
     * @param string $data
     * @param string $horaInicio
     * @param string $horaFim
     * @param int $idPaciente
     * @param int|null $idPsicologo
     * @return bool
     */
    private function existeConflitoAgendamento(int $idClinica, ?int $idSala, string $data, string $horaInicio, string $horaFim, int $idPaciente, ?int $idPsicologo): bool
    {
        // Inicia a consulta com os filtros de data e hora, que são sempre necessários.
        $conflitoQuery = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('DT_AGEND', $data)
            ->where('HR_AGEND_INI', '<', $horaFim)
            ->where('HR_AGEND_FIN', '>', $horaInicio);

        // Agora, agrupa as condições de conflito (paciente, sala, psicólogo)
        $conflitoQuery->where(function ($query) use ($idPaciente, $idSala, $idPsicologo) {
            // Conflito 1: O paciente já está ocupado.
            $query->where('ID_PACIENTE', $idPaciente);

            // Conflito 2: A sala já está ocupada (SÓ SE uma sala for informada)
            $query->when($idSala, function ($q) use ($idSala) {
                return $q->orWhere('ID_SALA', $idSala);
            });

            // Conflito 3: O psicólogo já está ocupado (SÓ SE um psicólogo for informado)
            $query->when($idPsicologo, function ($q) use ($idPsicologo) {
                return $q->orWhere('ID_PSICOLOGO', $idPsicologo);
            });
        });

        return $conflitoQuery->exists();
    }   

    // VERIFICA CONFLITO EXCLUSIVO DO PACIENTE
    private function existeConflitoPaciente($idClinica, $idPaciente, $dataAgend, $hrIni, $hrFim, $idAgendamentoAtual = null)
    {
        // FORMATA VALORES
        $dataAgend = Carbon::parse($dataAgend)->format('Y-m-d');
        $hrIni = Carbon::parse($hrIni)->format('H:i:s');
        $hrFim = Carbon::parse($hrFim)->format('H:i:s');

        $query = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('ID_PACIENTE', $idPaciente)
            ->where('DT_AGEND', $dataAgend)

            // VERIFICA SOBREPOSIÇÃO DE HORÁRIO
            ->where(function($q) use ($hrIni, $hrFim) {
                $q->where('HR_AGEND_INI', '<', $hrFim)
                ->where('HR_AGEND_FIN', '>', $hrIni);
            })
            ->where('STATUS_AGEND', '<>', 'Excluido')
            ->where('STATUS_AGEND', '<>', 'Remarcado');

        // EVITA QUE ACUSE DE ERRO COM O PRÓPRIO AGENDAMENTO
        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }        
        return $query->exists();
    }

    // VERIFICA SE SALA ESTÁ ATIVA
    private function salaEstaAtiva($salaAgendamento)
    {
        if ($salaAgendamento == null) {
            return true;
        }

        // BUSCA TODAS AS SALAS INATIVAS DA CLÍNICA
        $salasInativas = FaesaClinicaSala::where('ATIVO', 'N')->get();

        // Verifica se alguma dessas salas tem a descrição igual à sala do agendamento
        foreach ($salasInativas as $sala) {
            if ($sala->ID_SALA_CLINICA == $salaAgendamento) {
                return false;
            }
        }
        return true;
    }

    // VERIFICA HORÁRIOS DISPONÍVEIS
    private function horarioEstaDisponivel($idClinica, $dataAgendamento, $horaInicio, $horaFim)
    {
        $data = Carbon::parse($dataAgendamento)->format('Y-m-d');
        $horaInicio = Carbon::parse($horaInicio)->format('H:i:s');
        $horaFim = Carbon::parse($horaFim)->format('H:i:s');

        // VERIFICA BLOQUEIOS
        $bloqueios = FaesaClinicaHorario::where('ID_CLINICA', $idClinica)
            ->where('BLOQUEADO', 'S')
            ->whereDate('DATA_HORARIO_INICIAL', '<=', $data)
            ->whereDate('DATA_HORARIO_FINAL', '>=', $data)
            ->whereTime('HR_HORARIO_INICIAL', '<', $horaFim)
            ->whereTime('HR_HORARIO_FINAL', '>', $horaInicio)
            ->exists();

        if ($bloqueios) return false;

        // VERIFICA SE EXISTE ALGUM HORÁRIO PERMITIDO
        $horariosPermitidos = FaesaClinicaHorario::where('ID_CLINICA', $idClinica)
            ->where('BLOQUEADO', 'N')
            ->whereDate('DATA_HORARIO_INICIAL', '<=', $data)
            ->whereDate('DATA_HORARIO_FINAL', '>=', $data)
            ->get();

        // Se não tiver horários permitidos cadastrados, considera disponível
        if ($horariosPermitidos->isEmpty()) return true;

        foreach ($horariosPermitidos as $horario) {
            $inicioPermitido = Carbon::parse($horario->HR_HORARIO_INICIAL)->format('H:i:s');
            $fimPermitido = Carbon::parse($horario->HR_HORARIO_FINAL)->format('H:i:s');

            if ($horaInicio >= $inicioPermitido && $horaFim <= $fimPermitido) {
                return true;
            }
        }
        return false;
    }

    // ADICIONA MENSAGEM DE MOTIVO DE CANCELAMENTO AO AGENDAMENTO
    public function addMensagemCancelamento(Request $request)
    {
        $this->agendamentoService->addMensagemCancelamento($request->id, $request->mensagem);
        return response()->json([
            'success' => true,
            'message' => 'Mensagem de Cancelamento adicionada com sucesso!'
        ]);
    }

    // VERIFICA FERIADO PARA NÃO PERMITIR AGENDA
    private function verificaFeriado($data)
    {   
        $data = Carbon::parse($data)->format('Y-m-d');
        $dataFeriado = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_FERIADO')
            ->where('DATA', '=', $data )
            ->get();

        if ($dataFeriado->isNotEmpty()){
            return true;
        }

        return false;
    }

    /**
     * Valida e cria um único agendamento.
     * Retorna uma string com a razão da falha, ou null em caso de sucesso.
     */
    private function validarECriarAgendamentoUnico(array $dadosAgendamento): ?string
    {
        // 1. Verifica a disponibilidade GERAL do horário primeiro.
        if (!$this->horarioEstaDisponivel($dadosAgendamento['ID_CLINICA'], $dadosAgendamento['DT_AGEND'], $dadosAgendamento['HR_AGEND_INI'], $dadosAgendamento['HR_AGEND_FIN'])) {
            return "Horário indisponível"; // Mensagem específica
        }

        // 2. Verifica se a sala está ativa (se houver sala).
        if (isset($dadosAgendamento['ID_SALA']) && !$this->salaEstaAtiva($dadosAgendamento['ID_SALA'])) {
            return "Sala inativa"; // Mensagem específica
        }

        // 3. Por último, verifica conflitos ESPECÍFICOS com outros agendamentos.
        if ($this->existeConflitoAgendamento(
            $dadosAgendamento['ID_CLINICA'],
            $dadosAgendamento['ID_SALA'],
            $dadosAgendamento['DT_AGEND'],
            $dadosAgendamento['HR_AGEND_INI'],
            $dadosAgendamento['HR_AGEND_FIN'],
            $dadosAgendamento['ID_PACIENTE'],
            $dadosAgendamento['ID_PSICOLOGO']
        )) {
            return "Conflito de agendamento"; // Mensagem específica
        }

        // Se tudo estiver OK, cria o agendamento.
        FaesaClinicaAgendamento::create($dadosAgendamento);

        return null; // Sucesso
    }
}