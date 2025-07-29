<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;
use App\Models\FaesaClinicaServico;
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

    // CRIAR AGENDAMENTO
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

        // Função para verificar conflito de horário no mesmo local e dia
        $existeConflito = function($dataAgend, $hrIni, $hrFim) use ($idClinica, $request) {
            $dataAgend = Carbon::parse($dataAgend)->format('Y-m-d'); // data no formato correto
            $hrIni = date('H:i:s', strtotime($hrIni));              // hora no formato correto
            $hrFim = date('H:i:s', strtotime($hrFim));

            return FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
                ->where('DT_AGEND', $dataAgend)
                ->where(function ($query) use ($request, $hrIni, $hrFim) {
                    // Conflito de horário
                    $query->whereBetween('HR_AGEND_INI', [$hrIni, $hrFim])
                        ->orWhereBetween('HR_AGEND_FIN', [$hrIni, $hrFim])
                        ->orWhere(function($q2) use ($hrIni, $hrFim) {
                            $q2->where('HR_AGEND_INI', '<=', $hrIni)
                                ->where('HR_AGEND_FIN', '>=', $hrFim);
                        });
                })
                ->where(function ($query) use ($request) {
                    // Ou conflito no local
                    $query->where('LOCAL', $request->local_agend)
                        // Ou conflito no paciente (em qualquer local)
                        ->orWhere('ID_PACIENTE', $request->paciente_id);
                })
                ->exists();
        };

        if ($request->input('tem_recorrencia') === "1") {
            $recorrencia = $request->input('recorrencia');
            $diasSemana = $request->input('dias_semana', []);
            $dataInicio = Carbon::parse($request->dia_agend);

            $dataFim = $duracaoMesesRecorrencia
                ? $dataInicio->copy()->addMonths($duracaoMesRecorrencia)
                : ($request->filled('data_fim_recorrencia')
                    ? Carbon::parse($request->data_fim_recorrencia)
                    : $dataInicio->copy()->addMonths(1));

            $agendamentosCriados = [];
            $diasComConflito = [];

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
                        continue; // pula esse dia, mas continua os demais
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
                    $agendamentosCriados[] = FaesaClinicaAgendamento::create($dados);
                }
            } else {
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
                        $agendamentosCriados[] = FaesaClinicaAgendamento::create($dados);
                    }
                }
            }

            if (!empty($diasComConflito)) {
                $msg = 'Agendamentos criados, exceto para os dias com conflito: ' . implode(', ', $diasComConflito);
                return redirect('/psicologia/criar-agendamento/')
                    ->with('success', $msg);
            }

            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Agendamentos recorrentes criados conforme os dias e duração definidos!');
        }

        // Outras regras para serviços específicos (triagem, plantão, psicodiagnóstico, psicoterapia, etc.)
        // Aplicar a mesma validação de conflito dentro desses loops:

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
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['conflito' => "Conflito detectado no dia $dataFormatada no local {$request->local_agend}."]);
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

            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Agendamentos criados conforme o tipo de serviço.');
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
                ->with('success', 'Atendimentos semanais gerados para 6 meses.');
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
        FaesaClinicaAgendamento::create($dados);

        return redirect('/psicologia/criar-agendamento/')
            ->with('success', 'Agendamento criado com sucesso!');
    }

    public function showAgendamento($id)
    {
        $agendamento = FaesaClinicaAgendamento::with([
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

            $start = Carbon::parse("{$dateOnly} {$horaInicio}")->toIso8601String();
            $end = Carbon::parse("{$dateOnly} {$horaFim}")->toIso8601String();

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

    // RETORNA VIEW DE EDIÇÃO DE AGENDAMENTO
    public function editAgendamento($id)
    {
        $agendamento = FaesaClinicaAgendamento::with('paciente', 'servico')->findOrFail($id);
        return view('psicologia.editar_agendamento', compact('agendamento'));
    }

    // CONTROLLER DE EDIÇÃO DE PACIENTE
    public function updateAgendamento(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'status' => 'required|string',
        ]);

        $agendamento = FaesaClinicaAgendamento::findOrFail($id);

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

        // 🛑 Valida conflito de agendamento
        if ($this->existeConflitoAgendamento($idClinica, $local, $data, $horaIni, $horaFim, $idPaciente, $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: outro agendamento no mesmo horário/local ou para o mesmo paciente.']);
        }

        // 🛑 Valida conflito exclusivo do paciente
        if ($this->existeConflitoPaciente($idClinica, $idPaciente, $data, $horaIni, $horaFim, $id)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Conflito detectado: o paciente já possui agendamento neste horário.']);
        }

        // ✅ Atualiza se não houver conflitos
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

        // Exclui o agendamento
        $agendamento->delete();

        // Redireciona para a lista ou outra rota com mensagem de sucesso
        return redirect()->route('listagem-agendamentos')
                        ->with('success', 'Agendamento excluído com sucesso!');
    }

    private function existeConflitoAgendamento($idClinica, $local, $dataAgend, $hrIni, $hrFim, $idPaciente, $idAgendamentoAtual = null)
    {
        $dataAgend = Carbon::parse($dataAgend)->format('Y-m-d');
        $hrIni = Carbon::parse($hrIni)->format('H:i:s');
        $hrFim = Carbon::parse($hrFim)->format('H:i:s');

        $query = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('DT_AGEND', $dataAgend)
            ->where(function ($q) use ($hrIni, $hrFim) {
                $q->whereBetween('HR_AGEND_INI', [$hrIni, $hrFim])
                    ->orWhereBetween('HR_AGEND_FIN', [$hrIni, $hrFim])
                    ->orWhere(function ($q2) use ($hrIni, $hrFim) {
                        $q2->where('HR_AGEND_INI', '<=', $hrIni)
                            ->where('HR_AGEND_FIN', '>=', $hrFim);
                    });
            })
            ->where(function ($q) use ($local, $idPaciente) {
                $q->where('LOCAL', $local)
                ->orWhere('ID_PACIENTE', $idPaciente); // impede mesmo paciente em outro local
            });

        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }

        return $query->exists();
    }


    private function existeConflitoPaciente($idClinica, $idPaciente, $dataAgend, $hrIni, $hrFim, $idAgendamentoAtual = null)
    {
        $query = FaesaClinicaAgendamento::where('ID_CLINICA', $idClinica)
            ->where('ID_PACIENTE', $idPaciente)
            ->where('DT_AGEND', $dataAgend)
            ->where(function($q) use ($hrIni, $hrFim) {
                $q->whereBetween('HR_AGEND_INI', [$hrIni, $hrFim])
                ->orWhereBetween('HR_AGEND_FIN', [$hrIni, $hrFim])
                ->orWhere(function($q2) use ($hrIni, $hrFim) {
                    $q2->where('HR_AGEND_INI', '<=', $hrIni)
                        ->where('HR_AGEND_FIN', '>=', $hrFim);
                });
            });

        if ($idAgendamentoAtual) {
            $query->where('ID_AGENDAMENTO', '<>', $idAgendamentoAtual);
        }

        return $query->exists();
    }

}