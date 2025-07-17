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
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        if ($request->filled('paciente_id')) {
            $query->where('ID_PACIENTE', $request->input('paciente_id'));
        }

        if ($request->filled('dt_agend')) {
            $query->whereDate('DT_AGEND', Carbon::parse($request->input('dt_agend'))->format('Y-m-d'));
        }

        if ($request->filled('status_agend')) {
            $query->where('STATUS_AGEND', $request->input('status_agend'));
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Se não enviou nenhum filtro (nenhum parâmetro), limitar a 5 registros
        if (!$request->filled('search') &&
            !$request->filled('paciente_id') &&
            !$request->filled('dt_agend') &&
            !$request->filled('status_agend')) {
            $agendamentos = $query->limit(5)->get();
        } else {
            $agendamentos = $query->get();
        }

        return response()->json($agendamentos);
    }

    // CRIAR AGENDAMENTO
    public function criarAgendamento(Request $request)
    {
        // ID DA CLINICA DE PSICOLOGIA
        $idClinica = 1;

        // TROCA VÍRGULA DO VALOR POR PONTO PARA INSERIR NO BANCO
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
            'hr_fim' => 'required',
            'status_agend' => 'required|string',
            'id_agend_remarcado' => 'nullable|integer',
            'recorrencia' => 'nullable|string|max:64',
            'tem_recorrencia' => 'nullable|string',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'in:0,1,2,3,4,5,6',
            'data_fim_recorrencia' => 'nullable|date|after_or_equal:dia_agend',
        ], [
            'paciente_id.required' => 'Selecione um paciente antes de continuar.',
            'id_servico.required' => 'Selecione um serviço antes de continuar.',
            'dia_agend.required' => 'Selecione a data do agendamento.',
            'hr_ini.required' => 'Informe o horário de início.',
            'hr_fim.required' => 'Informe o horário de término.',
            'valor_agend.numeric' => 'Informe um valor válido para o agendamento.',
            'data_fim_recorrencia.after_or_equal' => 'A data final da recorrência deve ser igual ou posterior ao dia do agendamento.'
        ]);

        // TROCA VÍRGULA DO VALOR POR PONTO PARA INSERIR NO BANCO
        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;

        // VERIFICA SE O SERVICO É TRIAGEM OU PLANTAO
        $servico = FaesaClinicaServico::find($request->id_servico);
        if($servico && (strtolower($servico->SERVICO_CLINICA_DESC) === 'triagem' || strtolower($servico->SERVICO_CLINICA_DESC) === 'plantão')) {
            // Gerar 3 agendamentos, 1 por semana no mesmo dia da semana
            $dataInicio = Carbon::parse($request->dia_agend);
            $agendamentosCriados = [];
            for ($i = 0; $i < 3; $i++) {
                $dataAgendamento = $dataInicio->copy()->addWeeks($i);
                $dados = [
                    'ID_CLINICA' => $idClinica,
                    'ID_PACIENTE' => $request->paciente_id,
                    'ID_SERVICO' => $request->id_servico,
                    'DT_AGEND' => $dataAgendamento->format('d-m-Y'),
                    'HR_AGEND_INI' => $request->hr_ini,
                    'HR_AGEND_FIN' => $request->hr_fim,
                    'STATUS_AGEND' => 'Em aberto',
                    'RECORRENCIA' => $request->recorrencia ?? null,
                    'VALOR_AGEND' => $valorAgend,
                    'OBSERVACOES' => $request->observacoes,
                ];
                $agendamento = FaesaClinicaAgendamento::create($dados);
                $agendamentosCriados[] = $agendamento;
            }
            return redirect('/psicologia/criar-agendamento/')->with('success', 'Gerados 3 agendamentos, 1 por semana!');
        } else if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicodiagnóstico'])) {
            // Gerar agendamentos semanais no mesmo dia e horário durante 6 meses
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = $dataInicio->copy()->addMonths(6);
            $agendamentosCriados = [];

            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                $dados = [
                    'ID_CLINICA' => $idClinica,
                    'ID_PACIENTE' => $request->paciente_id,
                    'ID_SERVICO' => $request->id_servico,
                    'DT_AGEND' => $data->format('d-m-Y'),
                    'HR_AGEND_INI' => $request->hr_ini,
                    'HR_AGEND_FIN' => $request->hr_fim,
                    'STATUS_AGEND' => 'Em aberto',
                    'RECORRENCIA' => $request->recorrencia ?? null,
                    'VALOR_AGEND' => $valorAgend,
                    'OBSERVACOES' => $request->observacoes,
                ];
                $agendamento = FaesaClinicaAgendamento::create($dados);
                $agendamentosCriados[] = $agendamento;
            }
            return redirect('/psicologia/criar-agendamento/')->with('success', 'Atendimentos semanais gerados para 6 meses no mesmo dia e horário.');
        } else if ($servico && in_array(strtolower($servico->SERVICO_CLINICA_DESC), ['psicoterapia', 'educação'])) {
            // Gerar agendamentos semanais no mesmo dia e horário durante 1 ano
                $dataInicio = Carbon::parse($request->dia_agend);
                $dataFim = $dataInicio->copy()->addYear(); // +1 ano
                $agendamentosCriados = [];
                for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                    $dados = [
                        'ID_CLINICA' => $idClinica,
                        'ID_PACIENTE' => $request->paciente_id,
                        'ID_SERVICO' => $request->id_servico,
                        'DT_AGEND' => $data->format('d-m-Y'),
                        'HR_AGEND_INI' => $request->hr_ini,
                        'HR_AGEND_FIN' => $request->hr_fim,
                        'STATUS_AGEND' => 'Em aberto',
                        'RECORRENCIA' => $request->recorrencia ?? null,
                        'VALOR_AGEND' => $valorAgend,
                        'OBSERVACOES' => $request->observacoes,
                    ];
                    $agendamento = FaesaClinicaAgendamento::create($dados);
                    $agendamentosCriados[] = $agendamento;
                }
            return redirect('/psicologia/criar-agendamento/')
            ->with('success', 'Atendimentos semanais gerados para 1 ano no mesmo dia e horário.');
        }

        // CASO TENHA RECORRÊNCIA
        if ($request->input('tem_recorrencia') === "1") {
            $diasSemana = $request->input('dias_semana', []);
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = Carbon::parse($request->data_fim_recorrencia);

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
                        'STATUS_AGEND' => 'Em aberto',
                        'RECORRENCIA' => $request->recorrencia,
                        'VALOR_AGEND' => $valorAgend,
                        'OBSERVACOES' => $request->observacoes,
                    ];
                    $agendamento = FaesaClinicaAgendamento::create($dados);
                    $agendamentosCriados[] = $agendamento;
                }
            }
        } else {
            $dados = [
                'ID_CLINICA' => $idClinica,
                'ID_PACIENTE' => $request->paciente_id,
                'ID_SERVICO' => $request->id_servico,
                'DT_AGEND' => $request->dia_agend,
                'HR_AGEND_INI' => $request->hr_ini,
                'HR_AGEND_FIN' => $request->hr_fim,
                'STATUS_AGEND' => 'Em aberto',
                'RECORRENCIA' => null,
                'VALOR_AGEND' => $valorAgend,
                'OBSERVACOES' => $request->observacoes,
            ];
            $agendamento = FaesaClinicaAgendamento::create($dados);
        }
        return redirect('/psicologia/criar-agendamento/')->with('success', 'Agendamento criado com sucesso!');
    }

    public function show($id)
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

            $start = Carbon::parse("{$dateOnly} {$horaInicio}")->toIso8601String();
            $end = Carbon::parse("{$dateOnly} {$horaFim}")->toIso8601String();

            return [
                'id' => $agendamento->ID_AGENDAMENTO,
                'title' => $agendamento->paciente ? $agendamento->paciente->NOME_COMPL_PACIENTE : 'Agendamento',
                'start' => $start,
                'end' => $end,
                'color' => '#007bff',
                'description' => $agendamento->OBSERVACOES ?? '',
            ];
        });

        return response()->json($events);
    }

}
