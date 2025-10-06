<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

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
            ->where('BDA.STATUS', '=', 'ALOCADO')
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
}
