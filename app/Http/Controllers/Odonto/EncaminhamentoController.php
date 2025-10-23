<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncaminhamentoController extends Controller
{

    public function consultaEncaminhamentos(Request $request)
    {
        // -------- Filtros vindos da tela --------
        $q = trim((string) ($request->input('q', $request->input('query', ''))));

        // status: aceita string ou array; default: DISPONIVEL
        $statusParam = $request->input('statusEncaminhamento', $request->input('statusEncaminhamento'));
        $status = collect(is_array($statusParam) ? $statusParam : [$statusParam])
            ->filter()
            ->map(fn($s) => strtoupper((string) $s))
            ->unique()
            ->values();

        $disciplina = $request->input('disciplina');
        $box        = $request->input('box');

        // datas (YYYY-MM-DD). Se usar pt-BR, converta antes ou use CAST.
        $data     = $request->input('data');   // data exata
        $dataDe   = $request->input('de');     // intervalo início
        $dataAte  = $request->input('ate');    // intervalo fim

        $limit = (int) $request->input('limit', 200);
        $limit = max(1, min($limit, 500)); // segurança

        // -------- Query base --------
        $qb = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO as e')
            ->leftJoin('FAESA_CLINICA_AGENDAMENTO as a', 'a.ID_AGENDAMENTO', '=', 'e.ID_AGENDAMENTO')
            ->select([
                'e.ID',
                'e.ID_AGENDAMENTO',
                'e.ID_NOVO_AGENDAMENTO',
                'e.DISCIPLINA',
                'e.STATUS',
                DB::raw('CAST(a.DT_AGEND AS date) as DATA'), // SQL Server-friendly
                'a.HR_AGEND_INI',
                'a.HR_AGEND_FIN',
            ]);

        // -------- Aplicação de filtros --------
        $qb->when($status->isNotEmpty(), fn($q2) => $q2->whereIn('e.STATUS', $status->all()));
        $qb->when($disciplina, fn($q2, $v) => $q2->where('e.DISCIPLINA', $v));
        $qb->when($box,        fn($q2, $v) => $q2->where('e.ID_BOX', $v));

        // data única
        $qb->when($data, function ($q2, $v) {
            // em SQL Server, CAST a date:
            $q2->where(DB::raw('CAST(a.DT_AGEND AS date)'), '=', $v);
        });

        // intervalo de datas
        if ($dataDe && $dataAte) {
            $qb->whereBetween(DB::raw('CAST(a.DT_AGEND AS date)'), [$dataDe, $dataAte]);
        }

        // busca livre (DISCIPLINA/TURMA/STATUS)
        $qb->when($q !== '', function ($q2) use ($q) {
            $like = "%{$q}%";
            $q2->where(function ($w) use ($like) {
                $w->where('e.DISCIPLINA', 'like', $like)
                    ->orWhere('e.STATUS', 'like', $like);
            });
        });

        $qb->orderBy('a.DT_AGEND', 'desc')
            ->orderBy('e.DISCIPLINA')
            ->limit($limit);

        // -------- Formatos de resposta --------
        // Formato Select2 (results: [{id, text, ...}])
        if ($request->boolean('select2')) {
            $items = $qb->get()->map(function ($r) {
                $periodo = trim(($r->DATA ?? '') . ' ' . ($r->HR_AGEND_INI ?? ''));
                $texto = trim(
                    implode(' • ', array_filter([
                        $r->DISCIPLINA,
                        $periodo ?: null,
                    ]))
                );
                return [
                    'id'              => $r->ID,
                    'text'            => $texto !== '' ? $texto : ('Enc. #' . $r->ID),
                    'status'          => $r->STATUS,
                    'id_agendamento'  => $r->ID_AGENDAMENTO,
                    'disciplina'      => $r->DISCIPLINA,
                    'turma'           => $r->TURMA ?? null,
                    'data'            => $r->DATA ?? null,
                    'hr_ini'          => $r->HR_AGEND_INI ?? null,
                    'hr_fim'          => $r->HR_AGEND_FIN ?? null,
                    'box'             => $r->ID_BOX ?? null,
                ];
            });

            return response()->json(['results' => $items]);
        }

        // JSON “cru” (full) para AJAX
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($qb->get());
        }

        // Render HTML normal
        $lista = $qb->get();
        return view('odontologia/encaminhamentos', [
            'listaEncaminhamentos' => $lista,
        ]);
    }

    public function infoEncaminhamentos($id)
    {
        $enc = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO as E')
            ->join('FAESA_CLINICA_AGENDAMENTO as A', 'A.ID_AGENDAMENTO', '=', 'E.ID_AGENDAMENTO')
            ->leftJoin('LYCEUM.dbo.LY_ALUNO as AL', 'AL.ALUNO', '=', 'A.ID_PACIENTE')
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
        // Validação básica
        $validated = $request->validate([
            'data'         => ['required', 'date'], // ajuste o formato se vier d/m/Y
            'box'          => ['required'],
            'turma'        => ['required'],
            'disciplina'   => ['nullable'],         // se vier do form
            'prof_destino' => ['nullable'],
            'aluno'        => ['required'],         // pode vir "123,456" | "123|456" | array
        ]);

        return DB::transaction(function () use ($id, $request, $validated) {

            // (1) Carrega e bloqueia encaminhamento para evitar corrida
            $enc = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')
                ->where('ID', $id)
                ->lockForUpdate()
                ->first();

            abort_unless($enc, 404);
            if ($enc->STATUS !== 'DISPONIVEL') {
                return response()->json(['error' => 'Encaminhamento não está disponível.'], 409);
            }

            // (2) Agendamento de origem
            $orig = DB::table('FAESA_CLINICA_AGENDAMENTO')
                ->where('ID_AGENDAMENTO', $enc->ID_AGENDAMENTO)
                ->first();

            abort_unless($orig, 422, 'Agendamento de origem não encontrado.');

            // (3) Normaliza alunos: aceita "idA,idB", "idA|idB" ou array
            $raw = $request->input('aluno');
            $ids = collect(is_array($raw) ? $raw : preg_split('/[,\|]+/', (string) $raw))
                ->filter(fn($v) => $v !== null && $v !== '')
                ->map(fn($v) => trim((string) $v))
                ->unique()
                ->values();

            if ($ids->count() !== 2) {
                return response()->json(['error' => 'Selecione uma dupla válida de alunos.'], 422);
            }

            // (4) Cria o novo agendamento
            $novoId = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                'ID_CLINICA'         => $orig->ID_CLINICA ?? 2,
                'ID_PACIENTE'        => $orig->ID_PACIENTE,
                'ID_SERVICO'         => $orig->ID_SERVICO ?? null,
                'DT_AGEND'           => $validated['data'],
                'HR_AGEND_INI'       => $orig->HR_AGEND_INI,
                'HR_AGEND_FIN'       => $orig->HR_AGEND_FIN,
                'STATUS_AGEND'       => 'ENCAMINHAMENTO',
                'ID_AGEND_REMARCADO' => null,
                'RECORRENCIA'        => '1',
                'VALOR_AGEND'        => $orig->VALOR_AGEND,
                'OBSERVACOES'        => $orig->OBSERVACOES ?? null,
                'LOCAL'              => $request->input('box') ?? null,
                'MENSAGEM'           => $orig->MENSAGEM ?? null,
                'STATUS_PAG'         => $orig->STATUS_PAG ?? null,
                'VALOR_PAG'          => $orig->VALOR_PAG ?? null,
                'DT_AGEND_FINAL'     => $validated['data'],
                'ID_ALUNO'           => $orig->ID_ALUNO ?? null,   // se não for usado aqui, pode remover
                'ID_SALA'            => $orig->ID_SALA ?? null,    // <-- corrigido
                'CREATED_AT'         => now(),
                'UPDATED_AT'         => now(),
            ]);

            // (5) Relaciona cada aluno em uma linha
            foreach ($ids as $alunoId) {
                DB::table('FAESA_CLINICA_AGENDAMENTO_ALUNO')->insert([
                    'ID_CLINICA'     => $orig->ID_CLINICA ?? 2,
                    'ID_AGENDAMENTO' => $novoId,
                    'ALUNO'          => $alunoId,
                    'ID_BOX'         => $request->input('box'),
                    'DOCENTE'        => $request->input('prof_destino') ?? null,
                    'STATUS'         => 'ATIVO',
                    'ANO_LETIVO'     => 2025, // se precisar, derive dinamicamente
                    'SEMESTRE'       => 2,
                    'CREATED_AT'     => now(),
                    'UPDATED_AT'     => now(),
                ]);
            }

            // (6) Local/box + turma/discipina
            DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                'ID_AGENDAMENTO' => $novoId,
                'ID_BOX'         => $request->input('box'),       // <-- corrigido (sem $data)
                'DISCIPLINA'     => $enc->DISCIPLINA ?? $request->input('disciplina'),
                'TURMA'          => $request->input('turma'),
            ]);

            // (7) Atualiza encaminhamento → ACEITO e relaciona destino
            DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')
                ->where('ID', $enc->ID)
                ->update([
                    'ID_AGENDAMENTO' => $orig->ID_AGENDAMENTO,
                    'STATUS'                 => 'REAGENDADO',
                    'UPDATED_AT'             => now(),
                    'CREATED_AT'             => now(),
                    'ID_NOVO_AGENDAMENTO' => $novoId,
                ]);

            return response()->json([
                'ok'                     => true,
                'STATUS'                 => 'ACEITO',
                'ID_NOVO_AGENDAMENTO' => $novoId,
            ]);
        });
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
