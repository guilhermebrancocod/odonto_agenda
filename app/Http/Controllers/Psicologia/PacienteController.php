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
    public function criarPaciente(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'CPF_PACIENTE' => 'required|string|unique:FAESA_CLINICA_PACIENTE,CPF_PACIENTE',
            'NOME_COMPL_PACIENTE' => 'required|string|max:255',
            'DT_NASC_PACIENTE' => 'required|date',
            'SEXO_PACIENTE' => 'required|in:M,F,O',
            'ENDERECO' => 'nullable|string|max:100',
            'END_NUM' => 'nullable|string|max:10',
            'END_COMPL' => 'nullable|string|max:255',
            'BAIRRO' => 'nullable|string|max:50',
            'UF' => 'nullable|string|size:2',
            'CEP' => 'nullable|string|max:9',
            'FONE_PACIENTE' => 'required|string|max:20',
            'E_MAIL_PACIENTE' => 'required|email|max:255',
            'OBSERVACAO' => 'nullable|string|max:500',
        ], [
            'CPF_PACIENTE.required' => 'Por favor, informe o CPF do paciente.',
            'CPF_PACIENTE.unique' => 'Este CPF já está cadastrado.',
            'NOME_COMPL_PACIENTE.required' => 'Por favor, informe o nome completo do paciente.',
            'DT_NASC_PACIENTE.required' => 'Por favor, informe a data de nascimento.',
            'DT_NASC_PACIENTE.date' => 'Informe uma data de nascimento válida.',
            'SEXO_PACIENTE.required' => 'Por favor, selecione o sexo do paciente.',
            'SEXO_PACIENTE.in' => 'Sexo selecionado inválido.',
            'E_MAIL_PACIENTE.required' => 'Por favor, informe o e-mail do paciente.',
            'E_MAIL_PACIENTE.email' => 'Informe um e-mail válido.',
            'FONE_PACIENTE.required' => 'Por favor, informe o celular do paciente.',
        ]
        );

        FaesaClinicaPaciente::create($validated);

        return redirect()->back()->with('success', 'Paciente criado com sucesso!');
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
