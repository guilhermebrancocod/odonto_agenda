<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaServico;

class ServicoController extends Controller
{
    public function criarServico(Request $request)
    {
        $request->merge([
            'VALOR_SERVICO' => $request->input('VALOR_SERVICO') !== '' ? $request->input('VALOR_SERVICO') : null,
        ]);

        $validated = $request->validate([
            'ID_CLINICA' => 'required|integer|min:1',
            'SERVICO_CLINICA_DESC' => 'required|string|min:1|max:255',
            'VALOR_SERVICO' => 'nullable|numeric',
        ]);

        FaesaClinicaServico::create($validated);

        return redirect()->back()->with('success', 'Serviço criado com sucesso');
    }

    // PESQUISA OS SERVIÇOS DISPONÍVES
    // UTILIZADO NA PÁGINA DE CRIAÇÃO DE AGENDAMENTO AO TENTAR SELECIONAR SERVIÇO
    public function getServicos(Request $request)
    {
        $search = trim($request->query('search', '')); // Trim para evitar conflitos

        $query = FaesaClinicaServico::query();
        if ($search) {
            $query->where('SERVICO_CLINICA_DESC', 'LIKE', "%{$search}%");
        }

        $servicos = $query->orderBy('ID_SERVICO_CLINICA', 'desc')->get();

        return response()->json($servicos);
    }

    // Método para atualizar um serviço
    public function atualizarServico(Request $request, $id)
    {
        $validated = $request->validate([
            'SERVICO_CLINICA_DESC' => 'required|string|max:255',
            'COD_INTERNO_SERVICO_CLINICA' => 'required|integer|min:0',
            'VALOR_SERVICO' => 'nullable|numeric'
        ]);

        $servico = FaesaClinicaServico::find($id);
        if (!$servico) {
            return response()->json(['message' => 'Serviço não encontrado'], 404);
        }

        $servico->update($validated);

        return response()->json(['message' => 'Serviço atualizado com sucesso']);
    }
}
