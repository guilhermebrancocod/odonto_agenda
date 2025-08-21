<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    // RETORNA PROFESSORES
    public function getProfessor()
    {
        
    }

    // CRIA PROFESSOR
    public function createProfessor(Request $request)
    {
        // VALIDA OS DADOS
        $validatedData = $request->validate([
            ''
        ]);

        // CRIA REGISTRO
    }
}
