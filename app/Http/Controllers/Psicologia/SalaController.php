<?php

namespace app\Http\Controllers\Psicologia;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FaesaClinicaSala;

class SalaController extends Controller
{
    public function createSala(Request $request)
    {
        $idClinica = $request->input('id_clinica');
        
        $validatedData = $request->validate([
            'DESCRICAO' => 'required|string|max:255',
        ], [
            'DESCRICAO.required' => 'A descrição da Sala é obrigatória',
            'DESCRICAO.string' => 'A descrição da sala não pode ser numérica',
            'DESCRICAO.max' => 'A descrição da sala não pode ter mais de 255 caracteres',
        ]);

        // Verifica se a clínica existe
        if (FaesaClinicaSala::where('DESCRICAO', $validatedData['DESCRICAO'])->exists()) {
            return response()->json(['error' => 'ID da clínica não fornecido'], 400);
        }

        $sala = new FaesaClinicaSala();
        $sala->DESCRICAO = $validatedData['DESCRICAO'];
        $sala->save();

        return redirect()->route('/psicologia/criar-sala')->with('success', 'Sala criada com sucesso!');
    }

    public function getSala()
    {

    }

    public function updateSala(Request $request, $id)
    {

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

        $salas = $query->orderBy('CREATED_AT', 'desc')->get();

        return response()->json($salas);
    }
}
