<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function consultaRelatorios(Request $request)
    {
        $lista = DB::table('FAESA_CLINICA_RELATORIOS')
            ->select([
                'TITULO',
                'SLUG',
                'CATEGORIA'
            ]);

        return view('odontologia/relatorios', [
            'listaRelatorios' => $lista,
        ]);
    }
}
