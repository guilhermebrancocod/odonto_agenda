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
        // VALIDA DADOS
        $validatedData = $request->validate([
            'MATRICULA' => 'required|min:8|max:8',
            'NOME_COMPL' => 'required|string|max:255',
            'DT_NASC_PSICOLOGO' => 'required|date',
            'CPF_PSICOLOGO' => 'required|string|max:14',
            'SEXO_PSCIOLOGO' => 'required|string|in:M,F,O',
            'E_MAIL_PSICOLOGO' => 'required|email|max:255',
            'FONE_PSICOLOGO' => 'required|string|max:20',
            // Criar validação dos campos de horário e disciplinas
        ]);

        // CRIA NO USUARIO_GERAL
        // CRIA NO PSICOLOGO
        // CRIA NO PSICLOGO_DISPONIBILIDADE
        // CRIA NO PSICOLOGO_DISCIPLINA
    }
}