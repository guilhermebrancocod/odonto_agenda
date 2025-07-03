<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        dd("Passou pela validação");
    }

    public function logout(Request $request)
    {
        dd($request);
    }
}
