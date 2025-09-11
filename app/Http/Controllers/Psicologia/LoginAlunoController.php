<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginalunoController extends Controller
{
    public function login(Request $request)
    {
        if(session()->has('aluno')) {
            return redirect()->route('alunoAgenda');
        } else {
            dd($request);
        }
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('alunoLoginGet');
    }
}
