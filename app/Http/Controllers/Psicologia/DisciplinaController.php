<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DisciplinaController extends Controller
{
    public function getDisciplina()
    { 
        $disciplinas = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as d')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA as m', 'm.DISCIPLINA', '=', 'd.DISCIPLINA')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as a', 'a.ALUNO', '=', 'm.ALUNO')
            ->where('a.CURSO', '2010')
            ->where('m.ANO', '2025')
            ->where('m.SEMESTRE', '1')
            ->distinct()
            ->select('d.DISCIPLINA', 'd.NOME')
            ->get();

        return response()->json($disciplinas);
    }
}
