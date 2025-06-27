<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaPaciente;

class PacienteController extends Controller
{
    public function criarPaciente(Request $request)
    {
        // VALIDAÇÃO DOS DADOS
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

        // CRIAÇÃO DO PACIENTE
        $paciente = FaesaClinicaPaciente::create($validated);

        dd($paciente);

        // RETORNO DE SUCESSO
        return response()->json([
            'message' => 'Paciente criado com sucesso',
            'paciente' => $paciente,
        ], 201);
    }
}
