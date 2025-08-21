<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PsicologoController extends Controller
{
    public function listAlunos(string $matricula) {
        $resultado = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA as m')
        ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as a', 'a.ALUNO', 'm.ALUNO')
        ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p', 'a.NOME_COMPL', 'p.NOME_COMPL')
        ->where('m.ALUNO', 'like', value: "$matricula%")
        ->where('a.CURSO', '2010')
        ->where('m.ANO', '2025')
        ->where('m.SEMESTRE', '2')
        ->where('m.SIT_MATRICULA', 'Matriculado')
        ->distinct()
        ->select('m.ALUNO', 'a.NOME_COMPL', 'p.DT_NASC', 'p.CPF', 'p.SEXO')
        ->get();

        return $resultado->toJson();
    }

    public function createPsicologo(Request $request)
    {
        dd($request->all());
    }
}