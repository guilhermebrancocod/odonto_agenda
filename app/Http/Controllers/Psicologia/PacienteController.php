<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaPaciente;
use Illuminate\Database\Eloquent\Collection;

class PacienteController extends Controller
{
    
    public function createPaciente(Request $request)
    {
        $validatedData = $request->validate([
            'NOME_COMPL_PACIENTE' => 'required|string|max:255',
            'DT_NASC_PACIENTE' => 'nullable|date',
            'CPF_PACIENTE' => 'required|string|max:14|unique:FAESA_CLINICA_PACIENTE,CPF_PACIENTE',
            'SEXO_PACIENTE' => 'required|string|in:M,F,O',
            'CEP' => 'required|string|max:20',
            'ENDERECO' => 'required|string|max:255',
            'END_NUM' => 'required|string',
            'COMPLEMENTO' => 'nullable|string|max:255',
            'BAIRRO' => 'required|string',
            'municipio' => 'required|string|max:255',
            'UF' => 'required|string|max:2',
            'E_MAIL_PACIENTE' => 'nullable|email|max:255',
            'FONE_PACIENTE' => 'required|string|max:20',
            'OBSERVACAO' => 'nullable|string',
            'STATUS' => 'required|string|max:50|in:Em espera'
        ], [
            'NOME_COMPL_PACIENTE.required' => 'Informe o nome do paciente antes de continuar.',
            'CPF_PACIENTE.required' => 'Informe o CPF do paciente antes de continuar.',
            'CPF_PACIENTE.unique' => 'Este CPF já está cadastrado no sistema. Por favor, verifique',
            'SEXO_PACIENTE.required' => 'Informe o sexo do paciente antes de continuar.',
            'CEP.required' => 'Informe o CPF do endereço do paciente antes de continuar.',
            'ENDERECO.required' => 'Informe o endereço do paciente antes de continuar',
            'END_NUM.required' => 'Informe o número do endereço do paciente antes de continuar.',
            'BAIRRO.required' => 'Informe o bairro do paciente antes de continuar.',
            'municipio.required' => 'Informe o municipio do paciente antes de continuar',
            'UF.required' => 'Informe o estado (UF) do endereço do paciente antes de continuar.',
            'FONE_PACIENTE.required' => 'Informe o telefone do paciente antes de continuar.',
        ]);

        $paciente = new FaesaClinicaPaciente();
        $paciente->NOME_COMPL_PACIENTE=$validatedData['NOME_COMPL_PACIENTE'];
        $paciente->CPF_PACIENTE=$validatedData['CPF_PACIENTE'];
        $paciente->DT_NASC_PACIENTE=$validatedData['DT_NASC_PACIENTE'] ?? null;
        $paciente->SEXO_PACIENTE=$validatedData['SEXO_PACIENTE'];
        $paciente->ENDERECO=$validatedData['ENDERECO'];
        $paciente->END_NUM=$validatedData['END_NUM'];
        $paciente->COMPLEMENTO=$validatedData['COMPLEMENTO'];
        $paciente->BAIRRO=$validatedData['BAIRRO'];
        $paciente->UF=$validatedData['UF'];
        $paciente->CEP=$validatedData['CEP'];
        $paciente->FONE_PACIENTE=$validatedData['FONE_PACIENTE'];
        $paciente->E_MAIL_PACIENTE=$validatedData['E_MAIL_PACIENTE'] ?? null;
        $paciente->MUNICIPIO=$validatedData['municipio'];
        $paciente->STATUS=$validatedData['STATUS'];

        $paciente->save();

        return redirect('/psicologia/criar-paciente')->with('success','Paciente criado com sucesso!');
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

        // SE PARAMETRO SEARCH VEM PREENCHIDO, PESQUISA POR NOME E POR CPF
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
