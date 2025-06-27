<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoController extends Controller
{
    public function fIncludePatient(Request $request)
    {
        $idPaciente = DB::table('TABELA_PACIENTE')->insertGetId(
            [
                'NOME_COMPL_PACIENTE' => $request->input('nome'),
                'CPF_PACIENTE' => $request->input('cpf_paciente'),
                'DT_NASC_PACIENTE' => $request->input('dt_nasc'),
                'SEXO_PACIENTE' => $request->input('sexo'),
                'CEP' => $request->input('cep'),
                'ENDERECO' => $request->input('rua'),
                'END_NUM' => $request->input('numero'),
                'BAIRRO' => $request->input('bairro'),
                'MUNICIPIO' => $request->input('cidade'),
                'UF' => $request->input('estado'),
                'E_MAIL_PACIENTE' => $request->input('email'),
                'FONE_1_PACIENTE' => $request->input('celular'),
                'FONE_2_PACIENTE' => $request->input('telefone')
            ]
        );
        
        return response()->json(['success'=>true,'paciente_id' => $idPaciente]);
    }

    public function fSelectPatient(Request $request)
    {
        $query_patient = $request->input('search-input');

        $selectPatient = DB::table('TABELA_PACIENTE')
        ->select('NOME_COMPL')
        ->where('NOME_COMPL','like','%'.$query_patient.'%')
        ->orwhere('CPF_PACIENTE','like','%'.$query_patient.'%')
        ->get();

        return response()->json($selectPatient);
    }
}
