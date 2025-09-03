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

    // GET AGENDAMENTO - ADM
    public function getAgendamento(Request $request)
    {
        $agendamentos = $this->agendamentoService->getAgendamento($request);
        return $agendamentos;
    }

    public function getAgendamentosForPsicologo(Request $request)
    {
        $agendamentos = $this->agendamentoService->getAgendamentosForPsicologo($request);
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
        // CLINICA FIXO
        $idClinica = 1;
        // LIMPA DADOS DE PREENCHIMENTO DE VALOR
        if ($request->has('valor_agend')) {
            $request->merge([
                'valor_agend' => str_replace(',', '.', $request->valor_agend),
            ]);
        }
        // CASO TENHA ID_SALA., PREENCHE COLUNA 'LOCAL'
        if($request->has('ID_SALA')) {
            $descricaoSala = FaesaClinicaSala::where('id_sala_clinica', $request->id_sala_clinica)
            ->select('DESCRICAO')
            ->first();
            $request->merge([
                'local_agend' => $descricaoSala->DESCRICAO
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
            'paciente_id.required' => 'A seleção de paciente é obrigatória.',
            'id_servico.required' => 'A seleção de serviço obrigatória.',
            'dia_agend.required' => 'A data do agendamento é obrigatória.',
            'hr_ini.required' => 'A hora de início é obrigatória.',
            'hr_fim.required' => 'A hora de término é obrigatória.',
            'hr_fim.after' => 'A hora de término deve ser posterior à hora de início.',
            'id_sala_clinica.exists' => 'A sala selecionada não existe.',
        ]);

        // FORMATA VALORES
        $servico = FaesaClinicaServico::find($request->id_servico);
        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;

        // VERIFICA FERIADO
        if ($this->verificaFeriado($request->dia_agend)) {
            return redirect('/psicologia/criar-agendamento/')->with('error', 'Agendamento não foi criado devido a um feriado');
        }

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

        if (empty($datasParaAgendar)) {
            $datasParaAgendar[] = $dataInicio;
        }

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

        if ($agendamentosCriados > 0) {
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
    public function showAgendamento($id)
    {
        $agendamento = FaesaClinicaAgendamento::findOrFail($id);

        $lista_psicologos = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->select('ID_PSICOLOGO')
            ->whereNotNull('ID_PSICOLOGO')
            ->distinct()
            ->get();

        return view('psicologia.adm.agendamento_show', compact('agendamento', 'lista_psicologos'));
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
        // Ajusta o valor do agendamento caso venha com vírgula
        if ($request->filled('VALOR_AGEND')) {
            $request->merge([
                'VALOR_AGEND' => str_replace(',', '.', $request->VALOR_AGEND),
            ]);
        }

        // Se veio ID_SALA, pega a descrição
        if ($request->filled('ID_SALA')) {
            $descricaoLocal = FaesaClinicaSala::where('ID_SALA_CLINICA', $request->ID_SALA)
                ->value('DESCRICAO');

            if ($descricaoLocal) {
                $request->merge(['LOCAL' => $descricaoLocal]);
            }
        }

        // Validação
        $validatedData = $request->validate([
            'ID_AGENDAMENTO' => 'required|integer',
            'ID_SERVICO'     => 'required|integer',
            'ID_CLINICA'     => 'required|integer',
            'ID_PACIENTE'    => 'required|integer',
            'ID_PSICOLOGO'   => 'nullable|integer',
            'ID_SALA'        => 'nullable|integer',
            'LOCAL'          => 'nullable|string',
            'DT_AGEND'       => 'required|string',
            'HR_AGEND_INI'   => 'required',
            'HR_AGEND_FIN'   => 'required|after:HR_AGEND_INI',
            'STATUS_AGEND'   => 'required|string',
            'VALOR_AGEND'    => 'nullable|numeric',
            'OBSERVACOES'    => 'nullable|string',
        ], [
            'ID_SERVICO.required' => 'Informe o Serviço do Agendamento antes de prosseguir',
            'HR_AGEND_FIN.after'  => 'O horário final deve ser igual ou posterior ao horário inicial',
        ]);

        // ARMAZENA ID DO AGENDAMENTO ORIGINAL
        $agendamentoOriginal = FaesaClinicaAgendamento::findOrFail($validatedData['ID_AGENDAMENTO']);

        $novaData    = $validatedData['DT_AGEND'];
        $novaHoraIni = $validatedData['HR_AGEND_INI'];

        $houveAlteracaoDeDataHora = $novaData != $agendamentoOriginal->DT_AGEND->format('Y-m-d') ||
                                    $novaHoraIni != \Carbon\Carbon::parse($agendamentoOriginal->HR_AGEND_INI)->format('H:i');

        // VERIFICAÇÃO DE FERIADO
        if ($this->verificaFeriado($novaData)) {
            return back()->withInput()->with('error', 'A data selecionada é um feriado.');
        }

        // VERIFICA SE SALA ESTÁ ATIVA
        if (!$this->salaEstaAtiva($request->ID_SALA)) {
            return back()->withInput()->withErrors(['sala_indisponivel' => 'Sala não está disponível.']);
        }

        if ($this->existeConflitoAgendamento(
            $validatedData['ID_CLINICA'] ?? $agendamentoOriginal->ID_CLINICA,
            $validatedData['ID_SALA'],
            $novaData,
            $novaHoraIni,
            $validatedData['HR_AGEND_FIN'],
            $validatedData['ID_PACIENTE'],
            $validatedData['ID_PSICOLOGO'],
            $agendamentoOriginal->ID_AGENDAMENTO
        )) {
            return back()->withErrors(['conflito' => 'Conflito detectado: outro agendamento no mesmo horário/local ou para o mesmo paciente.']);
        }

        // Se houve alteração de data/hora -> cria novo agendamento (remarcação)
        if ($houveAlteracaoDeDataHora) {
            $agendamentoOriginal->STATUS_AGEND = "Remarcado";
            $agendamentoOriginal->save();

            $novoAgendamento = new FaesaClinicaAgendamento();
            $novoAgendamento->ID_SERVICO   = $validatedData['ID_SERVICO'];
            $novoAgendamento->ID_CLINICA   = $validatedData['ID_CLINICA'] ?? $agendamentoOriginal->ID_CLINICA;
            $novoAgendamento->ID_PACIENTE  = $validatedData['ID_PACIENTE'];
            $novoAgendamento->ID_PSICOLOGO = $validatedData['ID_PSICOLOGO'] ?? null;
            $novoAgendamento->ID_SALA      = $validatedData['ID_SALA'] ?? null;
            $novoAgendamento->DT_AGEND     = $novaData;
            $novoAgendamento->HR_AGEND_INI = $novaHoraIni;
            $novoAgendamento->HR_AGEND_FIN = $validatedData['HR_AGEND_FIN'];
            $novoAgendamento->VALOR_AGEND  = $validatedData['VALOR_AGEND'] ?? null;
            $novoAgendamento->OBSERVACOES  = $validatedData['OBSERVACOES'] ?? null;
            $novoAgendamento->STATUS_AGEND = $validatedData['STATUS_AGEND'];
            $novoAgendamento->LOCAL        = $validatedData['LOCAL'] ?? null;
            $novoAgendamento->ID_AGEND_REMARCADO = $agendamentoOriginal->ID_AGENDAMENTO;

            $novoAgendamento->save();

            return redirect()->route('listagem-agendamentos', ['id' => $novoAgendamento->ID_AGENDAMENTO])
                ->with('success', 'Agendamento remarcado com sucesso! Um novo agendamento foi criado.');
        }

        // Caso contrário -> atualiza o mesmo agendamento
        $agendamentoOriginal->ID_SERVICO   = $validatedData['ID_SERVICO'];
        $agendamentoOriginal->ID_CLINICA   = $validatedData['ID_CLINICA'] ?? $agendamentoOriginal->ID_CLINICA;
        $agendamentoOriginal->ID_PACIENTE  = $validatedData['ID_PACIENTE'];
        $agendamentoOriginal->ID_PSICOLOGO = $validatedData['ID_PSICOLOGO'];
        $agendamentoOriginal->ID_SALA      = $validatedData['ID_SALA'];
        $agendamentoOriginal->VALOR_AGEND  = $validatedData['VALOR_AGEND'] ?? null;
        $agendamentoOriginal->OBSERVACOES  = $validatedData['OBSERVACOES'] ?? null;
        $agendamentoOriginal->STATUS_AGEND = $validatedData['STATUS_AGEND'];
        $agendamentoOriginal->LOCAL        = $validatedData['LOCAL'] ?? null;

        $agendamentoOriginal->save();

        return redirect()->route('listagem-agendamentos', ['id' => $agendamentoOriginal->ID_AGENDAMENTO])
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
     *
     * @param int $idClinica
     * @param int|null $idSala
     * @param string $data
     * @param string $horaInicio
     * @param string $horaFim
     * @param int $idPaciente
     * @param int|null $idPsicologo
     * @param int|null $idAgendamentoParaIgnorar O ID do agendamento atual para ignorar na verificação (essencial para updates)
     * @return bool
     */
    private function existeConflitoAgendamento(
        int $idClinica, 
        ?int $idSala, 
        string $data, 
        string $horaInicio, 
        string $horaFim, 
        int $idPaciente, 
        ?int $idPsicologo, 
        ?int $idAgendamentoParaIgnorar = null
    ): bool
    {
        // Inicia a consulta com os filtros de data e hora
        $conflitoQuery = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('DT_AGEND', $data)
            ->where('HR_AGEND_INI', '<', $horaFim)
            ->where('HR_AGEND_FIN', '>', $horaInicio)
            ->where('STATUS_AGEND', '<>', 'Excluido');

        // EVITA AGENDAMENTO QUE ESTÁ SENDO ATUALIZADO, SE FOR O CASO
        if ($idAgendamentoParaIgnorar) {
            $conflitoQuery->where('ID_AGENDAMENTO', '!=', $idAgendamentoParaIgnorar);
        }

        $conflitoQuery->where(function ($query) use ($idPaciente, $idSala, $idPsicologo) {
            $query->where('ID_PACIENTE', $idPaciente);

            if ($idSala) {
                $query->orWhere('ID_SALA', $idSala);
            }
            
            if ($idPsicologo) {
                $query->orWhere('ID_PSICOLOGO', $idPsicologo);
            }
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