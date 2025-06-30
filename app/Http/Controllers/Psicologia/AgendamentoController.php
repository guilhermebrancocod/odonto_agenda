<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;

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
        $validated = $request->validate([
            'ID_CLINICA' => 'required|integer|min:1',
            'ID_PACIENTE' => 'required|integer|min:1',
            'ID_SERVICO_CLINICA' => 'required|integer|min:1',
            'DT_AGEND' => 'required|date',
            'HR_AGEND_INI' => 'required|date_format:H:i',
            'HR_AGEND_FIN' => 'required|date_format:H:i|after:HR_AGEND_INI',
            'STATUS_AGEND' => 'nullable|string|max:50',
            'ID_AGEND_REMARCADO' => 'nullable|integer|min:1|exists:FAESA_CLINICA_AGENDAMENTO,ID_AGENDAMENTO',
            'RECORRENCIA' => 'nullable|boolean',
            'VALOR_AGEND' => 'nullable|numeric|min:0',
            'OBSERVACAO_AGEND' => 'nullable|string|max:255',
        ]);

        $agendamento = FaesaClinicaAgendamento::create($validated);

        return response()->json([
            'message' => 'Agendamento criado com sucesso',
            'agendamento' => $agendamento,
        ], 201);
    }
}
