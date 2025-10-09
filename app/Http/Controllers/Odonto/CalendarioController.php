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

    public function getAlunosSemAgendamento(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $ini = $start ? \Carbon\Carbon::parse($start)->startOfDay() : null;
        $fim = $end   ? \Carbon\Carbon::parse($end)->endOfDay()   : null;

        $rows = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as DA')
            ->join('FAESA_CLINICA_BOX_DISCIPLINA as BD', 'BD.ID_BOX_DISCIPLINA', '=', 'DA.ID_BOX_DISCIPLINA')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.DBO.LY_ALUNO as LA', 'LA.ALUNO', '=', 'DA.ALUNO')
            ->whereNotExists(function ($q) use ($ini, $fim) {
                $q->select(DB::raw(1))
                    ->from('FAESA_CLINICA_AGENDAMENTO_ALUNO as AA')
                    ->whereColumn('AA.ALUNO', 'DA.ALUNO')
                    ->when($ini && $fim, fn($qq) => $qq->whereBetween('AA.DT_AGEND', [$ini, $fim]))
                    ->where('AA.ID_CLINICA', 2);
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
