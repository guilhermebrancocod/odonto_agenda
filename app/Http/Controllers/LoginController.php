<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $usuario = session('usuario');
        $clinicas = $usuario->pluck('ID_CLINICA')->toArray();

        if(in_array(1, $clinicas) && in_array(2, $clinicas)) {
            dd("Acesso Às duas clínicas");
        } else if (in_array(1, $clinicas)) {
            dd("Acesso à clínica de Psicologia");
        } else if (in_array(2, $clinicas)) {
            dd("Acesso à clínica de Odontologia");
        } else {
            dd("Usuário com acesso inválido");
        }
    }

    public function logout(Request $request)
    {
        dd($request);
    }
}
