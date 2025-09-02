<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DisciplinaController extends Controller
{
    public function getDisciplina()
    { 
        $anoSemestre = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_OPCOES')
            ->where('CHAVE', 4)
            ->select('ANO_LETIVO', 'SEM_LETIVO')
            ->first();
        
        $disciplinas = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_TURMA as t')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as d', 'd.DISCIPLINA', 't.DISCIPLINA')
            // ->where('t.FL_FIELD_17', 'CLINICA')
            ->where('t.ANO', $anoSemestre->ANO_LETIVO)
            ->where('t.SEMESTRE', $anoSemestre->SEM_LETIVO)
            ->select('d.DISCIPLINA', 'd.NOME')
            ->distinct()
            ->get();


        return response()->json($disciplinas);
    }

    // QUANDO ESTIVER EM PRODUÇÃO UTILIZAR FUNCAO ABAIXO
    // public function getDisciplina()
    // {

    // }

    public function getDisciplinaByCodigo($codigo): JsonResponse
    {
        $disciplina = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA')
        ->where('DISCIPLINA', $codigo)
        ->select('NOME')
        ->first();

        if(!$disciplina) {
            return response()->json(['NOME' => 'Disciplina não encontrada'], 404);
        }

        return response()->json(['NOME' => $disciplina->NOME]);
    }

    // FUNCAO PARA QUANDO ESTIVER IMPLEMENTADO
    // public function getDisciplinaByCodigo($codigo): string
    // {

    // }
}
