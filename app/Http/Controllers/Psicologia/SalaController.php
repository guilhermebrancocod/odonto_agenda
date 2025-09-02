<?php

namespace app\Http\Controllers\Psicologia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FaesaClinicaAgendamento;
use App\Models\FaesaClinicaSala;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class SalaController extends Controller
{
    public function createSala(Request $request)
    {
        $idClinica = $request->input('id_clinica');

        $validatedData = $request->validate([
            'DESCRICAO' => 'required|string|max:255|unique:FAESA_CLINICA_SALA,DESCRICAO',
            'DISCIPLINA' => 'string|max:10'
        ], [
            'DESCRICAO.required' => 'A descrição da Sala é obrigatória',
            'DESCRICAO.unique' => 'Já existe uma sala com essa descrição.',
            'DESCRICAO.string' => 'A descrição da sala não pode ser numérica',
            'DESCRICAO.max' => 'A descrição da sala não pode ter mais de 255 caracteres',
            'DISCIPLINA.string' => 'A Disciplina deve conter o código da Disciplina',
        ]);

        $sala = new FaesaClinicaSala();
        $sala->DESCRICAO = $validatedData['DESCRICAO'];
        $sala->DISCIPLINA = $validatedData['DISCIPLINA'];
        $sala->save();

        return redirect()->route('salas_psicologia')->with('success', 'Sala criada com sucesso!');
    }


    public function getSala(Request $request)
    {
        $search = trim($request->query('search', ''));

        // RETORNA APENAS SALAS QUE ESTÃO ATIVAS PARA CRIAR AGENDAMENTO
        $salas = FaesaClinicaSala::where('DESCRICAO', 'like', "%{$search}%")
                    ->where('ATIVO', '<>', 'N')
                    ->select('ID_SALA_CLINICA', 'DESCRICAO')
                    ->get();

        return response()->json($salas);
    }


    public function updateSala(Request $request, $id)
    {
        $requestData = $request->json()->all();

        $validatedData = validator($requestData, [
            'DESCRICAO' => [
                'required',
                'string',
                'max:255',
                Rule::unique('FAESA_CLINICA_SALA', 'DESCRICAO')->ignore($id, 'ID_SALA_CLINICA'),
            ],
            'DISCIPLINA' => 'nullable|string|max:10',
            'ATIVO' => 'required|in:S,N',
        ], [
            'DESCRICAO.required' => 'A descrição da Sala é obrigatória',
            'DESCRICAO.unique' => 'Já existe uma sala com essa descrição.',
            'DESCRICAO.string' => 'A descrição da sala não pode ser numérica',
            'DESCRICAO.max' => 'A descrição da sala não pode ter mais de 255 caracteres',
            'DISCIPLINA.string' => 'A Disciplina deve conter o código da Disciplina',
        ])->validate();

        $sala = FaesaClinicaSala::findOrFail($id);
        $sala->update($validatedData);

        return response()->json(['message' => 'Sala atualizada com sucesso!']);
    }

    public function deleteSala($id): JsonResponse
    {
        $sala = FaesaClinicaSala::find($id);
        if(!$sala) {
            return response()->json(['message' => 'Sala não foi encontrada'], 404);
        } else if ($sala->ATIVO === 'S') {
            return response()->json(['message' => 'Sala não pôde ser excluída pois ainda está ativa. Desative-a antes de prosseguir com exclusão.'], 422);
        }

        $agendamentos = FaesaClinicaAgendamento::where('ID_SALA', $id)->exists();

        if($agendamentos) {
            return response()->json(['message' => 'Sala possui agendamento(s) vinculados e por isso não pode ser excluída'], 422);
        } else {
            // Nao exclui diretamente, mas muda situacao para excluido, caso seja necessario reverter
            $sala->SIT_SALA = 'Excluido';
            $sala->save();
            return response()->json(['message' => 'Sala excluída com sucesso.'], 200);
        }
    }

    // LISTAGEM DE SALAS
    public function listSalas(Request $request)
    {
        $search = trim($request->input('search', ''));
        $query = FaesaClinicaSala::query();

        $query->where(function ($q) {
            $q->where('SIT_SALA', '<>', 'Excluido')
            ->orWhereNull('SIT_SALA');
        });

        $query->when($search, function ($query, $search) {
            return $query->where('DESCRICAO', 'like', '%' . $search . '%');
        });

        $salas = $query->orderBy('DESCRICAO', 'desc')->get();

        return response()->json($salas);
    }
}
