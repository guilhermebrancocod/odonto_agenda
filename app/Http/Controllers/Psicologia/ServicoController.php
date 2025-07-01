<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaServico;

class ServicoController extends Controller
{
    public function criarServico(Request $request)
    {

        // Precisa adicionar valor de variavel de sessão ID_CLINICA para completar cadastro de Serviço.
        // Será feito após completar página de login.

        $validated = $request->validate([
            'ID_CLINICA' => 'required|integer|min:1',
            'SERVICO_CLINICA_DESC' => 'required|string|min:1',
            'COD_INTERNO_SERVICO_CLINICA' => 'required|integer|min:1',
        ]);

        FaesaClinicaServico::create($validated);

        return redirect()->back()->with('success', 'Serviço criado com sucesso');
    }
}
