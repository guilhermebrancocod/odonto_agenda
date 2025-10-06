<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncaminhamentoController extends Controller
{

    public function consultaEncaminhamentos(Request $request)
    {
        $q = trim((string) $request->input('query', ''));

        $lista = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')
            ->select('ID', 'ID_AGENDAMENTO', 'DISCIPLINA', 'STATUS')
            ->where('STATUS', '=', 'DISPONIVEL') // confirme se é DISPONIVEL ou ATIVO
            ->when($q !== '', fn($qb) => $qb->where('DISCIPLINA', 'like', "%{$q}%"))
            ->orderBy('DISCIPLINA')
            ->limit(200)
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($lista);
        }

        return view('odontologia/encaminhamentos', [
            'listaEncaminhamentos' => $lista
        ]);
    }

    public function infoEncaminhamentos($id)
    {
        $enc = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO as E')
            ->join('FAESA_CLINICA_AGENDAMENTO as A', 'A.ID', '=', 'E.ID_AGENDAMENTO') // origem
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as AL', 'AL.ALUNO', '=', 'A.ID_PACIENTE') // se fizer sentido
            ->selectRaw("
                E.ID,
                E.ID_AGENDAMENTO,
                E.DISCIPLINA,
                E.STATUS,
                A.DT_AGEND,
                A.HR_AGEND_INI,
                A.HR_AGEND_FIN,
                A.ID_PACIENTE,
                AL.NOME_COMPL as NOME_PACIENTE
            ")
            ->where('E.ID', $id)
            ->first();

        abort_unless($enc, 404);

        return response()->json($enc);
    }

    public function gerarAgendamento($id, Request $request)
    {
        // Validação básica do mini-form
        $data = $request->validate([
            'prof_destino' => 'required|integer',
            'box'          => 'nullable|string|max:20',
            'procedimento' => 'nullable|string|max:100',
            'obs'          => 'nullable|string|max:500',
            // horários/ data do novo agendamento (se você já decidir aqui)
            'dt_agend'     => 'required|date',        // ex.: 2025-10-01
            'hr_ini'       => 'required|date_format:H:i',
            'hr_fim'       => 'required|date_format:H:i|after:hr_ini',
        ]);

        // Carrega encaminhamento + checagem de status
        $enc = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')
            ->where('ID', $id)->first();
        abort_unless($enc, 404);

        if ($enc->STATUS !== 'DISPONIVEL') {
            return response()->json(['error' => 'Encaminhamento não está disponível.'], 409);
        }

        // Dados do agendamento de origem (para herdar paciente, etc.)
        $orig = DB::table('FAESA_CLINICA_AGENDAMENTO')->where('ID', $enc->ID_AGENDAMENTO)->first();
        abort_unless($orig, 422, 'Agendamento de origem não encontrado.');

        $novoId = null;

        DB::transaction(function () use ($request, $data, $enc, $orig, &$novoId) {
            // 1) Cria o agendamento destino
            $novoId = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                'ID_CLINICA'        => $orig->ID_CLINICA ?? 2,
                'ID_PACIENTE'       => $orig->ID_PACIENTE,
                'ID_SERVICO'        => $orig->ID_SERVICO ?? null, // ajuste se usa serviço por disciplina
                'DT_AGEND'          => $data['dt_agend'],
                'HR_AGEND_INI'      => $data['hr_ini'],
                'HR_AGEND_FIN'      => $data['hr_fim'],
                'STATUS_AGEND'      => 'Agendado',
                'ID_AGEND_REMARCADO' => null,
                'RECORRENCIA'       => '1',
                'VALOR_AGEND'       => null,
                'OBSERVACOES'       => $data['obs'] ?? null,
                'LOCAL'             => $data['box'] ?? null,
                'MENSAGEM'          => null,
                'STATUS_PAG'        => null,
                'VALOR_PAG'         => null,
                'DT_AGEND_FINAL'    => $data['dt_agend'],
                'ID_ALUNO'          => null,
                'ID_SALA'           => null,
                /*'ID_USUARIO'        => auth()->id() ?? null,*/
                'CREATED_AT'        => now(),
                'UPDATED_AT'        => now(),
            ]);

            // (Opcional) registrar local em tabela de locais
            // DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
            //     'ID_AGENDAMENTO' => $novoId,
            //     'ID_BOX'         => $data['box'] ?? null,
            //     'DISCIPLINA'     => $enc->DISCIPLINA,
            //     'TURMA'          => null,
            // ]);

            // 2) Atualiza encaminhamento → ACEITO e linka o novo agendamento
            DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')
                ->where('ID', $enc->ID)
                ->update([
                    'STATUS'                 => 'ACEITO',
                    'ID_AGENDAMENTO_DESTINO' => $novoId,      // se você criou essa coluna
                    'PROF_DESTINO'           => $data['prof_destino'] ?? null,
                    'UPDATED_AT'             => now(),
                ]);
        });

        return response()->json([
            'ok'                     => true,
            'STATUS'                 => 'ACEITO',
            'ID_AGENDAMENTO_DESTINO' => $novoId,
        ]);
    }

    public function boxesPorDisciplina($disciplina)
    {
        // Se também filtra por turma/clinica, pegue do request e inclua no WHERE/ON
        // $turma = request('turma');
        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as BD')
            ->join('FAESA_CLINICA_BOXES as B', 'B.ID_BOX_CLINICA', '=', 'BD.ID_BOX')
            ->where('BD.DISCIPLINA', '=', $disciplina)
            // ->when($turma, fn($q) => $q->where('BD.TURMA', $turma))
            ->select('B.ID_BOX_CLINICA as id', 'B.DESCRICAO as text') // Select2-friendly
            ->distinct()
            ->orderBy('B.DESCRICAO')
            ->get();

        return response()->json($rows);
    }

    public function diaSemanaPorDisciplina($disciplina)
    {
        // Exemplo: se houver tabela de procedimentos atrelada à disciplina
        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as BD')
            ->where('BD.DISCIPLINA', '=', $disciplina)
            ->select('BD.DIA_SEMANA')
            ->distinct()
            ->get();

        return response()->json($rows);
    }

    public function turmasPorDisciplina($disciplina)
    {
        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as BD')
            ->where('BD.DISCIPLINA', '=', $disciplina)
            ->select('BD.TURMA')
            ->distinct()
            ->get();

        return response()->json($rows);
    }

    public function alunos($disciplina, $turma, $box)
    {
        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as BDA')
            ->join('FAESA_CLINICA_BOX_DISCIPLINA as BD', 'BD.ID_BOX_DISCIPLINA', '=', 'BDA.ID_BOX_DISCIPLINA')
            ->where('BD.DISCIPLINA', $disciplina)
            ->where('BD.TURMA', $turma)
            ->where('BD.ID_BOX', $box)
            ->distinct()
            ->orderBy('BDA.ALUNO')
            ->pluck('BDA.ALUNO') // só os IDs
            ->map(fn($id) => ['id' => (string)$id, 'text' => (string)$id])
            ->values();

        return response()->json($rows);
    }
}
