<?php

namespace App\Http\Controllers\Odonto;

use Carbon\Carbon;
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
        // Remove máscara do CPF
        $cpf = preg_replace('/\D/', '', $request->input('cpf'));

        // Verifica se o CPF já existe
        $cpfExiste = DB::table('FAESA_CLINICA_PACIENTE')
            ->where('CPF_PACIENTE', $cpf)
            ->exists();

        if ($cpfExiste) {
            return redirect()->back()
                ->withInput()
                ->with('alert', 'Paciente já existe!');
        }

        // Cadastro do paciente
        $idPaciente = DB::table('FAESA_CLINICA_PACIENTE')->insertGetId([
            'NOME_COMPL_PACIENTE' => $request->input('nome'),
            'CPF_PACIENTE' => $cpf,
            'COD_SUS' => $request->input('cod_sus'),
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
            'FONE_PACIENTE' => preg_replace('/\D/', '', $request->input('celular')),
            'NOME_RESPONSAVEL' => $request->input('nome_resposavel'),
            'CPF_RESPONSAVEL' => $request->input('cpf_responsavel'),
            'OBSERVACAO' => $request->input('obs_laudo')
        ]);

        return redirect()->back()->with('success', 'Paciente criado com sucesso!');
    }

    public function showFormAgenda()
    {
        return view('odontologia/create_agenda');
    }

    public function fCreateAgenda(Request $request)
    {
        $idClinica = 2;
        $idBox = $request->input('ID_BOX');
        $valor_convert = $request->input('VALOR_AGEND');

        if ($valor_convert === null || $valor_convert === '') {
            $valor_convert = null;
        } else {
            $tmp = str_replace(['R$', ' ', '.'], '', $valor_convert);
            $valor_convert = (float) str_replace(',', '.', $tmp);
        }

        $recorrencia = $request->input('recorrencia');
        $diasSemana  = $request->input('dia_semana', []);
        $dataInicio  = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $dataFim     = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'));

        $hrIni = Carbon::createFromFormat('H:i', $request->hr_ini)->format('H:i:s');
        $hrFin = Carbon::createFromFormat('H:i', $request->hr_fim)->format('H:i:s');

        $disciplina = DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
            ->where('ID_SERVICO_CLINICA', $request->input('ID_SERVICO'))
            ->value('DISCIPLINA');

        $descricaoLocal = DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->value('DESCRICAO');


        $diasMap = ['domingo' => 0, 'segunda' => 1, 'terca' => 2, 'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6];

        $diasSelecionados = array_values(array_intersect_key($diasMap, array_flip($diasSemana)));


        if ($recorrencia === 'recorrencia' && $dataFim && !empty($diasSelecionados)) {
            // anda dia a dia e insere quando bater
            $dataAtual = $dataInicio->copy();
            while ($dataAtual->lte($dataFim)) {
                if (in_array($dataAtual->dayOfWeek, $diasSelecionados, true)) {
                    $idAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                        'ID_CLINICA'         => $idClinica,
                        'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                        'ID_SERVICO'         => (int) $request->input('ID_SERVICO'),
                        'DT_AGEND'           => $dataAtual->format('Y-m-d'),
                        'HR_AGEND_INI'       => $hrIni,
                        'HR_AGEND_FIN'       => $hrFin,
                        'STATUS_AGEND'       => $request->input('status'),
                        'ID_AGEND_REMARCADO' => null,
                        'RECORRENCIA'        => $recorrencia,
                        'VALOR_AGEND'        => $valor_convert,
                        'OBSERVACOES'        => $request->input('obs'),
                        'LOCAL'              => $descricaoLocal,
                    ]);

                    DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                        'ID_AGENDAMENTO' => $idAgendamento,
                        'ID_BOX'         => $idBox,
                        'DISCIPLINA'     => $disciplina,
                    ]);
                }
                $dataAtual->addDay();
            }
        } else {
            // pontual
            $idAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                'ID_CLINICA'         => $idClinica,
                'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                'ID_SERVICO'         => (int) $request->input('ID_SERVICO'),
                'DT_AGEND'           => $dataInicio->format('Y-m-d'),
                'HR_AGEND_INI'       => $hrIni,
                'HR_AGEND_FIN'       => $hrFin,
                'STATUS_AGEND'       => $request->input('status'),
                'ID_AGEND_REMARCADO' => null,
                'RECORRENCIA'        => 'pontual',
                'VALOR_AGEND'        => $valor_convert,
                'OBSERVACOES'        => $request->input('obs'),
                'LOCAL'              => $descricaoLocal,
            ]);

            DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                'ID_AGENDAMENTO' => $idAgendamento,
                'ID_BOX'         => $idBox,
                'DISCIPLINA'     => $disciplina,
            ]);
        }

        return redirect()->back()->with('success', 'Agendamento realizado com sucesso!');
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
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'A.ID_AGENDAMENTO')
            ->join('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->where('a.ID_AGENDAMENTO', $agendaId)->first();

        if (!$agenda) {
            abort(404);
        }

        return view('odontologia.create_agenda', compact('agenda'));
    }

    public function createService(Request $request)
    {
        $disciplines = $request->input('disciplines');

        $idService = DB::table('FAESA_CLINICA_SERVICO')->insertGetId([
            'ID_CLINICA' => 2,
            'SERVICO_CLINICA_DESC' => $request->input('descricao'),
            'COD_INTERNO_SERVICO_CLINICA' => 0,
            'VALOR_SERVICO' => $request->input('valor'),
            'ATIVO' => $request->input('ativo')
        ]);

        foreach ($disciplines as $disciplina) {
            DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')->insert([
                'ID_SERVICO_CLINICA' => $idService,
                'DISCIPLINA' => $disciplina
            ]);
        }

        return redirect()->back()->with('success', 'Serviço cadastrado com sucesso!');
    }

    public function editService($idService)
    {
        $servico = DB::table('FAESA_CLINICA_SERVICO')->where('ID_SERVICO_CLINICA', $idService)->first();

        if (!$servico) {
            return redirect('/odontologia/consultarservico')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_service', compact('servico'));
    }


    public function createBox(Request $request)
    {
        $idBox = DB::table('FAESA_CLINICA_BOXES')->insertGetId([
            'ID_CLINICA' => 2,
            'DESCRICAO' => $request->input('descricao'),
            'ATIVO' => $request->input('status'),
        ]);
        return redirect()->back()->with('success', 'Box cadastrado com sucesso!');
    }

    public function editBox($idBox)
    {
        $box = DB::table('FAESA_CLINICA_BOXES')->where('ID_BOX_CLINICA', $idBox)->first();

        if (!$box) {
            return redirect('odontologia/consultarbox')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_box', compact('box'));
    }

    public function createBoxDiscipline(Request $request)
    {
        $boxes = $request->input('boxes');
   
        if (!$boxes || count($boxes) === 0) {
            return redirect()->back()->with('error', 'Nenhum box foi selecionado.');
        }

        foreach ($boxes as $boxId) {
            DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insertGetId([
                'ID_CLINICA' => 2,
                'ID_BOX' => $boxId,
                'DISCIPLINA' => $request->input('disciplina'),
                'DIA_SEMANA' => $request->input('dia_semana'),
                'HR_INICIO' => $request->input('hr_inicio'),
                'HR_FIM' => $request->input('hr_fim'),
                'DT_CADASTRO' => now(),

            ]);
        }
        return redirect()->back()->with('success', ' Disciplina e dia da semana vinculado a um ou mais boxes!');
    }

    public function editBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        $BoxDiscipline = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)->first();

        if (!$BoxDiscipline) {
            return redirect('odontologia/criarboxdisciplina')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_box_discipline', compact('BoxDiscipline'));
    }

    public function defineLocalAtendimento($agendaId, $boxId)
    {
        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->inserGetId([
            'ID_AGENDAMENTO' => $agendaId,
            'ID_BOX' => $boxId
        ]);
    }
}
