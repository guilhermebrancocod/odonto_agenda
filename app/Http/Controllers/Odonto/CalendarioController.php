<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    public function getAgendamentos(Request $request)
    {
        // Você pode usar os parâmetros se quiser filtrar:
        $start = $request->query('start');
        $end = $request->query('end');
        $turma = $request->query('TURMA') ?? $request->query('turma');

        // 1) Busca e agrupa os alunos por agendamento
        $alunosPorAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO_ALUNO AS AA')
            ->join('LYCEUM.dbo.LY_ALUNO AS A', 'AA.ALUNO', '=', 'A.ALUNO')
            ->select(
                'AA.ID_AGENDAMENTO',
                'AA.ALUNO',
                'A.NOME_COMPL as NOME_ALUNO'
            )
            ->get()
            ->groupBy('ID_AGENDAMENTO')      
            ->map(function ($grupo) {
                return $grupo->pluck('NOME_ALUNO')->all();
            });

        // 2) Busca os agendamentos e injeta os alunos no map
        $agendamentos = DB::table('FAESA_CLINICA_AGENDAMENTO AS A')
            ->join('FAESA_CLINICA_PACIENTE AS P', 'A.ID_PACIENTE', '=', 'P.ID_PACIENTE')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO AS L', 'A.ID_AGENDAMENTO', '=', 'L.ID_AGENDAMENTO')
            ->select(
                'A.ID_AGENDAMENTO as id',
                'A.ID_SERVICO as servicoId',
                'L.TURMA',
                'A.MENSAGEM',
                'A.DT_AGEND',
                'A.HR_AGEND_INI',
                'A.HR_AGEND_FIN',
                'A.OBSERVACOES',
                'A.STATUS_AGEND',
                'A.LOCAL',
                'P.NOME_COMPL_PACIENTE as paciente'
            )
            ->where('A.ID_CLINICA', '=', 2)
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('A.DT_AGEND', [$start, $end]);
            })
            ->when(!empty($turma), function ($q) use ($turma) {
                $q->where('L.TURMA', $turma);
            })
            ->get()
            ->map(function ($item) use ($alunosPorAgendamento) {
                return [
                    'id'    => $item->id,
                    'title' => $item->paciente,
                    'start' => $item->DT_AGEND . 'T' . substr($item->HR_AGEND_INI, 0, 5),
                    'end'   => $item->DT_AGEND . 'T' . substr($item->HR_AGEND_FIN, 0, 5),
                    'color' => match ($item->STATUS_AGEND) {
                        0 => '#007bff',
                        1 => '#dc3545',
                        2 => '#28a745',
                        default => '#6c757d',
                    },
                    'extendedProps' => [
                        'observacoes' => $item->OBSERVACOES,
                        'mensagem'    => $item->MENSAGEM,
                        'status'      => $item->STATUS_AGEND,
                        'local'       => $item->LOCAL,
                        'turma'       => $item->TURMA,
                        'NOME_ALUNO'      => $alunosPorAgendamento[$item->id] ?? [],
                    ],
                ];
            });

        return response()->json($agendamentos);
    }

    public function getAlunosSemAgendamento(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $ini = $start ? \Carbon\Carbon::parse($start)->startOfDay() : null;
        $fim = $end   ? \Carbon\Carbon::parse($end)->endOfDay()   : null;

        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as DA')
            ->join('FAESA_CLINICA_BOX_DISCIPLINA as BD', 'BD.ID_BOX_DISCIPLINA', '=', 'DA.ID_BOX_DISCIPLINA')
            ->leftJoin('LYCEUM.DBO.LY_ALUNO as LA', 'LA.ALUNO', '=', 'DA.ALUNO')
            ->whereNotExists(function ($q) use ($ini, $fim) {
                $q->select(DB::raw(1))
                    ->from('FAESA_CLINICA_AGENDAMENTO_ALUNO as AA')
                    ->whereColumn('AA.ALUNO', 'DA.ALUNO')
                    ->when($ini && $fim, fn($qq) => $qq->whereBetween('AA.DT_AGEND', [$ini, $fim]));
                // ->whereNotIn('AA.STATUS_AGEND', ['Cancelado']);
            })
            ->select([
                'DA.ALUNO',
                DB::raw('COALESCE(LA.NOME_COMPL, CAST(DA.ALUNO as varchar(50))) as NOME_COMPL'),
                'BD.DISCIPLINA',
                'BD.TURMA',
                'BD.DIA_SEMANA',
                'BD.HR_INICIO',
                'BD.HR_FIM',
            ])
            ->orderBy('BD.DISCIPLINA')->orderBy('BD.TURMA')->orderBy('DA.ALUNO')
            ->get();

        return response()->json($rows);
    }

    public function editStatus(Request $request, $agendaId)
    {

        DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $agendaId)
            ->update([
                'STATUS_AGEND' => $request->input('status'),
                'MENSAGEM' => $request->input('mensagem')
            ]);

        return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
    }
}
