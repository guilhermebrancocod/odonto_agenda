<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoDeleteController extends Controller
{
    public function deleteBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        // Existe agenda vinculada a este Box/Disciplina?
        $hasAgenda = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as d')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', function ($join) {
                $join->on('la.ID_BOX', '=', 'd.ID_BOX')
                    ->on('la.DISCIPLINA', '=', 'd.DISCIPLINA');
            })
            ->join('FAESA_CLINICA_AGENDAMENTO as a', 'a.ID_AGENDAMENTO', '=', 'la.ID_AGENDAMENTO')
            ->where('d.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->exists();

        if ($hasAgenda) {
            return redirect('odontologia/consultardisciplinabox')
                ->with('error', 'Não é possível remover: existe agendamento para este local/disciplina.');
        }

        // Tenta remover
        $deleted = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->delete();

        if ($deleted) {
            return redirect('odontologia/consultardisciplinabox')
                ->with('success', 'Box/Disciplina removido com sucesso.');
        }

        return redirect('odontologia/criarboxdisciplina')
            ->with('error', 'Box/Disciplina não encontrado.');
    }
}
