<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaesaClinicaAgendamento;

class AgendamentoController extends Controller
{

    public function getAgendamento(Request $request): FaesaClinicaAgendamento
    {
        dd($request->all());
    }
}
