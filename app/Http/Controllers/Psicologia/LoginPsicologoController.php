<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginPsicologoController extends Controller
{
    public function login(Request $request)
    {
        if(session()->has('psicologo')) {
            return redirect()->route('psicologoAgenda');
        } else {
            dd($request);
        }
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('psicologoLoginGet');
    }
}
