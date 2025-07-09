<?php

namespace App\Http\Controllers\Odonto;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoCreateController extends Controller
{

    public function showForm()
    {
        return view('odontologia/create_patient');
    }

    public function fCreatePatient(Request $request)
    {
        $idPaciente = DB::table('FAESA_CLINICA_PACIENTE')->insertGetId(
            [
                'NOME_COMPL_PACIENTE' => $request->input('nome'),
                'CPF_PACIENTE' => preg_replace('/\D/', '', $request->input('cpf')),
                'DT_NASC_PACIENTE' => $request->input('dt_nasc'),
                'SEXO_PACIENTE' => $request->input('sexo'),
                'CEP' => preg_replace('/\D/', '', $request->input('cep')),
                'ENDERECO' => $request->input('rua'),
                'END_NUM' => $request->input('numero'),
                'END_COMPL' => $request->input('complemento'),
                'BAIRRO' => $request->input('bairro'),
                'MUNICIPIO' => $request->input('cidade'),
                'UF' => $request->input('estado'),
                'E_MAIL_PACIENTE' => $request->input('email'),
                'FONE_PACIENTE' => preg_replace('/\D/', '', $request->input('celular')),
            ]
        );

        return redirect()->route('consultarpaciente')->with('success', 'Paciente criado com sucesso');
    }

    public function showFormAgenda()
    {
        return view('odontologia/create_agenda');
    }

    public function fCreateAgenda(Request $request)
    {
        $idClinica = 2;

        if (!$idClinica) {
            return redirect()->back()->with('error', 'Clínica do paciente não encontrada.');
        }

        $recorrencia = $request->input('recorrencia');
        $diasSemana = $request->input('dia_semana', []);
        $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $dataFim = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'));

        // Se for recorrente
        if ($recorrencia === 'recorrencia' && $dataFim && count($diasSemana) > 0) {
            $diasMap = [
                'domingo' => 0,
                'segunda' => 1,
                'terca' => 2,
                'quarta' => 3,
                'quinta' => 4,
                'sexta' => 5,
                'sabado' => 6
            ];

            // Loop de datas
            $dataAtual = $dataInicio->copy();
            while ($dataAtual->lte($dataFim)) {
                if (in_array(array_search($dataAtual->dayOfWeek, $diasMap), $diasSemana)) {
                    DB::table('FAESA_CLINICA_AGENDAMENTO')->insert([
                        'ID_CLINICA' => $idClinica,
                        'ID_PACIENTE' => $request->input('ID_PACIENTE'),
                        'ID_SERVICO' => $request->input('servico'),
                        'DT_AGEND' => $dataAtual->format('Y-m-d'),
                        'HR_AGEND_INI' => $request->input('hr_ini'),
                        'HR_AGEND_FIN' => $request->input('hr_fim'),
                        'STATUS_AGEND' => 1,
                        'ID_AGEND_REMARCADO' => null,
                        'RECORRENCIA' => $recorrencia,
                        'VALOR_AGEND' => $request->input('valor'),
                        'OBSERVACOES' => $request->input('obs'),
                    ]);
                }
                $dataAtual->addDay();
            }
        } else {
            // Agendamento pontual
            DB::table('FAESA_CLINICA_AGENDAMENTO')->insert([
                'ID_CLINICA' => $idClinica,
                'ID_PACIENTE' => $request->input('ID_PACIENTE'),
                'ID_SERVICO' => $request->input('servico'),
                'DT_AGEND' => $request->input('date'),
                'HR_AGEND_INI' => $request->input('hr_ini'),
                'HR_AGEND_FIN' => $request->input('hr_fim'),
                'STATUS_AGEND' => $request->input('status'),
                'ID_AGEND_REMARCADO' => null,
                'RECORRENCIA' => $recorrencia,
                'VALOR_AGEND' => $request->input('valor'),
                'OBSERVACOES' => $request->input('obs'),
            ]);
        }

        return redirect()->route('criaragenda')->with('success', 'Agendamento realizado com sucesso');
    }


    public function editPatient($pacienteId)
    {
        $paciente = DB::table('FAESA_CLINICA_PACIENTE')->where('ID_PACIENTE', $pacienteId)->first();

        if (!$paciente) {
            abort(404);
        }

        return view('odontologia/create_patient', compact('paciente'));
    }

    public function editAgenda($agendaId)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->where('a.ID_AGENDAMENTO', $agendaId)->first();

        if (!$agenda) {
            abort(404);
        }

        return view('odontologia.create_agenda', compact('agenda'));
    }
}
