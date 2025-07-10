<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;
use Carbon\Carbon;

class AgendamentoController extends Controller
{
    /**
     * Exibe os dados de agendamento recebidos na requisição.
     * 
     * @param Request $request Dados da Requisição HTTP.
     * @return FaesaClinicaAgendamento Retorna uma instância de FaesaClinicaAgendamento
     */
    public function getAgendamento(Request $request): FaesaClinicaAgendamento
    {
        dd($request->all());
    }

    /**
     * Cria um novo registro de Agendamento no Banco de Dados
     * 
     * @param Request $request Dados da Requisição HTTP.
     * @return \Illuminate\Http\RedirectResponse Redireciona de volta à página anterior com uma mensagem de status.
     * @throws \Illuminate\Validation\ValidationException Se algum dado da requisição falhar na validação. 
     */
    public function criarAgendamento(Request $request)
    {
        $idClinica = session('clinica-selecionada');

        // Pré-processamento para aceitar valores com vírgula
        if ($request->has('valor_agend')) {
            $request->merge([
                'valor_agend' => str_replace(',', '.', $request->valor_agend),
            ]);
        }

        // Validações básicas
        $request->validate([
            'paciente_id' => 'required|integer',
            'id_servico' => 'required|integer',
            'dia_agend' => 'required|date',
            'hr_ini' => 'required',
            'hr_fim' => 'required',
            'status_agend' => 'required|string',
            'id_agend_remarcado' => 'nullable|integer',
            'recorrencia' => 'nullable|string|max:64',
            'tem_recorrencia' => 'required|string',
            'valor_agend' => 'nullable|numeric',
            'observacoes' => 'nullable|string',
            'dias_semana' => 'nullable|array',
            'dias_semana.*' => 'in:0,1,2,3,4,5,6',
            'data_fim_recorrencia' => 'nullable|date|after_or_equal:dia_agend',
        ]);

        $valorAgend = $request->valor_agend ? str_replace(',', '.', $request->valor_agend) : null;

        if($request->has('tem_recorrencia') && $request->tem_recorrencia) {
            $diasSemana = $request->input('dias_semana', []);
            $dataInicio = Carbon::parse($request->dia_agend);
            $dataFim = Carbon::parse($request->data_fim_recorrencia);

            $agendamentosCriados = [];

            for($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                if(in_array($data->dayOfWeek, $diasSemana)) {
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
            // AGENDAMENTO ÚNICO
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

            return response()->json([
                'message' => 'Agendamento Criado com sucesso!',
                'agendamento' => $agendamento,
            ], 201);
        }
    }
}
