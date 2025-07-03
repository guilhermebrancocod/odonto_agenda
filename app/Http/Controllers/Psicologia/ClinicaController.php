<?php

namespace App\Http\Controllers\Psicologia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClinicaController extends Controller
{
    public function selecionarClinica(Request $request)
    {
        $clinicaSelecionada = $request->input('clinica');
        session(['clinica-selecionada' => $clinicaSelecionada ]);

        // Redireciona para o menu da clínica escolhida
        if ($clinicaSelecionada == 1) {
            return redirect()->route('menu_agenda_psicologia');
        } elseif ($clinicaSelecionada == 2) {
            return redirect()->route('menu_agenda_odontologia');
        } else {
            return redirect()->back()->with('error', 'Seleção inválida.');
        }
    }
}
