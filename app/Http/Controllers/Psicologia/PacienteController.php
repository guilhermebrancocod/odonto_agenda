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
            'FONE_PACIENTE' => 'nullable|string|max:20',
            'E_MAIL_PACIENTE' => 'nullable|email|max:255|unique:FAESA_CLINICA_PACIENTE,E_MAIL_PACIENTE',
        ]);

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
        // ARMAZENA NOME DO PACIENTE EM QUESTÃO PARA BUSCA
        $nome = $request->query('search');

        // CASO NOME SEJA FORNECIDO, PESQUISA POR ELE
        if ($nome) {
            $pacientes = FaesaClinicaPaciente::where('NOME_COMPL_PACIENTE', 'like', "%{$nome}%")->get();
        
        // CASO NOME NÃO SEJA FORNECIDO, RETORNA OS 10 ÚLTIMOS PACIENTES ADICIONADOS    
        } else {
            $pacientes = FaesaClinicaPaciente::orderBy('ID_PACIENTE', 'DESC')->limit(10)->get();
        }

        return response()->json($pacientes);
    }
}
