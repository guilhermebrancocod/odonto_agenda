<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaServico;

class ServicoController extends Controller
{
    // CRIAÇÃO DE SERVIÇO
    public function criarServico(Request $request)
    {
        // ✅ Ajuste de valor do serviço
        $valorInput = $request->input('VALOR_SERVICO');
        if ($valorInput) {
            $valor = str_replace(['.', ','], ['', '.'], $valorInput);
            $request->merge(['VALOR_SERVICO' => $valor]);
        } else {
            $request->merge(['VALOR_SERVICO' => null]);
        }

        // ✅ Ajuste do código interno (null se vazio ou 0)
        if (!$request->filled('COD_INTERNO_SERVICO_CLINICA') || $request->input('COD_INTERNO_SERVICO_CLINICA') == 0) {
            $request->merge(['COD_INTERNO_SERVICO_CLINICA' => null]);
        }

        // ✅ Validação
        $validated = $request->validate([
            'ID_CLINICA' => 'required|integer|min:1',
            'SERVICO_CLINICA_DESC' => 'required|string|min:1|max:255',
            'COD_INTERNO_SERVICO_CLINICA' => 'nullable|integer|min:0',
            'VALOR_SERVICO' => 'nullable|numeric',
            'OBSERVACAO' => 'nullable|string|max:500',
            'TEMPO_RECORRENCIA_MESES' => 'nullable|integer|min:0',
        ]);

        // ✅ Verificação de duplicidade por nome
        $existeNome = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', $validated['SERVICO_CLINICA_DESC'])
            ->where('ID_CLINICA', $validated['ID_CLINICA'])
            ->exists();

        if ($existeNome) {
            return redirect()->back()
                ->withErrors(['Já existe um serviço com este nome nesta clínica.'])
                ->withInput();
        }

        // ✅ Verificação de duplicidade por código interno se informado
        if (!is_null($validated['COD_INTERNO_SERVICO_CLINICA'])) {
            $existeCodigo = FaesaClinicaServico::where('COD_INTERNO_SERVICO_CLINICA', $validated['COD_INTERNO_SERVICO_CLINICA'])
                ->where('ID_CLINICA', $validated['ID_CLINICA'])
                ->exists();

            if ($existeCodigo) {
                return redirect()->back()
                    ->withErrors(['Já existe um serviço com este código interno nesta clínica.'])
                    ->withInput();
            }
        }

        // ✅ Criação do serviço
        FaesaClinicaServico::create($validated);

        return redirect()->back()->with('success', 'Serviço criado com sucesso.');
    }

    // PESQUISA OS SERVIÇOS DISPONÍVEIS
    public function getServicos(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = FaesaClinicaServico::where('ID_CLINICA', 1); // FILTRO FIXO PARA CLÍNICA DE PSICOLOGIA

        if ($search) {
            $query->where('SERVICO_CLINICA_DESC', 'LIKE', "%{$search}%");
        }

        $servicos = $query->orderBy('ID_SERVICO_CLINICA', 'desc')->get();

        // Substituir null ou 0 no código interno por texto customizado
        $servicos->transform(function($item) {
            if (is_null($item->COD_INTERNO_SERVICO_CLINICA) || $item->COD_INTERNO_SERVICO_CLINICA == 0) {
                $item->COD_INTERNO_SERVICO_CLINICA = '--';
            }
            return $item;
        });

        return response()->json($servicos);
    }

    // ATUALIZAÇÃO DE SERVIÇO
    public function atualizarServico(Request $request, $id)
    {
        // Ajustar input antes da validação para evitar erro de "deve ser inteiro"
        $input = $request->all();

        // Ajusta código interno para null se vazio ou '--'
        if (isset($input['COD_INTERNO_SERVICO_CLINICA'])) {
            $cod = $input['COD_INTERNO_SERVICO_CLINICA'];
            if ($cod === '--' || trim($cod) === '') {
                $input['COD_INTERNO_SERVICO_CLINICA'] = null;
            }
        }

        // Atualiza os dados do request para a validação
        $request->replace($input);

        // Validação, incluindo campos que aparecem no formulário de edição
        $validated = $request->validate([
            'SERVICO_CLINICA_DESC' => 'required|string|max:255',
            'COD_INTERNO_SERVICO_CLINICA' => 'nullable|integer|min:0',
            'VALOR_SERVICO' => 'nullable',
            'OBSERVACAO' => 'nullable|max:500',
            'TEMPO_RECORRENCIA_MESES' => 'nullable|integer|min:0',
            'PERMITE_ATENDIMENTO_SIMULTANEO' => 'nullable|string|in:S,N',
        ]);

        // Ajuste do valor (troca vírgula por ponto)
        if (isset($validated['VALOR_SERVICO'])) {
            $valor = str_replace(',', '.', $validated['VALOR_SERVICO']);
            $validated['VALOR_SERVICO'] = is_numeric($valor) ? (float)$valor : null;
        }

        // Ajuste do código interno: se null ou 0, define como null
        if (!isset($validated['COD_INTERNO_SERVICO_CLINICA']) || $validated['COD_INTERNO_SERVICO_CLINICA'] == 0) {
            $validated['COD_INTERNO_SERVICO_CLINICA'] = null;
        }

        $servico = FaesaClinicaServico::find($id);
        if (!$servico) {
            return response()->json(['message' => 'Serviço não encontrado'], 404);
        }

        $clinicaId = $servico->ID_CLINICA;

        // Verificação duplicidade nome
        $existeNome = FaesaClinicaServico::where('SERVICO_CLINICA_DESC', $validated['SERVICO_CLINICA_DESC'])
            ->where('ID_CLINICA', $clinicaId)
            ->where('ID_SERVICO_CLINICA', '!=', $id)
            ->exists();

        if ($existeNome) {
            return response()->json(['message' => 'Já existe um serviço com este nome nesta clínica.'], 422);
        }

        // Verificação duplicidade código apenas se não for null
        if (!is_null($validated['COD_INTERNO_SERVICO_CLINICA'])) {
            $existeCodigo = FaesaClinicaServico::where('COD_INTERNO_SERVICO_CLINICA', $validated['COD_INTERNO_SERVICO_CLINICA'])
                ->where('ID_CLINICA', $clinicaId)
                ->where('ID_SERVICO_CLINICA', '!=', $id)
                ->exists();

            if ($existeCodigo) {
                return response()->json(['message' => 'Já existe um serviço com este código interno nesta clínica.'], 422);
            }
        }

        // Se PERMITE_ATENDIMENTO_SIMULTANEO não foi enviado, define padrão como 'N'
        if (!isset($validated['PERMITE_ATENDIMENTO_SIMULTANEO'])) {
            $validated['PERMITE_ATENDIMENTO_SIMULTANEO'] = 'N';
        }

        $servico->update($validated);

        return response()->json(['message' => 'Serviço atualizado com sucesso']);
    }

    // DELETAR SERVIÇO
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
