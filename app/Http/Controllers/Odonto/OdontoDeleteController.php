<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoDeleteController extends Controller
{
    public function deleteBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        $deleted = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->delete();

        if ($deleted) {
            return redirect('odontologia/consultardisciplinabox')
                ->with('success', 'Box Disciplina removido com sucesso.');
        }

        return redirect('odontologia/criarboxdisciplina')
            ->with('error', 'Box Disciplina não encontrado.');
    }
}
