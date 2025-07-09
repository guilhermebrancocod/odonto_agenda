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
        $idClinica = session('clinica-selecionada');

        $request->validate([
            'paciente_id' => 'required|integer',
            'id_servico' => 'required|integer',
            'dia_agend' => 'required|date',
            'hr_ini' => 'required',
            'hr_fim' => 'required',
            'tipo_recorrencia' => 'required|string',
        ]);

        $dados = [
            'ID_CLINICA' => $idClinica,
            'ID_PACIENTE' => $request->paciente_id,
            'ID_SERVICO' => $request->id_servico,
            'DT_AGEND' => $request->dia_agend,
            'HR_AGEND_INI' => $request->hr_ini,
            'HR_AGEND_FIN' => $request->hr_fim,
            'STATUS_AGEND' => '1',
            'RECORRENCIA' => $request->tipo_recorrencia,
        ];

        $agendamento = FaesaClinicaAgendamento::create($dados);

        return response()->json([
            'message' => 'Agendamento criado com sucesso',
            'agendamento' => $agendamento,
        ], 201);
    }
}
