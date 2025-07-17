<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaPaciente;
use Illuminate\Database\Eloquent\Collection;

class PacienteController extends Controller
{
    /**
     * Cria um novo registro de Paciente no Banco de Dados
     * 
     * @param Request @request Dados da Requisição HTTP
     * @return \Illuminate\Http\RedirectResponse Redireciona de volta à página anterior com uma mensagem de status.
     * @throws \Illuminate\Validation\ValidationException Se algum dado da requisição falhar na validação.
     */
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
        'servico' => 'required|string',
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

    $servicoDescricao = strtolower(trim($request->input('servico', '')));

    if (in_array($servicoDescricao, ['triagem', 'plantão', 'plantao'])) {
        // Criação automática de 3 agendamentos semanais
        $dataInicio = Carbon::parse($request->dia_agend);
        $uuidRecorrencia = Str::uuid()->toString();

        for ($i = 0; $i < 3; $i++) {
            $dataAgendamento = $dataInicio->addWeeksNoOverflow($i)->copy();

            $dados = [
                'ID_CLINICA' => $idClinica,
                'ID_PACIENTE' => $request->paciente_id,
                'ID_SERVICO' => $request->id_servico,
                'DT_AGEND' => $dataAgendamento->format('Y-m-d'),
                'HR_AGEND_INI' => $request->hr_ini,
                'HR_AGEND_FIN' => $request->hr_fim,
                'STATUS_AGEND' => 'Em aberto',
                'RECORRENCIA' => $uuidRecorrencia,
                'VALOR_AGEND' => $valorAgend,
                'OBSERVACOES' => $request->observacoes,
            ];

            FaesaClinicaAgendamento::create($dados);
        }
    }
    elseif ($request->input('tem_recorrencia') === "1") {
        $diasSemana = $request->input('dias_semana', []);
        $dataInicio = Carbon::parse($request->dia_agend);
        $dataFim = Carbon::parse($request->data_fim_recorrencia);

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
                    'RECORRENCIA' => $request->recorrencia ?? Str::uuid()->toString(),
                    'VALOR_AGEND' => $valorAgend,
                    'OBSERVACOES' => $request->observacoes,
                ];
                FaesaClinicaAgendamento::create($dados);
            }
        }
    }
    else {
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

        FaesaClinicaAgendamento::create($dados);
    }

    return redirect('/psicologia/criar-agendamento/')->with('success', 'Agendamento(s) criado(s) com sucesso!');
}


    /**
     * Recupera e retorna uma coleção de Pacientes, com opção de busca por nome
     * 
     * @param Request $request A instância da requisição HTTP. Pode conter o parâmetro 'search'.
     * @return \Illuminate\Http\JsonResponse Retorna uma resposta JSON contendo uma coleção
     * de objetos FaesaClinicaPaciente.
     */
    public function getPaciente(Request $request)
    {
        $search = $request->query('search');

        if ($search) {
            $pacientes = FaesaClinicaPaciente::where(function ($query) use ($search) {
                $query->where('NOME_COMPL_PACIENTE', 'LIKE', "%{$search}%")
                    ->orWhere('CPF_PACIENTE', 'LIKE', "%{$search}%");
            })->get();
        } else {
            $pacientes = FaesaClinicaPaciente::orderBy('ID_PACIENTE', 'DESC')->limit(10)->get();
        }

        return response()->json($pacientes);
    }


    /**
     * Identifica e altera um Paciente em específico
     * 
     * @param Request $request A instância da requisição HTTP. Pode conter o parâmetro 'search'.
     */
    public function editarPaciente(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nome'       => 'required|string|max:255',
            'cpf'        => 'required|string|max:14',
            'dt_nasc'    => 'nullable|date',
            'sexo'       => 'nullable|string|in:M,F,O',
            'endereco'   => 'nullable|string|max:255',
            'num'        => 'nullable|integer',
            'complemento'=> 'nullable|string|max:255',
            'bairro'     => 'nullable|string|max:255',
            'uf'         => 'nullable|string|max:2',
            'cep'        => 'nullable|string|max:20',
            'celular'    => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'municipio'  => 'nullable|string|max:255',
        ]);

        $paciente = FaesaClinicaPaciente::find($id);
        if (!$paciente) {
            return response()->json(['message' => 'Paciente não encontrado'], 404);
        }

        $paciente->NOME_COMPL_PACIENTE = $validatedData['nome'];
        $paciente->CPF_PACIENTE = $validatedData['cpf'];
        $paciente->DT_NASC_PACIENTE = $validatedData['dt_nasc'] ?? $paciente->DT_NASC_PACIENTE;
        $paciente->SEXO_PACIENTE = $validatedData['sexo'] ?? $paciente->SEXO_PACIENTE;
        $paciente->ENDERECO = $validatedData['endereco'] ?? $paciente->ENDERECO;
        $paciente->END_NUM = $validatedData['num'] ?? $paciente->END_NUM;
        $paciente->COMPLEMENTO = $validatedData['complemento'] ?? $paciente->COMPLEMENTO;
        $paciente->BAIRRO = $validatedData['bairro'] ?? $paciente->BAIRRO;
        $paciente->UF = $validatedData['uf'] ?? $paciente->UF;
        $paciente->CEP = $validatedData['cep'] ?? $paciente->CEP;
        $paciente->FONE_PACIENTE = $validatedData['celular'] ?? $paciente->FONE_PACIENTE;
        $paciente->E_MAIL_PACIENTE = $validatedData['email'] ?? $paciente->E_MAIL_PACIENTE;
        $paciente->MUNICIPIO = $validatedData['municipio'] ?? $paciente->MUNICIPIO;

        $paciente->save();

        return response()->json([
            'message' => 'Paciente atualizado com sucesso',
            'paciente' => $paciente,
        ]);
    }
}
