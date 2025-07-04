<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // ARMAZENA LOGIN DE USUARIO E ID DA CLINICA
        $usuario = session('usuario');
        $clinicas = $usuario->pluck('ID_CLINICA')->toArray();
        

        // VERIFICA CLINICAS QUE USUÁRIO TEM ACESSO
        if(in_array(1, $clinicas) && in_array(2, $clinicas)) {
            return redirect()->route('selecionar-clinica-get');

        // CASO USUARIO POSSA ACESSAR SOMENTE PSICOLOGIA
        } else if (in_array(1, $clinicas)) {
           return redirect()->route('menu_agenda_psicologia');

        // CASO USUARIO POSSA ACESSAR SOMENTE ODONTOLOGIA
        } else if (in_array(2, $clinicas)) {
            return redirect()->route('menu_agenda_odontologia');

        // CASO DE ACESSO INVÁLIDO
        } else {
            dd("Usuário com acesso inválido");
        }
    }

    public function logout(Request $request)
    {
        session()->flush();
        return redirect()->route('loginGET');
    }
}
