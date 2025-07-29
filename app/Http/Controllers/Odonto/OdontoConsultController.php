<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

class OdontoConsultController extends Controller
{

    public function fSelectPatient(Request $request)
    {
        $query_patient = $request->input('search-input');

        $selectPatient = DB::table('FAESA_CLINICA_PACIENTE')
            ->select('NOME_COMPL_PACIENTE')
            ->where(function ($query) use ($query_patient) {
                $query->where('NOME_COMPL_PACIENTE', 'like', '%' . $query_patient . '%')
                    ->orWhere('CPF_PACIENTE', 'like', '%' . $query_patient . '%');
            })
            ->get();

        return view('odontologia/consult_patient', compact('selectPatient', 'query_patient'));
    }

    public function buscarPacientes(Request $request)
    {
        $query = $request->input('query');

        $pacientes = DB::table('FAESA_CLINICA_PACIENTE')
            ->select('ID_PACIENTE', 'NOME_COMPL_PACIENTE', 'CPF_PACIENTE', 'E_MAIL_PACIENTE', 'FONE_PACIENTE')
            ->where('NOME_COMPL_PACIENTE', 'like', '%' . $query . '%')
            ->orWhere('CPF_PACIENTE', 'like', '%' . $query . '%')
            ->limit(10)
            ->get(['ID_PACIENTE', 'NOME_COMPL_PACIENTE']);

        return response()->json($pacientes);
    }

    public function fSelectAgenda(Request $request)
    {
        $query_agenda = $request->input('search-input');

        $selectAgenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->select('FAESA_CLINICA_PACIENTE.NOME_COMPL_PACIENTE')
            ->where(function ($query) use ($query_agenda) {
                $query->where('FAESA_CLINICA_PACIENTE.NOME_COMPL_PACIENTE', 'like', '%' . $query_agenda . '%')
                    ->orWhere('FAESA_CLINICA_PACIENTE.CPF_PACIENTE', 'like', '%' . $query_agenda . '%')
                    ->where('FAESA_CLINICA_AGENDAMENTO.ID_CLINICA', '=', 2);
            })
            ->get();

        return view('odontologia/consult_agenda', compact('selectAgenda', 'query_agenda'));
    }


    public function buscarAgendamentos(Request $request)
    {
        $pacienteId = $request->input('pacienteId');

        $query = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->select(
                'a.ID_AGENDAMENTO',
                'a.DT_AGEND',
                'a.HR_AGEND_INI',
                'a.HR_AGEND_FIN',
                'a.ID_SERVICO',
                's.SERVICO_CLINICA_DESC',
                'p.ID_PACIENTE',
                'p.NOME_COMPL_PACIENTE',
                'p.E_MAIL_PACIENTE',
                'p.FONE_PACIENTE'
            )
            ->where('a.ID_CLINICA', '=', 2)
            ->orderByDesc('a.DT_AGEND');

        if ($pacienteId) {
            $query->where('a.ID_PACIENTE', $pacienteId);
        }

        $agendamentos = $query->get();

        return response()->json($agendamentos);
    }

    public function buscarBoxes(Request $request)
    {
        $boxesId = $request->input('boxesId');

        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select(
                'DESCRICAO',
                'ATIVO',
                'ID_BOX_CLINICA'
            )
            ->where('ID_CLINICA', '=', 2);

        if ($boxesId) {
            $query->where('ID_BOX_CLINICA', $boxesId);
        }

        $boxes = $query->get();

        return response()->json($boxes);
    }

    public function buscarBoxeDisciplinas(Request $request)
    {
        $disciplines = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES', 'FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->select('FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX_DISCIPLINA',
                'FAESA_CLINICA_BOXES.ID_BOX_CLINICA',
                'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX',
                'FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA',
                'FAESA_CLINICA_BOXES.DESCRICAO',
                'FAESA_CLINICA_BOX_DISCIPLINA.DIA_SEMANA',
                'FAESA_CLINICA_BOX_DISCIPLINA.HR_INICIO',
                'FAESA_CLINICA_BOX_DISCIPLINA.HR_FIM')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->get();

        return response()->json($disciplines);
    }

    public function boxesDisciplina($discipline)
    {

        $boxes = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES', 'FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->select('FAESA_CLINICA_BOXES.ID_BOX_CLINICA', 'FAESA_CLINICA_BOXES.DESCRICAO','FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', trim($discipline))
            ->get();

        return response()->json($boxes);
    }

    public function listaPacienteId($pacienteId = null)
    {
        $query = DB::table('FAESA_CLINICA_PACIENTE')
            ->select('ID_PACIENTE', 'CPF_PACIENTE', 'NOME_COMPL_PACIENTE', 'E_MAIL_PACIENTE', 'FONE_PACIENTE');

        if ($pacienteId) {
            $paciente = $query->where('ID_PACIENTE', $pacienteId)->first();

            if (!$paciente) {
                return response()->json(['erro' => 'Paciente não encontrado'], 404);
            }

            return response()->json($paciente);
        }

        // Se $pacienteId for vazio, retorna todos os pacientes
        $pacientes = $query->get();
        return response()->json($pacientes);
    }

