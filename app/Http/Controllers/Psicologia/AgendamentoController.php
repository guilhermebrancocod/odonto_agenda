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
        ->where('ID_CLINICA', 1); // CLÍNICA DE PSICOLOGIA

        // SE REQUEST VEM COM PARAMETRO SEARCH PREENCHIDO
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        // SE O REQUEST VEM COM PARAMETRO DATE PREENCHIDO:
        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d'); // CONVERTE PARA FORMATO Y-m-d COM CARBON
                $query->where('DT_AGEND', $date);
            } catch (\Exception $e) {
                // IGNORA DATA INVÁLIDA
            }
        }

        if ($request->filled('start_time')) {
            $startTime = $request->input('start_time');
            $query->where('HR_AGEND_INI', '>=', $startTime);
        }

        if ($request->filled('end_time')) {
            $endTime = $request->input('end_time');
            $query->where('HR_AGEND_FIN', '<=', $endTime);
        }

        if ($request->filled('status')) {
            $query->where('STATUS_AGEND', $request->input('status'));
        }

        if ($request->filled('service')) {
            $service = $request->input('service');
            $query->whereHas('servico', function($q) use ($service) {
                $q->where('SERVICO_CLINICA_DESC', 'like', "%{$service}%");
            });
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Pega o parâmetro limit (padrão 10)
        $limit = (int) $request->input('limit', 10);

        // Opcional: define limite máximo para evitar consultas muito grandes
        $limit = min($limit, 100);

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
            'hr_fim' => 'required',
            'status_agend' => 'required|string',
            'id_agend_remarcado' => 'nullable|integer',
            'recorrencia' => 'nullable|string|max:64',
            'tem_recorrencia' => 'nullable|in:0,1',
            'duracao_meses_recorrencia' => 'nullable|integer|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'in:0,1,2,3,4,5,6',
            'data_fim_recorrencia' => [
                'nullable',
                'date',
                'after_or_equal:dia_agend',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('dia_agend') && $value) {
                        $dataInicio = Carbon::parse($request->input('dia_agend'));
                        $dataFim = Carbon::parse($value);
                        if ($dataFim->greaterThan($dataInicio->copy()->addYear())) {
                            $fail('A data final da recorrência não pode ultrapassar 1 ano em relação à data de início.');
                        }
                    }
                },
            ],
        ], [
            'paciente_id.required' => 'Selecione um paciente antes de continuar.',
            'id_servico.required' => 'Selecione um serviço antes de continuar.',
            'dia_agend.required' => 'Selecione a data do agendamento.',
            'hr_ini.required' => 'Informe o horário de início.',
            'hr_fim.required' => 'Informe o horário de término.',
            'valor_agend.numeric' => 'Informe um valor válido para o agendamento.',
            'data_fim_recorrencia.after_or_equal' => 'A data final da recorrência deve ser igual ou posterior ao dia do agendamento.',
        ]);

        $valorAgend = $request->valor_agend ?? null;
        $servico = FaesaClinicaServico::find($request->id_servico);

        /**
         * ✅ 1) Caso marcada a recorrência manual
         */
        if ($request->input('tem_recorrencia') === "1") {
            $diasSemana = $request->input('dias_semana', []);
            $dataInicio = Carbon::parse($request->dia_agend);

            // Se selecionou duração em meses, calcula a data final da recorrência
            if ($request->filled('duracao_meses_recorrencia')) {
                $meses = (int) $request->duracao_meses_recorrencia;
                $dataFim = $dataInicio->copy()->addMonths($meses);
            }
            // Se enviou data_fim_recorrencia direta, usa ela
            elseif ($request->filled('data_fim_recorrencia')) {
                $dataFim = Carbon::parse($request->data_fim_recorrencia);
            } else {
                // fallback padrão: 1 mês
                $dataFim = $dataInicio->copy()->addMonth();
            }

            $agendamentosCriados = [];

            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                if (in_array($data->dayOfWeek, $diasSemana)) {
                    $dados = [
                        'ID_CLINICA' => $idClinica,
                        'ID_PACIENTE' => $request->paciente_id,
                        'ID_SERVICO' => $request->id_servico,
                        'DT_AGEND' => $data->format('d-m-Y'),
                        'HR_AGEND_INI' => $request->hr_ini,
                        'HR_AGEND_FIN' => $request->hr_fim,
                        'STATUS_AGEND' => 'Agendado',
                        'RECORRENCIA' => $request->recorrencia,
                        'VALOR_AGEND' => $valorAgend,
                        'OBSERVACOES' => $request->observacoes,
                    ];
                    $agendamento = FaesaClinicaAgendamento::create($dados);
                    $agendamentosCriados[] = $agendamento;
                }
            }

            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Agendamentos recorrentes criados conforme os dias definidos!');
        }

        /**
         * ✅ 2) Se não marcada a recorrência manual, seguem as regras padrão:
         *      - triagem / plantão: 3 agendamentos semanais
         *      - psicodiagnóstico: 6 meses semanais
         *      - psicoterapia / educação: 1 ano semanal
         *      - se o serviço tiver tempo recorrência em meses, usa esse tempo
         */

        $dataInicio = Carbon::parse($request->dia_agend);

        if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['triagem', 'plantão'])) {
            for ($i = 0; $i < 3; $i++) {
                $dataAgendamento = $dataInicio->copy()->addWeeks($i);
                $this->criarAgendamentoSimples($request, $dataAgendamento, $valorAgend, $idClinica);
            }

            return redirect('/psicologia/criar-agendamento/')
                ->with('success', 'Gerados 3 agendamentos, 1 por semana!');
        }
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
        $agendamentos = FaesaClinicaAgendamento::with('paciente')->get();

        $events = $agendamentos->map(function($agendamento) {
            $dateOnly = substr($agendamento->DT_AGEND, 0, 10);
            $horaInicio = substr($agendamento->HR_AGEND_INI, 0, 8);
            $horaFim = substr($agendamento->HR_AGEND_FIN, 0, 8);
            $status = $agendamento->STATUS_AGEND;

            $start = Carbon::parse("{$dateOnly} {$horaInicio}")->toIso8601String();
            $end = Carbon::parse("{$dateOnly} {$horaFim}")->toIso8601String();

            // Define a cor conforme o status
            $cor = match($status) {
                'Agendado' => '#0d6efd',    // azul
                'Presente' => '#28a745',    // verde
                'Cancelado' => '#dc3545',    // vermelho
                default => '#6c757d',        // cinza para outros casos
            };

            return [
                'id' => $agendamento->ID_AGENDAMENTO,
                'title' => $agendamento->paciente ? $agendamento->paciente->NOME_COMPL_PACIENTE : 'Agendamento',
                'start' => $start,
                'end' => $end,
                'status' => $status,
                'description' => $agendamento->OBSERVACOES ?? '',
                'color' => $cor,  // A cor para o evento no FullCalendar
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
        $agendamento->DT_AGEND = $request->input('date');
        $agendamento->HR_AGEND_INI = $request->input('start_time');
        $agendamento->HR_AGEND_FIN = $request->input('end_time');
        $agendamento->STATUS_AGEND = $request->input('status');
        $agendamento->save();

        return redirect()->route('listagem-agendamentos', $agendamento->ID_AGENDAMENTO)
                         ->with('success', 'Agendamento atualizado com sucesso!');
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
}
