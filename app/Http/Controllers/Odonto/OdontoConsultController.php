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
                'la.TURMA',
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

    public function buscarUsuarios(Request $request)
    {
        $usuarioId = $request->input('userId');

        $query = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select(
                'ID',
                'NOME',
                'USUARIO',
                'PESSOA',
                'TIPO',
                'STATUS',
                'TIPO'
            )
            ->where('ID_CLINICA', '=', 2);

        if ($usuarioId) {
            $query->where('ID', $usuarioId);
        }

        $user = $query->get();

        return response()->json($user);
    }

    public function buscarUsuariosLyceum(Request $request)
    {
        $search = $request->input('query'); // termo digitado no select2

        $query = DB::table('HADES.dbo.USUARIO')
            ->select('NOMEUSUARIO', 'USUARIO');

        if (!empty($search)) {
            $query->where('NOMEUSUARIO', 'like', '%' . $search . '%');
        }

        $users = $query->limit(20)->get(); // limit pra não sobrecarregar

        return response()->json($users);
    }

    public function buscarPessoaLyceum(Request $requet, $pessoa)
    {
        $pessoa = DB::table('HADES.dbo.USUARIO')
            ->where('USUARIO', $pessoa)
            ->select('NOMEUSUARIO', 'USUARIO')
            ->first();

        if (!$pessoa) {
            return response()->json([], 404); // não encontrou
        }

        return response()->json($pessoa);
    }

    public function buscarBoxeDisciplinas(Request $request)
    {
        $disciplines = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as bd')
            ->join('FAESA_CLINICA_BOX_DISCIPLINA as d', 'd.ID_BOX_DISCIPLINA', 'bd.ID_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES as b', 'b.ID_BOX_CLINICA', '=', 'bd.ID_BOX')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as ld', 'ld.DISCIPLINA', '=', 'd.DISCIPLINA')
            ->select(
                'bd.ID_BOX_DISCIPLINA',
                'd.DISCIPLINA',
                'ld.NOME',
                'bd.ALUNO',
                'b.ID_BOX_CLINICA',
                'd.ID_BOX',
                'd.TURMA',
                'b.DESCRICAO',
            )
            ->where('d.ID_CLINICA', '=', 2)
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
            ->where('s.ID_SERVICO_CLINICA', $servicoId)
            ->select(
                's.ID_SERVICO_CLINICA as id',
                's.SERVICO_CLINICA_DESC as descricao',
                's.VALOR_SERVICO as valor',
                's.ATIVO as ativo'
            )
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

    public function selectUser(Request $request)
    {
        $query = $request->input('search-input');

        $selectUser = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select('NOME', 'USUARIO')
            ->where('ID_CLINICA', 2)
            ->where('NOME', 'like', '%' . $query . '%')
            ->get();

        return view('odontologia/consult_user', compact('selectUser', 'query'));
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
            ->where('D.TIPO', '=', 'TEOPRA')
            ->select('D.DISCIPLINA', 'D.NOME')
            ->distinct()
            ->get();

        return response()->json($turmas);
    }

    public function getTodasTurmas($disciplina)
    {
        $turma = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA')
            ->select('SUBTURMA1')
            ->where('DISCIPLINA', $disciplina)
            ->distinct()
            ->pluck('SUBTURMA1');
        return response()->json($turma);
    }

    public function getTurmasAgendadas(Request $request)
    {
        $turma = DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO as la')
            ->join('FAESA_CLINICA_AGENDAMENTO as a', 'a.ID_AGENDAMENTO', '=', 'la.ID_AGENDAMENTO')
            ->select('la.TURMA')
            ->distinct()
            ->get(['ID_AGENDAMENTO', 'TURMA']);
        return response()->json($turma);
    }

    public function getTodasTurmasSelecionada($turmaSelecionada)
    {
        $turmaSelecionada = DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO as la')
            ->join('FAESA_CLINICA_AGENDAMENTO as a', 'a.ID_AGENDAMENTO', '=', 'la.ID_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->select('p.NOME_COMPL_PACIENTE', 'p.ID_PACIENTE', 's.SERVICO_CLINICA_DESC', 'a.DT_AGEND', 'a.HR_AGEND_INI', 'a.HR_AGEND_FIN', 'la.TURMA', 'p.FONE_PACIENTE', 'a.ID_AGENDAMENTO')
            ->where('la.TURMA', $turmaSelecionada)
            ->distinct()
            ->get();
        return response()->json($turmaSelecionada);
    }

    public function getTurmas(Request $request)
    {

        $disciplina = $request->query('disciplina');
        $box  = (int) $request->query('box');

        $turmas = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('DISCIPLINA', $disciplina)
            ->where('ID_BOX', (int) $box)
            ->select('TURMA')
            ->distinct()
            ->orderBy('TURMA')
            ->pluck('TURMA');

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

    public function getAlunosDisciplinaTurma($disciplina, $turma)
    {
        $alunos = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA as M')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as A', 'A.ALUNO', '=', 'M.ALUNO')
            ->where('M.DISCIPLINA', $disciplina)
            ->where('M.SUBTURMA1', $turma)
            ->whereNotExists(function ($q) use ($disciplina, $turma) {
                $q->select(DB::raw(1))
                    ->from('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as DA')
                    ->whereColumn('DA.ALUNO', 'M.ALUNO')
                    ->where('M.DISCIPLINA', $disciplina)
                    ->where('M.SUBTURMA1', $turma);
            })
            ->select('M.ALUNO', 'A.NOME_COMPL')
            ->distinct()
            ->orderBy('A.NOME_COMPL', 'asc')
            ->get();

        return response()->json($alunos);
    }

    public function getAlunosDisciplinaTurmaAgenda($disciplina, $turma, $box)
    {

        $alunos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as BD')
            ->join('FAESA_CLINICA_BOX_DISCIPLINA as D', 'D.ID_BOX_DISCIPLINA', '=', 'BD.ID_BOX_DISCIPLINA')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as A', 'A.ALUNO', '=', 'BD.ALUNO')
            ->where('D.DISCIPLINA', $disciplina)
            ->where('D.TURMA', $turma)
            ->where('BD.ID_BOX', $box)
            ->select('BD.ALUNO', 'A.NOME_COMPL')
            ->orderBy('A.NOME_COMPL', 'asc')
            ->get();
        return response()->json($alunos);
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

    public function getUserId($userId, Request $request)
    {
        if (!is_numeric($userId)) {
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $query = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select('ID', 'NOME', 'USUARIO', 'PESSOA', 'TIPO', 'STATUS')
            ->where('STATUS', '=', 'Ativo')
            ->where('ID', '=', $userId);

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('NOME', 'like', "%{$search}%");
        }

        $userId = $query->first();

        return response()->json($userId);
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
