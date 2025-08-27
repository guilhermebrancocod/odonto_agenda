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
            'paciente_id.required' => 'A seleção de paciente é obrigatória.',
            'id_servico.required' => 'A seleção de serviço obrigatória.',
            'dia_agend.required' => 'A data do agendamento é obrigatória.',
            'data_fim_recorrencia.after' => 'A data final da recorrência deve ser igual ou posterior à data inicial.',
            'hr_ini.required' => 'A hora de início é obrigatória.',
            'hr_fim.required' => 'A hora de término é obrigatória.',
            'hr_fim.after' => 'A hora de término deve ser posterior à hora de início.',
            'id_sala_clinica.exists' => 'A sala selecionada não existe.',
            'recorrencia.max' => 'A recorrência não pode ter mais de 64 caracteres.',
            'valor_agend.numeric' => 'O valor do agendamento deve ser um número.',
            'valor_agend.string' => 'O valor do agendamento deve ser numérico.',
            'observacoes.string' => 'As observações devem ser um texto.',
            'status_agend.required' => 'O status do agendamento é obrigatório.',
            'id_agend_remarcado.integer' => 'A identificação do agendamento remarcado deve ser um número inteiro.',
            'id_psicologo.integer' => 'A identificação do Psicólogo deve ser o número de matrícula',
        ]);

        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;
        $duracaoMesesRecorrencia = (int) $request->input('duracao_meses_recorrencia');
        $servico = FaesaClinicaServico::find($request->id_servico);

        // Tratamento de recorrência customizada
        if ($request->input('tem_recorrencia') === "1") {
            $recorrencia = $request->input('recorrencia');
            $diasSemana = $request->input('dias_semana', []);
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = $duracaoMesesRecorrencia
                ? $dataInicio->copy()->addMonths($duracaoMesesRecorrencia)
                : ($request->filled('data_fim_recorrencia')
                    ? Carbon::parse($request->data_fim_recorrencia)
                    : $dataInicio->copy()->addMonths(1)
                );

            $agendamentosCriados = [];
            $diasComConflito = [];

            if (empty($diasSemana)) {
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                    $dataFormatada = $data->format('Y-m-d');
                    if ($this->existeConflitoAgendamento($idClinica, $request->local_agend ?? null, $dataFormatada, $request->hr_ini, $request->hr_fim, $request->paciente_id)) {
                        $diasComConflito[] = $dataFormatada;
                        continue;
                    }
                    if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                        $diasComConflito[] = $dataFormatada;
                        continue;
                    }
                    $agendamentosCriados[] = FaesaClinicaAgendamento::create([
                        'ID_CLINICA' => $idClinica,
                        'ID_PACIENTE' => $request->paciente_id,
                        'ID_SERVICO' => $request->id_servico,
                        'DT_AGEND' => $dataFormatada,
                        'HR_AGEND_INI' => $request->hr_ini,
                        'HR_AGEND_FIN' => $request->hr_fim,
                        'STATUS_AGEND' => 'Agendado',
                        'RECORRENCIA' => $recorrencia,
                        'VALOR_AGEND' => $valorAgend,
                        'OBSERVACOES' => $request->observacoes,
                        'ID_SALA_CLINICA' => $request->id_sala_clinica ?? null,
                        'LOCAL' => $request->local_agend ?? null,
                        'ID_PSICOLOGO' => $request->id_psicologo ?? null,
                    ]);
                }
            } else {
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                    if (in_array($data->dayOfWeek, $diasSemana)) {
                        $dataFormatada = $data->format('Y-m-d');
                        if ($this->existeConflitoAgendamento($idClinica, $request->local_agend ?? null, $dataFormatada, $request->hr_ini, $request->hr_fim, $request->paciente_id)) {
                            $diasComConflito[] = $dataFormatada;
                            continue;
                        }
                        if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                            $diasComConflito[] = $dataFormatada;
                            continue;
                        }
                        $agendamentosCriados[] = FaesaClinicaAgendamento::create([
                            'ID_CLINICA' => $idClinica,
                            'ID_PACIENTE' => $request->paciente_id,
                            'ID_SERVICO' => $request->id_servico,
                            'DT_AGEND' => $dataFormatada,
                            'HR_AGEND_INI' => $request->hr_ini,
                            'HR_AGEND_FIN' => $request->hr_fim,
                            'STATUS_AGEND' => 'Agendado',
                            'RECORRENCIA' => $recorrencia,
                            'VALOR_AGEND' => $valorAgend,
                            'OBSERVACOES' => $request->observacoes,
                            'ID_SALA_CLINICA' => $request->id_sala_clinica ?? null,
                            'LOCAL' => $request->local_agend ?? null,
                            'ID_PSICOLOGO' => $request->id_psicologo ?? null,
                        ]);
                    }
                }
            }

            if (!empty($diasComConflito) && empty($agendamentosCriados)) {
                return redirect('/psicologia/criar-agendamento/')
                    ->with('error', 'Nenhum agendamento foi criado devido a conflitos em todos os dias selecionados.');
            }
            if (!empty($diasComConflito)) {
                $diasFormatados = array_map(fn($dia) => Carbon::parse($dia)->format('d-m-Y'), $diasComConflito);
                $this->pacienteService->setEmAtendimento($request->paciente_id);
                return redirect('/psicologia/criar-agendamento/')
                    ->with('success', 'Agendamentos criados, exceto para os dias com conflito: ' . implode(', ', $diasFormatados));
            }
            $this->pacienteService->setEmAtendimento($request->paciente_id);
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Agendamentos recorrentes criados conforme os dias e duração definidos!');
        }

        // Para serviços como triagem, plantão, etc.
        $dataInicio = Carbon::parse($request->dia_agend);
        if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['triagem', 'plantão'])) {
            $dataFim = $dataInicio->copy()->addWeeks(2);
        } elseif ($servico && strtolower($servico->SERVICO_CLINICA_DESC) === 'psicodiagnóstico') {
            $dataFim = $dataInicio->copy()->addMonths(6);
        } elseif ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicoterapia', 'educação'])) {
            $dataFim = $dataInicio->copy()->addYear();
        } elseif ($servico && $servico->TEMPO_RECORRENCIA_MESES && $servico->TEMPO_RECORRENCIA_MESES > 0) {
            $dataFim = $dataInicio->copy()->addMonths((int) $servico->TEMPO_RECORRENCIA_MESES);
        }

        if (isset($dataFim)) {
            $agendamentosCriados = [];
            $diasComConflito = [];

            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                $dataFormatada = $data->format('Y-m-d');
                if ($this->existeConflitoAgendamento($idClinica, $request->local_agend, $dataFormatada, $request->hr_ini, $request->hr_fim, $request->paciente_id)) {
                    $diasComConflito[] = $dataFormatada;
                    continue;
                }
                if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                    $diasComConflito[] = $dataFormatada;
                    continue;
                }
                $agendamentosCriados[] = FaesaClinicaAgendamento::create([
                    'ID_CLINICA' => $idClinica,
                    'ID_PACIENTE' => $request->paciente_id,
                    'ID_SERVICO' => $request->id_servico,
                    'DT_AGEND' => $dataFormatada,
                    'HR_AGEND_INI' => $request->hr_ini,
                    'HR_AGEND_FIN' => $request->hr_fim,
                    'STATUS_AGEND' => 'Agendado',
                    'RECORRENCIA' => null,
                    'VALOR_AGEND' => $valorAgend,
                    'OBSERVACOES' => $request->observacoes,
                    'ID_SALA_CLINICA' => $request->id_sala_clinica ?? null,
                    'LOCAL' => $request->local_agend ?? null,
                    'ID_PSICOLOGO' => $request->id_psicologo ?? null,
                ]);
            }

            if (!empty($diasComConflito) && empty($agendamentosCriados)) {
                return redirect('/psicologia/criar-agendamento/')
                    ->with('error', 'Nenhum agendamento foi criado devido a conflitos em todos os dias.');
            }

            if (!empty($agendamentosCriados)) {
                $this->pacienteService->setEmAtendimento($request->paciente_id);
            }

            if (!empty($diasComConflito)) {
                $diasFormatados = array_map(fn($dia) => Carbon::parse($dia)->format('d-m-Y'), $diasComConflito);
                return redirect('/psicologia/criar-agendamento/')
                    ->with('success', 'Agendamentos criados, exceto para os dias com conflito: ' . implode(', ', $diasFormatados));
            }
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Todos os agendamentos foram criados com sucesso.');
        }

        // Agendamento simples
        if ($this->existeConflitoAgendamento($idClinica, $request->local_agend, $request->dia_agend, $request->hr_ini, $request->hr_fim, $request->paciente_id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['conflito' => 'Já existe um agendamento neste horário para o paciente ou no local selecionado.']);
        }

        $dados = [
            'ID_CLINICA' => $idClinica,
            'ID_PACIENTE' => $request->paciente_id,
            'ID_SERVICO' => $request->id_servico,
            'DT_AGEND' => $request->dia_agend,
            'HR_AGEND_INI' => $request->hr_ini,
            'HR_AGEND_FIN' => $request->hr_fim,
            'STATUS_AGEND' => 'Agendado',
            'RECORRENCIA' => null,
            'VALOR_AGEND' => $valorAgend,
            'OBSERVACOES' => $request->observacoes,
            'ID_SALA_CLINICA' => $request->id_sala_clinica ?? null,
            'LOCAL' => $request->local_agend ?? null,
            'ID_PSICOLOGO' => $request->id_psicologo ?? null,
        ];

        if (!$this->salaEstaAtiva($request->local_agend)) {
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

        return redirect('/psicologia/criar-agendamento/')
            ->with('success', 'Agendamento criado com sucesso!');
    }

    // CRIAR AGENDAMENTO - PSICOLOGO
    public function criarAgendamentoPsicologo(Request $request)
    {
        $this->agendamentoService->criarAgendamentoPsicologo($request);
        return response()->json([
            'success' => true,
            // 'message' => ''
        ]);
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
        
        // VALODATED DATA É UM ARRAY
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

        //Verifica se a sala está ativa
        if (!$this->salaEstaAtiva($request->local)) 
        {
            return redirect()->back()
                ->withInput()
                ->withErrors(['sala_indisponivel' => 'Sala não está disponível.']);
        }

        // Valida conflito de agendamento
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

    // VERIFICA CONFLITO DE AGENDAMENTO
    private function existeConflitoAgendamento($idClinica, $local, $dataAgend, $hrIni, $hrFim, $idPaciente, $idAgendamentoAtual = null, $idPsicologo = null)
    {
        $dataAgend = Carbon::parse($dataAgend)->format('Y-m-d');
        $hrIni = Carbon::parse($hrIni)->format('H:i:s');
        $hrFim = Carbon::parse($hrFim)->format('H:i:s');

        $query = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('DT_AGEND', $dataAgend)
            ->where(function ($q) use ($hrIni, $hrFim) {
                $q->where('HR_AGEND_INI', '<', $hrFim)
                ->where('HR_AGEND_FIN', '>', $hrIni);
            })
            ->where(function ($q) use ($local, $idPaciente) {
                $q->where('LOCAL', $local)
                ->orWhere('ID_PACIENTE', $idPaciente);
            })
           ->where(function($q) use ($hrIni, $hrFim, $idPsicologo) {
                if ($idPsicologo) {
                    $q->where('HR_AGEND_INI', '<', $hrFim)
                    ->where('HR_AGEND_FIN', '>', $hrIni)
                    ->where('ID_PSICOLOGO', $idPsicologo);
                }
            })
            ->where('STATUS_AGEND', '<>', 'Excluido')
            ->where('STATUS_AGEND', '<>', 'Remarcado');
            

        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }

        return $query->exists();
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

    private function salaEstaAtiva($salaAgendamento)
    {


        if ($salaAgendamento == null) {
            return true; //Se nenhuma sala for selecionada, não retorna erro
        }

        // Busca todas as salas INATIVAS da clínica
        $salasInativas = FaesaClinicaSala::where('ATIVO', 'N')->get();


        // Verifica se alguma dessas salas tem a descrição igual à sala do agendamento
        foreach ($salasInativas as $sala) {
            if ($sala->DESCRICAO === $salaAgendamento) {
                return false; // A sala está disponível (está inativa e é a sala desejada)
            }
        }

        return true; // Nenhuma sala inativa bate com a descrição
    }
    private function horarioEstaDisponivel($idClinica, $dataAgendamento, $horaInicio, $horaFim)
    {
        $data = Carbon::parse($dataAgendamento)->format('Y-m-d');
        $horaInicio = Carbon::parse($horaInicio)->format('H:i:s');
        $horaFim = Carbon::parse($horaFim)->format('H:i:s');

        // Verifica bloqueios
        $bloqueios = FaesaClinicaHorario::where('ID_CLINICA', $idClinica)
            ->where('BLOQUEADO', 'S')
            ->whereDate('DATA_HORARIO_INICIAL', '<=', $data)
            ->whereDate('DATA_HORARIO_FINAL', '>=', $data)
            ->whereTime('HR_HORARIO_INICIAL', '<', $horaFim)
            ->whereTime('HR_HORARIO_FINAL', '>', $horaInicio)
            ->exists();

        if ($bloqueios) return false;

        // Verifica se existe algum horário permitido
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

        return false; // Nenhum horário permitido contemplou esse intervalo
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
}