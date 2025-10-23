<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoxDisciplineStudentsController extends Controller
{
    public function createBoxDiscipline(Request $request)
    {
        // 1) Validação
        $data = $request->validate([
            'disciplina'  => ['required', 'string'],
            'turma'       => ['required', 'string'],
            'data'        => ['required', 'integer', 'between:1,7'],     // dia da semana
            'boxes'       => ['required', 'array', 'min:1'],
            'boxes.*'     => ['integer'],
            'horarios'    => ['required', 'array', 'min:1'],
            'horarios.*'  => ['regex:/^\d{1,2}:\d{2}$/'],              // HH:mm
            'alocacoes'   => ['required', 'array', 'min:1'],             // mapa: [boxId => [alunos...]]
            'alocacoes.*' => ['array', 'min:1'],
            'alocacoes.*.*' => ['string', 'max:20'],                    // RA/matrícula
        ], [
            'required' => 'Campo obrigatório.',
            'between'  => 'Valor inválido.',
            'regex'    => 'Horário inválido (HH:mm).',
        ]);

        // 2) Normaliza hr_ini / hr_fim a partir de horarios[]
        $toMin = function (string $h) {
            [$H, $M] = array_map('intval', explode(':', $h));
            return $H * 60 + $M;
        };
        $mins  = array_map($toMin, $data['horarios']);
        $min   = min($mins);
        $max = max($mins);
        $hrIni = sprintf('%02d:%02d', intdiv($min, 60), $min % 60) . ':00'; // -> '07:30:00'
        $hrFim = sprintf('%02d:%02d', intdiv($max, 60), $max % 60) . ':00'; // -> '09:00:00'

        // 3) Ano/Semestre (ajuste se vier do request)
        $ano = (int) now()->year;
        $sem = now()->month <= 6 ? 1 : 2;

        DB::transaction(function () use ($data, $hrIni, $hrFim, $ano, $sem) {

            $idsBoxDisc = []; // [boxId => id_box_disciplina]

            // 3.1) Garante/obtém a linha de regra para CADA box
            foreach ($data['boxes'] as $boxId) {
                $attrs = [
                    'ID_CLINICA' => 2,
                    'ID_BOX'     => (int) $boxId,
                    'DISCIPLINA' => $data['disciplina'],
                    'TURMA'      => $data['turma'],
                    'DIA_SEMANA' => (int) $data['data'],
                    'HR_INICIO'  => $hrIni,
                    'HR_FIM'     => $hrFim,
                ];

                // tenta achar a regra
                $row = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->where($attrs)->first();

                if ($row) {
                    $idBoxDisc = (int) $row->ID_BOX_DISCIPLINA;
                } else {
                    // cria e pega o ID
                    $idBoxDisc = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insertGetId(
                        $attrs + ['DT_CADASTRO' => DB::raw('SYSDATETIME()')]
                    );
                }

                $idsBoxDisc[(int)$boxId] = $idBoxDisc;
            }

            // 3.2) Grava vínculos aluno↔box_disciplina (recorrente)
            foreach ($data['alocacoes'] as $boxId => $alunos) {
                $boxId = (int) $boxId;
                if (!isset($idsBoxDisc[$boxId])) {
                    // box não listado em boxes[]: ignore ou lance erro 422
                    continue;
                }
                $idBoxDisc = $idsBoxDisc[$boxId];

                // capacidade por box (ex.: 2)
                if (count($alunos) > 2) {
                    abort(422, "Box {$boxId} excedeu capacidade (2).");
                }

                // monta linhas para upsert
                $rows = [];
                foreach ($alunos as $alunoId) {
                    $rows[] = [
                        'ID_BOX_DISCIPLINA' => $idBoxDisc,
                        'ID_BOX'            => $boxId,
                        'ALUNO'             => (string) $alunoId,
                        'ANO'               => $ano,
                        'SEMESTRE'          => $sem,
                        'STATUS'            => 'ATIVO',
                        'CREATED_AT'        => DB::raw('SYSDATETIME()'),
                        'UPDATED_AT'        => DB::raw('SYSDATETIME()'),
                    ];
                }

                // upsert garante 1 linha por (regra, aluno, ano, semestre)
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')->upsert(
                    $rows,
                    ['ID_BOX_DISCIPLINA', 'ALUNO', 'ANO', 'SEMESTRE'],
                    ['STATUS', 'UPDATED_AT']
                );
            }
        });

        return back()->with('success', 'Vínculos salvos com sucesso!');
    }

    public function editBoxDiscipline(Request $request, int $idBoxDiscipline)
    {
        // 1) REGRA (linha única na tabela base)
        $regra = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->first();

        if (!$regra) {
            return redirect('odontologia/criarboxdisciplina')
                ->with('error', 'Serviço não encontrado.');
        }

        // filtros opcionais
        $ano = $request->integer('ano');
        $sem = $request->integer('semestre');

        // 2) ALUNOS vinculados (recorrência)
        $alunos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as BDA')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as A', 'A.ALUNO', '=', 'BDA.ALUNO')
            ->where('BDA.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->when($ano, fn($q) => $q->where('BDA.ANO', $ano))
            ->when($sem, fn($q) => $q->where('BDA.SEMESTRE', $sem))
            ->where('BDA.STATUS', '=', 'ATIVO')
            ->select('BDA.ALUNO', 'A.NOME_COMPL', 'BDA.ANO', 'BDA.SEMESTRE')
            ->orderBy('A.NOME_COMPL')
            ->get();

        $nome = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as BD')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.ly_disciplina as D', 'D.DISCIPLINA', '=', 'BD.DISCIPLINA')
            ->where('BD.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->value('D.NOME');

        $alunosIds = $alunos->pluck('ALUNO')->all();

        // 3) Empacota tudo numa única var pra view
        $boxDiscipline = (object) [
            'regra'       => $regra,                       // tem DISCIPLINA, TURMA, DIA_SEMANA, HR_INI/HR_FIM, ID_BOX etc.
            'alunos'      => $alunos,                      // lista com nome
            'alunosIds'   => $alunosIds,                   // só ids (pra marcar checkboxes)
            'disciplina'  => $regra->DISCIPLINA,
            'nome'        => $nome,
            'turma'       => $regra->TURMA,
            'dia_semana'  => $regra->DIA_SEMANA,
            // cuidado com o nome das colunas no seu banco: use HR_INI/HR_FIM OU HR_INICIO/HR_FIM conforme existir
            'hr_ini'      => $regra->HR_INI   ?? $regra->HR_INICIO ?? null,
            'hr_fim'      => $regra->HR_FIM   ?? null,
            'box_id'      => $regra->ID_BOX,
            'ano'         => $ano,
            'semestre'    => $sem,
            'disciplina_nome' => $nome
        ];

        return view('odontologia.create_box_discipline', compact('boxDiscipline'));
    }

    public function updateBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        // ---------- Entrada ----------
        $disciplina        = $request->input('disciplina');
        $turma             = $request->input('turma');
        $diaSemana         = $request->input('data');
        $boxesSelecionados = collect((array)$request->input('boxes', []))
            ->map(fn($v) => (string)$v)->unique()->values()->all();

        // Estado final desejado: alocacoes[BOX_ID][]=ALUNO_ID
        // (Se você ainda envia "alocados" + "alunos", veja bloco de merge logo abaixo)
        $boxes = collect((array)$request->input('boxes', []))
            ->map(fn($v) => (string)$v)
            ->unique()
            ->values()
            ->all();

        // Box ativo: preferencialmente do request, senão o primeiro de boxes[]
        $boxAtivo = (string) $request->input('boxAtivo') ?: ($boxes[0] ?? null);

        // Alunos novos
        $novosAlunos = collect((array)$request->input('alunos', []))
            ->map(fn($v) => (string)$v)
            ->unique()
            ->values()
            ->all();

        // Alocações completas, se existirem
        $alocacoes = collect((array)$request->input('alocacoes', []))
            ->map(fn($ids) => collect($ids)->map(fn($v) => (string)$v)->unique()->values()->all())
            ->all();

        // Se alocacoes estiverem vazias, cria usando alunos + boxAtivo
        if (empty($alocacoes) && $boxAtivo && count($novosAlunos)) {
            $alocacoes[$boxAtivo] = $novosAlunos;
        }

        // Se alocacoes vieram, mas boxAtivo + alunos também foram enviados,
        // mescla para garantir que os novos sejam inseridos corretamente.
        if (!empty($alocacoes) && $boxAtivo && count($novosAlunos)) {
            $atual = $alocacoes[$boxAtivo] ?? [];
            $alocacoes[$boxAtivo] = array_values(array_unique(array_merge($atual, $novosAlunos)));
        }

        // CAP por box (ajuste se necessário)
        $CAP = 2;

        // ---------- Horários ----------
        $hrIni = $request->input('hr_ini'); // "HH:MM"
        $hrFim = $request->input('hr_fim'); // "HH:MM"
        $hrIni = $hrIni ? Carbon::createFromFormat('H:i', substr($hrIni, 0, 5))->format('H:i:s') : null;
        $hrFim = $hrFim ? Carbon::createFromFormat('H:i', substr($hrFim, 0, 5))->format('H:i:s') : null;

        DB::transaction(function () use (
            $idBoxDiscipline,
            $disciplina,
            $turma,
            $diaSemana,
            $boxesSelecionados,
            $alocacoes,
            $hrIni,
            $hrFim,
            $CAP
        ) {
            // ---------- Boxes antigos ----------
            $boxesAntigos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                ->pluck('ID_BOX')
                ->map(fn($v) => (string)$v)
                ->toArray();

            $boxesParaAdicionar = array_values(array_diff($boxesSelecionados, $boxesAntigos));
            $boxesParaRemover   = array_values(array_diff($boxesAntigos, $boxesSelecionados));

            // ---------- Remover boxes desmarcados + alocações destes boxes ----------
            if (!empty($boxesParaRemover)) {
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                    ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                    ->whereIn('ID_BOX', $boxesParaRemover)
                    ->delete();

                DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')
                    ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                    ->whereIn('ID_BOX', $boxesParaRemover)
                    ->delete();
            }

            // ---------- Inserir boxes novos (FALTAVA ID_BOX_DISCIPLINA) ----------
            foreach ($boxesParaAdicionar as $boxId) {
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insert([
                    'ID_BOX_DISCIPLINA' => $idBoxDiscipline,
                    'ID_CLINICA'        => 2,
                    'ID_BOX'            => $boxId,
                    'DISCIPLINA'        => $disciplina,
                    'TURMA'             => $turma,
                    'DIA_SEMANA'        => $diaSemana,
                    'HR_INICIO'         => $hrIni,
                    'HR_FIM'            => $hrFim,
                    'DT_CADASTRO'       => now(),
                ]);
            }

            // ---------- Atualizar campos comuns ----------
            DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                ->update([
                    'DISCIPLINA'  => $disciplina,
                    'TURMA'       => $turma,
                    'DIA_SEMANA'  => $diaSemana,
                    'HR_INICIO'   => $hrIni,
                    'HR_FIM'      => $hrFim,
                    'DT_ALTERACAO' => now(),
                ]);

            // ---------- Sincronizar ALUNOS por box ----------
            // 1) Carrega alocações existentes
            $exist = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')
                ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                ->get()
                ->groupBy('ID_BOX')
                ->map(fn($g) => $g->pluck('ALUNO')->map(fn($v) => (string)$v)->values()->all())
                ->all();

            // 2) Regras de negócio no server:
            // 2a) CAP
            /*foreach ($alocacoes as $boxId => $ids) {
                if (count($ids) > $CAP) {
                    throw ValidationException::withMessages([
                        'alocacoes' => "Box {$boxId} excede a capacidade ({$CAP})."
                    ]);
                }
            }
            // 2b) Um aluno não pode estar em dois boxes da mesma regra
            $seen = [];
            foreach ($alocacoes as $boxId => $ids) {
                foreach ($ids as $aluno) {
                    if (isset($seen[$aluno]) && $seen[$aluno] !== $boxId) {
                        throw ValidationException::withMessages([
                            'alocacoes' => "Aluno {$aluno} está atribuído a mais de um box."
                        ]);
                    }
                    $seen[$aluno] = $boxId;
                }
            }*/

            // 3) Diff por box
            $toInsert = $toDelete = [];
            $allBoxIds = array_unique(array_merge(array_keys($alocacoes), array_keys($exist)));
            foreach ($allBoxIds as $boxId) {
                // ignora boxes que não existem mais (já removidos acima)
                if (!in_array((string)$boxId, $boxesSelecionados, true)) continue;

                $w = $alocacoes[$boxId] ?? [];

                $e = $exist[$boxId] ?? [];

                $add = array_values(array_diff($w, $e));
                $rem = array_values(array_diff($e, $w));

                if ($add) $toInsert[$boxId] = $add;
                if ($rem) $toDelete[$boxId] = $rem;
            }

            // 4) Executa deletes
            foreach ($toDelete as $boxId => $ids) {
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')
                    ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                    ->where('ID_BOX', $boxId)
                    ->whereIn('ALUNO', $ids)
                    ->delete();
            }
            // 5) Executa inserts
            foreach ($toInsert as $boxId => $ids) {
                foreach ($ids as $aluno) {
                    DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')->insert([
                        'ID_BOX_DISCIPLINA' => $idBoxDiscipline,
                        'ID_BOX'            => $boxId,
                        'ALUNO'             => $aluno,
                        'ANO'               => Carbon::now()->year,
                        'SEMESTRE'          => (Carbon::now()->month <= 6) ? 1 : 2,
                        'STATUS'            => 'ATIVO',
                        'CREATED_AT'        => now(),
                        'UPDATED_AT'        => now(),
                    ]);
                }
            }
        });

        //dd($disciplina, $turma, $diaSemana, $hrIni, $hrFim, $alocacoes, $novosAlunos,$boxesSelecionados );

        return redirect()->back()->with('success', 'Disciplinas, boxes e alunos atualizados com sucesso!');
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
                'd.DIA_SEMANA',
                'd.HR_INICIO',
                'd.HR_FIM',
                'ld.NOME',
                'b.DESCRICAO',
                'bd.ALUNO',
                'd.TURMA',
            )
            ->where('d.ID_CLINICA', '=', 2)
            ->distinct()
            ->get();

        return response()->json($disciplines);
    }

    public function boxesDisciplina($discipline, $diasemana)
    {
        $boxes = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->join('FAESA_CLINICA_BOXES', 'FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', 'FAESA_CLINICA_BOX_DISCIPLINA.ID_BOX')
            ->select('FAESA_CLINICA_BOXES.ID_BOX_CLINICA', 'FAESA_CLINICA_BOXES.DESCRICAO')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', trim($discipline))
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DIA_SEMANA', $diasemana)
            ->distinct()
            ->get();

        return response()->json($boxes);
    }

    public function getHorariosBoxDisciplinas($discipline)
    {
        $horarios = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->select('HR_INICIO', 'HR_FIM')
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.ID_CLINICA', '=', 2)
            ->where('FAESA_CLINICA_BOX_DISCIPLINA.DISCIPLINA', trim($discipline))
            ->get();

        return response()->json($horarios);
    }

    public function disciplinascombox($diasemana)
    {
        $disciplina = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as A')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as D', 'D.DISCIPLINA', '=', 'A.DISCIPLINA')
            ->select('D.DISCIPLINA', 'D.NOME')
            ->where('A.DIA_SEMANA', '=', $diasemana)
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

    public function getTurmas(Request $request,$diasemana)
    {

        $disciplina = trim($request->query('disciplina'));
        $box  = (int) $request->query('box');

        $turmas = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('DISCIPLINA', $disciplina)
            ->where('DIA_SEMANA', $diasemana)
            ->where('ID_BOX', (int) $box)
            ->select('TURMA')
            ->distinct()
            ->orderBy('TURMA')
            ->pluck('TURMA');

        return response()->json($turmas);
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
            ->get();

        return view('odontologia/consult_box_discipline', compact('selectBoxDiscipline', 'query_box_discipline'));
    }

    public function deleteBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        // Existe agenda vinculada a este Box/Disciplina?
        $hasAgenda = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as d')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', function ($join) {
                $join->on('la.ID_BOX', '=', 'd.ID_BOX')
                    ->on('la.DISCIPLINA', '=', 'd.DISCIPLINA');
            })
            ->join('FAESA_CLINICA_AGENDAMENTO as a', 'a.ID_AGENDAMENTO', '=', 'la.ID_AGENDAMENTO')
            ->where('d.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->exists();

        if ($hasAgenda) {
            return redirect('odontologia/consultardisciplinabox')
                ->with('error', 'Este Box/Disciplina não pode ser removido porque está vinculado a um ou mais agendamentos.');
        }

        // Tenta remover
        $deleted = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->delete();

        if ($deleted) {
            return redirect('odontologia/consultardisciplinabox')
                ->with('success', 'Box/Disciplina removido com sucesso.');
        }

        return redirect('odontologia/criarboxdisciplina')
            ->with('error', 'Box/Disciplina não encontrado.');
    }
}
