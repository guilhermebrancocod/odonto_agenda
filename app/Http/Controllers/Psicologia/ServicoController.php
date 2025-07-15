<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaServico;

class ServicoController extends Controller
{

    // CRIAÇAÕ DE SERVICO
    public function criarServico(Request $request)
    {
        if ($request->filled('VALOR_SERVICO')) {
            $valor = str_replace(',', '.', $request->input('VALOR_SERVICO'));
            $valor = number_format((float)$valor, 2, '.', '');
            $request->merge([
                'VALOR_SERVICO' => $valor
            ]);
        } else {
            $request->merge([
                'VALOR_SERVICO' => null
            ]);
        }

        $permiteAtendimento = $request->has('PERMITE_ATENDIMENTO_SIMULTANEO') ? 'S' : 'N';
        $request->merge([
            'PERMITE_ATENDIMENTO_SIMULTANEO' => $permiteAtendimento
        ]);

        $validated = $request->validate([
            'ID_CLINICA' => 'required|integer|min:1',
            'SERVICO_CLINICA_DESC' => 'required|string|min:1|max:255',
            'COD_INTERNO_SERVICO_CLINICA' => 'required|integer|min:0',
            'VALOR_SERVICO' => 'nullable|numeric',
            'PERMITE_ATENDIMENTO_SIMULTANEO' => 'required|in:S,N',
        ]);

        if (isset($validated['VALOR_SERVICO'])) {
            $validated['VALOR_SERVICO'] = (float)str_replace(',', '.', $validated['VALOR_SERVICO']);
        }

        $existeNome = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', $validated['SERVICO_CLINICA_DESC'])
            ->where('ID_CLINICA', $validated['ID_CLINICA'])
            ->exists();

        $existeCodigo = FaesaClinicaServico::where('COD_INTERNO_SERVICO_CLINICA', $validated['COD_INTERNO_SERVICO_CLINICA'])
            ->where('ID_CLINICA', $validated['ID_CLINICA'])
            ->exists();

        if ($existeNome) {
            return redirect()->back()
                ->withErrors(['Já existe um serviço com este nome nesta clínica.'])
                ->withInput();
        }

        if ($existeCodigo) {
            return redirect()->back()
                ->withErrors(['Já existe um serviço com este código interno nesta clínica.'])
                ->withInput();
        }

        FaesaClinicaServico::create($validated);

        return redirect()->back()->with('success', 'Serviço criado com sucesso');
    }


    // PESQUISA OS SERVIÇOS DISPONÍVEIS
    // UTILIZADO NA PÁGINA DE CRIAÇÃO DE AGENDAMENTO AO TENTAR SELECIONAR SERVIÇO
    public function getServicos(Request $request)
    {
        $search = trim($request->query('search', '')); 

        $query = FaesaClinicaServico::query();
        if ($search) {
            $query->where('SERVICO_CLINICA_DESC', 'LIKE', "%{$search}%");
        }

        return response()->json($query->orderBy('ID_SERVICO_CLINICA', 'desc')->get());
    }

    // ATUALIZACAO DE SERVICO
    public function atualizarServico(Request $request, $id)
    {
        $validated = $request->validate([
            'SERVICO_CLINICA_DESC' => 'required|string|max:255',
            'COD_INTERNO_SERVICO_CLINICA' => 'required|integer|min:0',
            'VALOR_SERVICO' => 'nullable',
            'PERMITE_ATENDIMENTO_SIMULTANEO' => 'required|in:S,N',
        ]);

        if (isset($validated['VALOR_SERVICO'])) {
            $valor = str_replace(',', '.', $validated['VALOR_SERVICO']);
            $validated['VALOR_SERVICO'] = is_numeric($valor) ? (float)$valor : null;
        }

        $servico = FaesaClinicaServico::find($id);
        if (!$servico) {
            return response()->json(['message' => 'Serviço não encontrado'], 404);
        }

        $clinicaId = $servico->ID_CLINICA;

        $existeNome = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', $validated['SERVICO_CLINICA_DESC'])
            ->where('ID_CLINICA', $clinicaId)
            ->where('ID_SERVICO_CLINICA', '!=', $id)
            ->exists();

        if ($existeNome) {
            return response()->json(['message' => 'Já existe um serviço com este nome nesta clínica.'], 422);
        }

        $existeCodigo = FaesaClinicaServico::where('COD_INTERNO_SERVICO_CLINICA', $validated['COD_INTERNO_SERVICO_CLINICA'])
            ->where('ID_CLINICA', $clinicaId)
            ->where('ID_SERVICO_CLINICA', '!=', $id)
            ->exists();

        if ($existeCodigo) {
            return response()->json(['message' => 'Já existe um serviço com este código interno nesta clínica.'], 422);
        }

        $servico->update($validated);

        return response()->json(['message' => 'Serviço atualizado com sucesso']);
    }

    // DELETER SERVIÇO
    public function deletarServico($id)
    {
        $servico = FaesaClinicaServico::find($id);

        if (!$servico) {
            return response()->json(['message' => 'Serviço não encontrado.'], 404);
        }

        $temAgendamentos = \DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_SERVICO', $id)
            ->exists();

        if ($temAgendamentos) {
            return response()->json([
                'message' => 'Não é possível excluir este serviço porque existem agendamentos vinculados a ele. Para excluir, é necessário remover ou atualizar os agendamentos antes.'
            ], 422);
        }

        $servico->delete();

        return response()->json(['message' => 'Serviço excluído com sucesso.']);
    }
}
