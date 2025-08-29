<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'A.ID_AGENDAMENTO')
            ->join('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->select(
                'a.ID_AGENDAMENTO',
                'a.DT_AGEND',
                'a.HR_AGEND_INI',
                'a.HR_AGEND_FIN',
                'a.ID_SERVICO',
                's.SERVICO_CLINICA_DESC',
                'la.ID_BOX',
                'cb.DESCRICAO',
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
        $disciplines = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as bd')
            ->join('FAESA_CLINICA_BOXES as b', 'b.ID_BOX_CLINICA', '=', 'bd.ID_BOX')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as ld', 'ld.DISCIPLINA', '=', 'bd.DISCIPLINA')
            ->select(
                'bd.ID_BOX_DISCIPLINA',
                'ld.NOME',
                'b.ID_BOX_CLINICA',
                'bd.ID_BOX',
                'bd.TURMA',
                'bd.DISCIPLINA',
                'b.DESCRICAO',
                'bd.DIA_SEMANA',
                'bd.HR_INICIO',
                'bd.HR_FIM'
            )
            ->where('bd.ID_CLINICA', '=', 2)
            ->get();

        return response()->json($disciplines);
    }

    public function boxesDisciplina($discipline)
    {
        $boxes = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES', 'FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->select('FAESA_CLINICA_BOXES.ID_BOX_CLINICA', 'FAESA_CLINICA_BOXES.DESCRICAO', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', trim($discipline))
            ->get();

        return response()->json($boxes);
    }

    public function getHorariosBoxDisciplinas($discipline)
    {
        $horarios = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->select('')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', trim($discipline))
            ->get();

        return response()->json($horarios);
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

    public function listaServicosId(int $servicoId)
    {
        // serviço (sem depender de disciplina)
        $row = DB::table('FAESA_CLINICA_SERVICO as s')
            ->leftJoin('FAESA_CLINICA_SERVICO_DISCIPLINA as sd', 'sd.ID_SERVICO_CLINICA', '=', 's.ID_SERVICO_CLINICA')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as ld', 'ld.DISCIPLINA', '=', 'sd.DISCIPLINA')
            ->where('s.ID_SERVICO_CLINICA', $servicoId)
            ->select(
                's.ID_SERVICO_CLINICA as id',
                's.SERVICO_CLINICA_DESC as descricao',
                's.VALOR_SERVICO as valor',
                's.ATIVO as ativo',
                'sd.DISCIPLINA as disciplina_codigo',
                'ld.NOME as disciplina_nome'
            )
            ->orderBy('ld.NOME')   // define um critério para qual disciplina pegar
            ->limit(1)             // garante apenas UMA disciplina
            ->first();

        if (!$row) {
            return response()->json(['erro' => 'Serviço não encontrado'], 404);
        }
        return response()->json([
            'id'                => $row->id,
            'descricao'         => $row->descricao,
            'valor'             => $row->valor,
            'ativo'             => $row->ativo,
            'disciplina_codigo' => $row->disciplina_codigo, // pode vir null se não houver
            'disciplina_nome'   => $row->disciplina_nome,   // pode vir null se não houver
        ]);
    }

    public function procedimento(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_SERVICO')
            ->select('ID_SERVICO_CLINICA', 'SERVICO_CLINICA_DESC')
            ->where('ID_CLINICA', '=', 2)
            ->where('ATIVO', '=', 'S');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('SERVICO_CLINICA_DESC', 'like', '%' . $search . '%');
        }

        $procedimentos = $query->get();

        return response()->json($procedimentos);
    }

    public function listaAgendamentoId($pacienteId)
    {
        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->leftjoin('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->leftjoin('FAESA_CLINICA_SERVICO', 'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA', '=', 'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO')
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
            ->where('FAESA_CLINICA_AGENDAMENTO.ID_CLINICA', '=', 2)
            ->get();

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
        $turma = $request->query('TURMA') ?? $request->query('turma');

        // Consulta os dados do banco
        $agendamentos = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO', 'FAESA_CLINICA_AGENDAMENTO.ID_AGENDAMENTO', '=', 'FAESA_CLINICA_LOCAL_AGENDAMENTO.ID_AGENDAMENTO')
            ->select(
                'FAESA_CLINICA_AGENDAMENTO.ID_AGENDAMENTO as id',
                'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO as servicoId',
                'FAESA_CLINICA_LOCAL_AGENDAMENTO.TURMA',
                'FAESA_CLINICA_AGENDAMENTO.MENSAGEM',
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
            ->when(!empty($turma), function ($q) use ($turma) {
                $q->where('FAESA_CLINICA_LOCAL_AGENDAMENTO.TURMA', $turma);
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
                        'mensagem' => $item->MENSAGEM,
                        'status' => $item->STATUS_AGEND,
                        'local' => $item->LOCAL,
                        'turma' => $item->TURMA
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

    public function buscarProcedimentos(Request $request)
    {
        $query = $request->input('query');

        $procedimentos =  DB::table('FAESA_CLINICA_SERVICO')
            ->select(
                'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA',
                'SERVICO_CLINICA_DESC',
                'VALOR_SERVICO',
                'ATIVO'
            )
            ->where('SERVICO_CLINICA_DESC', 'like', '%' . $query . '%')
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return response()->json($procedimentos);
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

    public function disciplinascombox(Request $request)
    {
        $disciplina = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as A')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as D', 'D.DISCIPLINA', '=', 'A.DISCIPLINA')
            ->select('D.DISCIPLINA', 'D.NOME')
            ->distinct()
            ->get();

        return response()->json($disciplina);
    }

    public function getDisciplinas(Request $request)
    {
        $turmas = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_AGENDA as A')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as D', 'D.DISCIPLINA', '=', 'A.DISCIPLINA')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_TURMA as T', function ($join) {
                $join->on('T.DISCIPLINA', '=', 'D.DISCIPLINA')
                    ->on('T.ANO', '=', 'A.ANO')
                    ->on('T.SEMESTRE', '=', 'A.SEMESTRE');
            })
            ->where('A.ANO', 2025)
            ->where('A.SEMESTRE', 2)
            ->where('T.CURSO', 2009)
            ->select('D.DISCIPLINA', 'D.NOME')
            ->distinct()
            ->get();

        return response()->json($turmas);
    }

    public function getTodasTurmas(Request $request)
    {
        $turma = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->select('TURMA')
            ->distinct()
            ->pluck('TURMA');
        return response()->json($turma);
    }

    public function getTurmas($disciplina)
    {
        $turmas = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_AGENDA as A')
            ->where('A.ANO', 2025)
            ->where('A.SEMESTRE', 2)
            ->where('A.DISCIPLINA', $disciplina)
            ->distinct()
            ->orderBy('A.TURMA')
            ->pluck('A.TURMA'); // array de strings
        return response()->json($turmas);
    }

    public function getDatasTurmaDisciplina($disciplina, $turma)
    {
        $diasemana = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_AGENDA as A')
            ->where('A.ANO', 2025)
            ->where('A.SEMESTRE', 2)
            ->where('A.DISCIPLINA', $disciplina)
            ->where('A.TURMA', $turma)
            ->distinct()
            ->orderBy('A.DIA_SEMANA')
            ->pluck('DIA_SEMANA');
        return response()->json($diasemana);
    }

    public function getHorariosDatasTurmaDisciplina($disciplina, $turma, $diasemana)
    {

        $diasemana = (int) $diasemana;
        $horarios = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_AGENDA as A')
            ->where('A.ANO', 2025)
            ->where('A.SEMESTRE', 2)
            ->where('A.DISCIPLINA', $disciplina)
            ->where('A.TURMA', $turma)
            ->where('A.DIA_SEMANA', $diasemana)
            ->selectRaw("CONVERT(varchar(5), CAST(A.HORA_INICIO as time), 108) as inicio")
            ->addSelect(DB::raw("CONVERT(varchar(5), CAST(A.HORA_FIM as time), 108) as fim"))
            ->distinct()
            ->get();
        return response()->json($horarios);
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

    public function getBoxesId($boxId, Request $request)
    {
        if (!is_numeric($boxId)) {
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA', 'ATIVO')
            ->where('ATIVO', '=', 'S')
            ->where('ID_BOX_CLINICA', '=', $boxId);

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('DESCRICAO', 'like', "%{$search}%");
        }

        $boxeId = $query->first();

        return response()->json($boxeId);
    }

    public function consultaboxdisciplina(Request $request, $idBoxDiscipline)
    {
        $query = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as bd')
            ->join('FAESA_CLINICA_BOXES as b', 'b.ID_BOX_CLINICA', '=', 'bd.ID_BOX')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline);

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('DESCRICAO', 'like', "%{$search}%");
        }

        $boxDisciplina = $query->first();

        return response()->json($boxDisciplina);
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
