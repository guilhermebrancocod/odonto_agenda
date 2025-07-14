<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoUpdateController extends Controller
{
    public function updatePatient(Request $request, $id)
    {
        $paciente = DB::table('FAESA_CLINICA_PACIENTE')->where('ID_PACIENTE', $id)->first();

        DB::table('FAESA_CLINICA_PACIENTE')
            ->where('ID_PACIENTE', $id)
            ->update([
                'NOME_COMPL_PACIENTE' => $request->input('nome'),
                'CPF_PACIENTE' => preg_replace('/\D/', '', $request->input('cpf')),
                'DT_NASC_PACIENTE' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('dt_nasc'))->format('Y-m-d'),
                'SEXO_PACIENTE' => $request->input('sexo'),
                'CEP' => preg_replace('/\D/', '', $request->input('cep')),
                'ENDERECO' => $request->input('rua'),
                'END_NUM' => $request->input('numero'),
                'COMPLEMENTO' => $request->input('complemento'),
                'BAIRRO' => $request->input('bairro'),
                'MUNICIPIO' => $request->input('cidade'),
                'UF' => $request->input('estado'),
                'E_MAIL_PACIENTE' => $request->input('email'),
                'FONE_PACIENTE' => $request->input('celular')
            ]);

        return redirect()->back()->with('success', 'Paciente atualizado com sucesso!');
    }

        public function updateService(Request $request, $idService)
    {
        $servico = DB::table('FAESA_CLINICA_SERVICO')->where('ID_SERVICO_CLINICA', $idService)->first();

        DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', $idService)
            ->update([
                'ID_CLINICA' => 2,
                'SERVICO_CLINICA_DESC' => $request->input('descricao'),
                'COD_INTERNO_SERVICO_CLINICA' => 0,
                'VALOR_SERVICO' => $request->input('valor')
            ]);

        return redirect()->back()->with('success', 'Paciente atualizado com sucesso!');
    }

    public function updateAgenda(Request $request, $id)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')->where('ID_AGENDAMENTO', $id)->first();

        $valor = $request->input('valor') ? str_replace(',', '.', $request->input('valor')) : null;

        DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $id)
            ->update([
                'ID_CLINICA' => 2,
                'ID_PACIENTE' => $request->input('ID_PACIENTE'),
                'ID_SERVICO' => $request->input('servico'),
                'DT_AGEND' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
                'DT_AGEND' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->format('Y-m-d'),
                'HR_AGEND_INI' => $request->input('hr_ini'),
                'HR_AGEND_FIN' => $request->input('hr_fim'),
                'STATUS_AGEND' => $request->input('status'),
                'ID_AGEND_REMARCADO' => $request->input('ID_AGEND_REMARCADO') ?: null,
                'RECORRENCIA' => $request->input('recorrencia'),
                'VALOR_AGEND' => $valor,
                'OBSERVACOES' => $request->input('obs'),
            ]);

        return redirect()->back()->with('success', 'Agendamento atualizado com sucesso!');
    }

    public function editStatus(Request $request, $agendaId)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $agendaId)
            ->update([
                'STATUS_AGEND' => $request->input('status')
            ]);

        return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
    }
}