    public function listaServicosId($servicoId = null)
    {
        $query = DB::table('FAESA_CLINICA_SERVICO')
            ->select('ID_SERVICO_CLINICA', 'SERVICO_CLINICA_DESC');

        if ($servicoId) {
            $servico = $query->where('ID_SERVICO_CLINICA', $servicoId)->first();

            if (!$servico) {
                return response()->json(['erro' => 'Serviço não encontrado'], 404);
            }

            return response()->json($servico);
        }

        // Se $pacienteId for vazio, retorna todos os pacientes
        $servicos = $query->get();
        return response()->json($servicos);
    }

    public function services(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_SERVICO as s')
            ->join('FAESA_CLINICA_SERVICO_DISCIPLINA as sd', 'sd.ID_SERVICO_CLINICA', '=', 's.ID_SERVICO_CLINICA')
            ->select('s.ID_SERVICO_CLINICA', 's.SERVICO_CLINICA_DESC', 'sd.DISCIPLINA');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('s.SERVICO_CLINICA_DESC', 'like', '%' . $search . '%');
        }

        $servicos = $query->get();

        return response()->json($servicos);
    }

    public function listaAgendamentoId($pacienteId)
    {
        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO', 'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA', '=', 'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO')
            ->select(
                'FAESA_CLINICA_PACIENTE.ID_PACIENTE',
                'FAESA_CLINICA_PACIENTE.CPF_PACIENTE',
                'FAESA_CLINICA_PACIENTE.NOME_COMPL_PACIENTE',
                'FAESA_CLINICA_PACIENTE.E_MAIL_PACIENTE',
                'FAESA_CLINICA_PACIENTE.FONE_PACIENTE',
                'FAESA_CLINICA_AGENDAMENTO.ID_AGENDAMENTO',
                'FAESA_CLINICA_AGENDAMENTO.DT_AGEND',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_INI',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_FIN',
                'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO',
                'FAESA_CLINICA_SERVICO.SERVICO_CLINICA_DESC'
            )
            ->where('FAESA_CLINICA_PACIENTE.ID_PACIENTE', $pacienteId)
            ->first();

        if (!$agenda) {
            return response()->json(['erro' => 'Paciente não encontrado'], 404);
        }

        return response()->json($agenda);
    }


    public function editarPaciente($pacienteId)
    {
        $paciente = DB::table('FAESA_CLINICA_PACIENTE')->where('id', $pacienteId)->first();

        if (!$paciente) {
            abort(404);
        }

        return view('createPatient', compact('paciente'));
    }

    public function getAgendamentos(Request $request)
    {
        // Você pode usar os parâmetros se quiser filtrar:
        $start = $request->query('start');
        $end = $request->query('end');

        // Consulta os dados do banco
        $agendamentos = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->select(
                'FAESA_CLINICA_AGENDAMENTO.ID_AGENDAMENTO as id',
                'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO as servicoId',
                'FAESA_CLINICA_AGENDAMENTO.DT_AGEND',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_INI',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_FIN',
                'FAESA_CLINICA_AGENDAMENTO.OBSERVACOES',
                'FAESA_CLINICA_AGENDAMENTO.STATUS_AGEND',
                'FAESA_CLINICA_AGENDAMENTO.LOCAL',
                'FAESA_CLINICA_PACIENTE.NOME_COMPL_PACIENTE as paciente'
            )
            ->where('FAESA_CLINICA_AGENDAMENTO.ID_CLINICA', '=', 2)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('FAESA_CLINICA_AGENDAMENTO.DT_AGEND', [$start, $end]);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'servicoId' => $item->servicoId,
                    'title' => $item->paciente,
                    'start' => $item->DT_AGEND . 'T' . substr($item->HR_AGEND_INI, 0, 5),
                    'end' => $item->DT_AGEND . 'T' . substr($item->HR_AGEND_FIN, 0, 5),
                    'color' => match ($item->STATUS_AGEND) {
                        0 => '#007bff',
                        1 => '#dc3545',
                        2 => '#28a745',
                        default => '#6c757d',
                    },
                    'extendedProps' => [
                        'observacoes' => $item->OBSERVACOES,
                        'status' => $item->STATUS_AGEND,
                        'local' => $item->LOCAL
                    ]
                ];
            });

        return response()->json($agendamentos);
    }

    public function fSelectService(Request $request)
    {
        $query_servico = $request->input('search-input');

        $selectService = DB::table('FAESA_CLINICA_SERVICO')
            ->select('SERVICO_CLINICA_DESC')
            ->where(function ($query) use ($query_servico) {
                $query->where('SERVICO_CLINICA_DESC', 'like', '%' . $query_servico . '%');
            })
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return view('odontologia/consult_servico', compact('selectService', 'query_servico'));
    }

    public function buscarServicos(Request $request)
    {
        $query = $request->input('query');

        $servicos =  DB::table('FAESA_CLINICA_SERVICO')
            ->select(
                'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA',
                'SERVICO_CLINICA_DESC',
                'VALOR_SERVICO',
                'FAESA_CLINICA_SERVICO_DISCIPLINA.DISCIPLINA',
                'ATIVO'
            )
            ->leftJoin(
                'FAESA_CLINICA_SERVICO_DISCIPLINA',
                'FAESA_CLINICA_SERVICO_DISCIPLINA.ID_SERVICO_CLINICA',
                '=',
                'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA'
            )
            ->where('SERVICO_CLINICA_DESC', 'like', '%' . $query . '%')
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return response()->json($servicos);
    }

    public function fSelectBox(Request $request)
    {
        $query_box = $request->input('search-input');

        $selectBox = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO')
            ->where(function ($query) use ($query_box) {
                $query->where('DESCRICAO', 'like', '%' . $query_box . '%');
            })
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return view('odontologia/consult_box', compact('selectBox', 'query_box'));
    }

    public function getDisciplinas(Request $request)
    {
        $query = DB::connection('sqlsrv2')
            ->table('LY_MATRICULA')
            ->join('LY_ALUNO', 'LY_MATRICULA.ALUNO', '=', 'LY_ALUNO.ALUNO')
            ->join('LY_CURSO', 'LY_ALUNO.CURSO', '=', 'LY_CURSO.CURSO')
            ->join('LY_DISCIPLINA', 'LY_MATRICULA.DISCIPLINA', '=', 'LY_DISCIPLINA.DISCIPLINA')
            ->select('LY_MATRICULA.DISCIPLINA', 'LY_DISCIPLINA.NOME')
            ->distinct()
            ->where('LY_ALUNO.CURSO', '=', '2009')
            ->where('LY_MATRICULA.SIT_MATRICULA', '=', 'MATRICULADO')
            ->where('LY_CURSO.FACULDADE', '=', 'AEV')
            ->where(function ($query) {
                $query->where('LY_DISCIPLINA.TIPO', '=', 'PRATICA')
                    ->orWhere('LY_DISCIPLINA.TIPO', '=', 'TEOPRA');
            });

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('LY_MATRICULA.DISCIPLINA', 'like', '%' . $search . '%');
        }

        $disciplinas = $query->get();

        return response()->json($disciplinas);
    }

    public function getBoxes(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA')
            ->where('ATIVO', '=', 'S');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('FAESA_CLINICA_BOXES.DESCRICAO', 'like', '%' . $search . '%');
        }

        $boxes = $query->get();

        return response()->json($boxes);
    }

    public function fSelectBoxDiscipline(Request $request)
    {
        $query_box_discipline = $request->input('search-input');

        $selectBoxDiscipline = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->select('ID_BOX', 'DISCIPLINA', 'DIA_SEMANA', 'HR_INICIO', 'HR_FIM')
            ->where(function ($query) use ($query_box_discipline) {
                $query->where('ID_BOX', 'like', '%' . $query_box_discipline . '%');
                $query->where('DISCIPLINA', 'like', '%' . $query_box_discipline . '%');
                $query->where('DIA_SEMANA', 'like', '%' . $query_box_discipline . '%');
                $query->where('HR_INICIO', 'like', '%' . $query_box_discipline . '%');
                $query->where('HR_FIM', 'like', '%' . $query_box_discipline . '%');
            })
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return view('odontologia/consult_box_discipline', compact('selectBoxDiscipline', 'query_box_discipline'));
    }

    public function getBoxeServicos($servicoId)
    {

        // Busca a disciplina associada ao serviço
        $query_servico = DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
            ->join('FAESA_CLINICA_SERVICO', 'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA', '=', 'FAESA_CLINICA_SERVICO_DISCIPLINA.ID_SERVICO_CLINICA')
            ->select('FAESA_CLINICA_SERVICO_DISCIPLINA.DISCIPLINA')
            ->where('FAESA_CLINICA_SERVICO.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA', '=', $servicoId)
            ->first();

        if (!$query_servico) {
            return response()->json([], 404);
        }

        $disciplina = $query_servico->DISCIPLINA;

        // Busca os boxes compatíveis com a disciplina
        $boxes = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES', 'FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->select('DESCRICAO', 'ATIVO', 'ID_BOX_CLINICA')
            ->where('FAESA_CLINICA_BOXES.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', '=', $disciplina)
            ->get();

        return response()->json($boxes);
    }
}
