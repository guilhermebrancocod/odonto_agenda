<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaHorario;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    // GET AGENDAMENTO
    public function getAgendamento(Request $request)
    {
        $query = FaesaClinicaAgendamento::with([
            'paciente',
            'servico',
            'clinica',
            'agendamentoOriginal',
            'remarcacoes'
        ])
        ->where('ID_CLINICA', 1);

        // Filtro por nome ou CPF do paciente
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        // Filtro por data
        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('DT_AGEND', $date);
            } catch (\Exception $e) {
                // Data inválida - ignora filtro
            }
        }

        // Filtro por hora de início
        if ($request->filled('start_time')) {
            try {
                $startTime = Carbon::createFromFormat('H:i', $request->input('start_time'))->format('H:i:s');
                $query->where('HR_AGEND_INI', '>=', $startTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // Filtro por hora de fim
        if ($request->filled('end_time')) {
            try {
                $endTime = Carbon::createFromFormat('H:i', $request->input('end_time'))->format('H:i:s');
                $query->where('HR_AGEND_FIN', '<=', $endTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('STATUS_AGEND', $request->input('status'));
        }

        // Filtro por serviço
        if ($request->filled('service')) {
            $service = $request->input('service');
            $query->whereHas('servico', function($q) use ($service) {
                $q->where('SERVICO_CLINICA_DESC', 'like', "%{$service}%");
            });
        }

        // **FILTRO POR LOCAL**
        if ($request->filled('local')) {
            $local = $request->input('local');
            $query->where('LOCAL', 'like', "%{$local}%");
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Limita o número de registros retornados
        $limit = min((int) $request->input('limit', 10), 100);

        $agendamentos = $query->limit($limit)->get();

        return response()->json($agendamentos);
    }

    // RETORNA AGENDAMENTOS PARA O CALENDÁRIO
    public function getAgendamentosForCalendar()
    {
        $agendamentos = FaesaClinicaAgendamento::with('paciente', 'servico')
            ->where('ID_CLINICA', 1)
            ->get();

        $events = $agendamentos->map(function($agendamento) {
            $dateOnly = substr($agendamento->DT_AGEND, 0, 10);
            $horaInicio = substr($agendamento->HR_AGEND_INI, 0, 8);
            $horaFim = substr($agendamento->HR_AGEND_FIN, 0, 8);
            $status = $agendamento->STATUS_AGEND;

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
            ->orderBy('DT_AGEND', 'desc')
            ->get();

        return response()->json($agendamentos);
    }

    // CRIAR AGENDAMENTO
    public function criarAgendamento(Request $request)
    {
        // ID FIXO POR SER CONTROLLER DA CLÍNICA DE PSICOLOGIA
        $idClinica = 1;

        // CASO TENHA VALOR INFORMADO, ADICIONA | SOBRESCREVE VALOR NA REQUEST
        if ($request->has('valor_agend')) {
            $request->merge([
                'valor_agend' => str_replace(',', '.', $request->valor_agend),
            ]);
        }

        // VALIDAÇÃO DOS DADOS
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
        ], [
            'paciente_id.required' => 'A seleção de paciente é obrigatória.',
            'id_servico.required' => 'A seleção de serviço obrigatória.',
            'dia_agend.required' => 'A data do agendamento é obrigatória.',
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
        ]);

        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;
        $duracaoMesesRecorrencia = (int) $request->input('duracao_meses_recorrencia');
        $servico = FaesaClinicaServico::find($request->id_servico);

        // VERIFICA SE TEM RECORRÊNCIA
        if ($request->input('tem_recorrencia') === "1") {
            $recorrencia = $request->input('recorrencia');
            $diasSemana = $request->input('dias_semana', []); // Array com os valores dos dias da semana
            $dataInicio = Carbon::parse($request->dia_agend);

            // VERIFICA SE USUÁRIO INFORMOU A DURAÇÃO COM DATA FINAL OU SE COLOCOU OS MESES PARA DURAÇÃO DA RECORRÊNCIA
            $dataFim = $duracaoMesesRecorrencia
            ? $dataInicio->copy()->addMonths($duracaoMesesRecorrencia) // SOMA A QUANTIDADE DE MESES À DATA DE INÍCIO
            : ($request->filled('data_fim_recorrencia')
            ? Carbon::parse($request->data_fim_recorrencia) // USA DIRETAMENTE A DATA FINAL FORNECIDA PELO USUÁRIO
            : $dataInicio->copy()->addMonths(1)); // SE NÃO INFORMOU AMBOS, USA POR PADRÃO 1 MES

            $agendamentosCriados = [];
            $diasComConflito = [];

            // SE NENHUM DIA DA SEMANA FOR ESPECIFICADO
            if (empty($diasSemana)) {
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                    $dataFormatada = $data->format('Y-m-d');
                    if ($this->existeConflitoAgendamento(
                        $idClinica,
                        $request->local_agend ?? null,
                        $dataFormatada,
                        $request->hr_ini,
                        $request->hr_fim,
                        $request->paciente_id
                    )) {
                        $diasComConflito[] = $dataFormatada;
                        continue;
                    }
                    $dados = [
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
                    ];

                    // VERIFICA HORÁRIOS DISPONÍVEIS
                    if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                        $diasComConflito[] = $dataFormatada;
                        continue;
                    }

                    $agendamentosCriados[] = FaesaClinicaAgendamento::create($dados);
                }
            } else { // SE ESPECIFICOU DIAS DA SEMANA PARA RECORRÊNCIA
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                    if (in_array($data->dayOfWeek, $diasSemana)) {                        
                        $dataFormatada = $data->format('Y-m-d');
                        if ($this->existeConflitoAgendamento(
                            $idClinica,
                            $request->local_agend ?? null,
                            $dataFormatada,
                            $request->hr_ini,
                            $request->hr_fim,
                            $request->paciente_id
                        )) {
                            $diasComConflito[] = $dataFormatada;
                            continue;
                        }
                        $dados = [
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
                        ];

                        // VERIFICA HORÁRIOS DISPONÍVEIS
                        if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                            $diasComConflito[] = $dataFormatada;
                            continue;
                        }

                        $agendamentosCriados[] = FaesaClinicaAgendamento::create($dados);
                    }
                }
            }

            // SE ELE ACHA DIAS COM CONFLITO, GERA PARA TODOS OS DIAS SEM CONFLITO MENOS PARA O QUE TEM CONFLITO
            if (!empty($diasComConflito)) {
                if($dataFormatada == $diasComConflito[0]) {
                    return redirect('/psicologia/criar-agendamento/')
                    ->with('error', 'Agendamento não pode ser criado pois há conflito: ' . $dataFormatada);
                }

                // Formata todos os dias com conflito para 'd-m-Y'
                $diasFormatados = array_map(function ($dia) {
                    return \Carbon\Carbon::parse($dia)->format('d-m-Y');
                }, $diasComConflito);

                return redirect('/psicologia/criar-agendamento/')
                    ->with('success', 'Agendamentos criados, exceto para os dias com conflito: ' . implode(', ', $diasFormatados));
            }
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Agendamentos recorrentes criados conforme os dias e duração definidos!');
        }

        // REGRAS PARA SERVIÇOS ESPECÍFICOS COMO TRIAGEM, PLATNÃO, E OS DEMAIS...
        // APLICA MESMA VALIDAÇÃO DE CONFLITO DENTRO DOS LOOPS

        $dataInicio = Carbon::parse($request->dia_agend); // TRANSFORMA NUM OBJETO CARBON

        // TRIAGEM - 3 AGENDAMENTOS | 1 POR SEMANA | APENAS DEFINE A DATA FINAL
        if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['triagem', 'plantão'])) {
            $dataFim = $dataInicio->copy()->addWeeks(2); // adiciona duas semana à data final

        // PSIODIAGNÓSTICO - AGENDAMENTOS SEMANAIS DURANTE 6 MESES | APENAS DEFINE A DATA FINAL
        } elseif ($servico && strtolower($servico->SERVICO_CLINICA_DESC) === 'psicodiagnóstico') {
            $dataFim = $dataInicio->copy()->addMonths(6);

        // PSICOTERAPIA OU EDUCAÇÃO - ATENDIMENTOS SEMANAIS DURANTE 1 ANO | APENAS DEFINE A DATA FINAL
        } elseif ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicoterapia', 'educação'])) {
            $dataFim = $dataInicio->copy()->addYear();

        // CASO NÃO SEJA O TEMPO DE RECORRÊNCIA DOS SERVIÇOS PADRÕES, UTILIZA A PROPRIEDADE DOS SERVIÇOS DE TEMPO DE RECORRÊNCIA EM MESES | APENAS DEFINE A DATA FINAL
        } elseif ($servico && $servico->TEMPO_RECORRENCIA_MESES && $servico->TEMPO_RECORRENCIA_MESES > 0) {
            $dataFim = $dataInicio->copy()->addMonths((int) $servico->TEMPO_RECORRENCIA_MESES);
        }

        //VERIFICA SE A VARIAVEL DE DATA FINAL FOI DEIFNIDA OU SE É NULL
        if (isset($dataFim)) {
            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                $dataFormatada = $data->format('Y-m-d');
                if ($this->existeConflitoAgendamento(
                        $idClinica,
                        $request->local_agend,
                        $dataFormatada,
                        $request->hr_ini,
                        $request->hr_fim,
                        $request->paciente_id
                    )) {
                    // return redirect()->back()
                    //     ->withInput()
                    //     ->withErrors(['conflito' => "Conflito detectado no dia $dataFormatada no local {$request->local_agend}."]);
                    $diasComConflito[] = $dataFormatada;
                    continue;
                }

                // VERIFICA HORÁRIOS DISPONÍVEIS
                if (!$this->horarioEstaDisponivel($idClinica, $dataFormatada, $request->hr_ini, $request->hr_fim)) {
                    $diasComConflito[] = $dataFormatada;
                    continue;
                }

                FaesaClinicaAgendamento::create([
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
                ]);
            }
            //  SE POSSUI CONFLITO O LONGO DOS DIAS DO AGENDAMENTO
            if (!empty($diasComConflito)) {

                // Formata todos os dias com conflito para 'd-m-Y'
                $diasFormatados = array_map(function ($dia) {
                    return \Carbon\Carbon::parse($dia)->format('d-m-Y');
                }, $diasComConflito);

                return redirect('/psicologia/criar-agendamento/')
                    ->with('success', 'Agendamentos criados, exceto para os dias com conflito: ' . implode(', ', $diasFormatados));
            }
            return redirect('/psicologia/criar-agendamento/')->with('success', 'Todos os agendamentos foram criados com sucesso.');
        }

        if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['triagem', 'plantão'])) {
            $dataInicio = Carbon::parse($request->dia_agend);
            $ret = $handleAgendamentoComConflito($dataInicio, $dataInicio->copy()->addWeeks(2));
            if ($ret) return $ret;
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Gerados 3 agendamentos, 1 por semana!');
        }

        if ($servico && strtolower($servico->SERVICO_CLINICA_DESC) === 'psicodiagnóstico') {
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = $dataInicio->copy()->addMonths(6);
            $ret = $handleAgendamentoComConflito($dataInicio, $dataFim);
            if ($ret) return $ret;
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Atendimentos semanais gerados por 6 meses.');
        }

        if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicoterapia', 'educação'])) {
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = $dataInicio->copy()->addYear();
            $ret = $handleAgendamentoComConflito($dataInicio, $dataFim);
            if ($ret) return $ret;
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Atendimentos semanais gerados para 1 ano.');
        }

        if ($servico && $servico->TEMPO_RECORRENCIA_MESES && $servico->TEMPO_RECORRENCIA_MESES > 0) {
            $meses = (int) $servico->TEMPO_RECORRENCIA_MESES;
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = $dataInicio->copy()->addMonths($meses);
            $ret = $handleAgendamentoComConflito($dataInicio, $dataFim);
            if ($ret) return $ret;
            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Atendimentos semanais gerados conforme recorrência padrão do serviço.');
        }

        // Agendamento simples - verificar conflito antes de criar
        if ($this->existeConflitoAgendamento(
            $idClinica,
            $request->local_agend,
            $request->dia_agend,
            $request->hr_ini,
            $request->hr_fim,
            $request->paciente_id
        )) {
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
        ];

        // VERIFICA HORÁRIOS DISPONÍVEIS
        if (!$this->horarioEstaDisponivel($idClinica, $request->dia_agend, $request->hr_ini, $request->hr_fim)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['horario_indisponivel' => 'O horário solicitado não está disponível.']);
        }

        FaesaClinicaAgendamento::create($dados);

        return redirect('/psicologia/criar-agendamento/')
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

        return view('psicologia.agendamento_show', compact('agendamento'));
    }

    // RETORNA VIEW DE EDIÇÃO DE AGENDAMENTO - Utiliza Injeção de Dependência
    public function editAgendamento($id, FaesaClinicaAgendamento $agendamentoModel)
    {
        $agendamento = $agendamentoModel->with('paciente', 'servico')->findOrFail($id);
        return view('psicologia.editar_agendamento', compact('agendamento'));
    }

    // CONTROLLER DE EDIÇÃO DE PACIENTE - Utiliza Injeção de Dependência
    public function updateAgendamento(Request $request, FaesaClinicaModel $agendamentoModel)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required|string',
        ]);

        $agendamento = agendamentoModel->findOrFail($id);

        $clinica = $agendamento->clinica;
        $data = $request->input('date');
        $horaIni = $request->input('start_time');
        $horaFim = $request->input('end_time');
        $local = $request->input('local', $agendamento->LOCAL);
        $idSalaClinica = $request->input('id_sala_clinica', $agendamento->ID_SALA_CLINICA);
        $status = $request->input('status');
        $idClinica = $agendamento->ID_CLINICA;
        $local = $agendamento->LOCAL;
        $idPaciente = $agendamento->ID_PACIENTE;

        // Valida conflito de agendamento
        if ($this->existeConflitoAgendamento($idClinica, $local, $data, $horaIni, $horaFim, $idPaciente, $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: outro agendamento no mesmo horário/local ou para o mesmo paciente.']);
        }

        // Valida conflito exclusivo do paciente
        if ($this->existeConflitoPaciente($idClinica, $idPaciente, $data, $horaIni, $horaFim, $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: o paciente já possui agendamento neste horário.']);
        }

        // Atualiza se não houver conflitos
        $agendamento->DT_AGEND = $data;
        $agendamento->HR_AGEND_INI = $horaIni;
        $agendamento->HR_AGEND_FIN = $horaFim;
        $agendamento->STATUS_AGEND = $status;
        $agendamento->LOCAL = $local;
        $agendamento->save();

        return redirect()->route('listagem-agendamentos', $agendamento->ID_AGENDAMENTO)
            ->with('success', 'Agendamento atualizado com sucesso!');
    }

    // ATUALIZA STATUS DO AGENDAMENTO
    public function atualizarStatus(Request $request, $id)
    {
        $agendamento = FaesaClinicaAgendamento::find($id);

        if (!$agendamento) {
            return response()->json(['message' => 'Agendamento não encontrado'], 404);
        }

        $request->validate([
            'status' => 'required|in:Agendado,Presente,Cancelado',
        ]);

        $agendamento->STATUS_AGEND = $request->status;
        $agendamento->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
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
    private function existeConflitoAgendamento($idClinica, $local, $dataAgend, $hrIni, $hrFim, $idPaciente, $idAgendamentoAtual = null)
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
            });

        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }

        return $query->exists();
    }

    // VERIFICA CONFLITO EXCLUSIVO DO PACIENTE
    private function existeConflitoPaciente($idClinica, $idPaciente, $dataAgend, $hrIni, $hrFim, $idAgendamentoAtual = null, FaesaClinicaAgendamento $agendamentoModel)
    {
        // FORMATA VALORES
        $dataAgend = Carbon::parse($dataAgend)->format('Y-m-d');
        $hrIni = Carbon::parse($hrIni)->format('H:i:s');
        $hrFim = Carbon::parse($hrFim)->format('H:i:s');

        $query = $agendamentoModel->where('ID_CLINICA', $idClinica)
            ->where('ID_PACIENTE', $idPaciente)
            ->where('DT_AGEND', $dataAgend)

            // VERIFICA SOBREPOSIÇÃO DE HORÁRIO
            ->where(function($q) use ($hrIni, $hrFim) {
                $q->where('HR_AGEND_INI', '<', $hrFim)
                ->where('HR_AGEND_FIN', '>', $hrIni);
            });

        // EVITA QUE ACUSE DE ERRO COM O PRÓPRIO AGENDAMENTO
        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }        
        return $query->exists();
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
}