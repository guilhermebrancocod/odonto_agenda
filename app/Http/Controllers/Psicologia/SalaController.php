<?php

namespace app\Http\Controllers\Psicologia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FaesaClinicaSala;
use Illuminate\Validation\Rule;

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

    public function deleteSala($id)
    {

    }

    // LISTAGEM DE SALAS
    public function listSalas(Request $request)
    {
        $search = trim($request->input('search', ''));
        $query = FaesaClinicaSala::where('DESCRICAO', 'like', '%' . $search . '%');

        if($search) {
            $query->where('DESCRICAO', 'like', '%' . $search . '%');
        }

        $salas = $query->orderBy('DESCRICAO', 'desc')->get();

        return response()->json($salas);
    }
}
