<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    /**
     * Consulta agendamentos no banco, retornando filtrado ou todos se sem filtro.
     * Retorna em JSON para uso em tabelas dinâmicas ou APIs internas.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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



    /**
     * Cria um novo registro de Agendamento no Banco de Dados
     *
     * @param Request $request Dados da Requisição HTTP.
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function criarAgendamento(Request $request)
    {
        // PSICOLOGIA
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
            'tem_recorrencia' => 'nullable|string',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'in:0,1,2,3,4,5,6',
            'data_fim_recorrencia' => 'nullable|date|after_or_equal:dia_agend',
        ]);

        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;

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
                        'DT_AGEND' => $data->format('Y-m-d'),
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
            $dateOnly = substr($agendamento->DT_AGEND, 0, 10); // Pega só "YYYY-MM-DD"
            $horaInicio = substr($agendamento->HR_AGEND_INI, 0, 8); // Pega só "HH:mm:ss"
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
