<?php

namespace App\Http\Controllers\Odonto\Relatorios;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RelatorioAgendamentoController extends Controller
{
    public function agendamento(Request $request)
    {
        $data_ini       = $request->input('data_ini');
        $data_fim       = $request->input('data_fim');
        $status         = $request->input('status');
        $filtroPaciente = $request->input('filtroPaciente');

        $q = DB::table('FAESA_CLINICA_AGENDAMENTO as A')
            ->join('FAESA_CLINICA_PACIENTE as P', 'P.ID_PACIENTE', '=', 'A.ID_PACIENTE')
            ->select([
                'A.ID_AGENDAMENTO as CODIGO',
                'P.NOME_COMPL_PACIENTE as PACIENTE',
                'A.DT_AGEND as DATA',
                'A.DT_AGEND_FINAL as DATA_FINAL',
                'A.HR_AGEND_INI as HORA_INICIO',
                'A.HR_AGEND_FIN as HORA_FIM',
                'A.STATUS_AGEND as STATUS',
            ]);

        // --- Filtro por data ---
        // Se vierem as duas datas, BETWEEN; se vier só uma, >= ou <=
        if ($data_ini && $data_fim) {
            $ini = Carbon::parse($data_ini)->startOfDay();
            $fim = Carbon::parse($data_fim)->endOfDay();
            if ($ini->gt($fim)) [$ini, $fim] = [$fim, $ini];

            $q->whereBetween('A.DT_AGEND', [$ini->toDateString(), $fim->toDateString()]);
        } elseif ($data_ini) {
            $ini = Carbon::parse($data_ini)->startOfDay();
            $q->whereDate('A.DT_AGEND', '>=', $ini->toDateString());
        } elseif ($data_fim) {
            $fim = Carbon::parse($data_fim)->endOfDay();
            $q->whereDate('A.DT_AGEND_FINAL', '<=', $fim->toDateString());
        }

        // --- Filtro por status ---
        if (!is_null($status) && $status !== '') {
            $q->where('A.STATUS_AGEND', $status);
        }

        // --- Filtro por paciente ---
        if (!is_null($filtroPaciente) && $filtroPaciente !== '') {
            if (is_numeric($filtroPaciente)) {
                // Se for número, filtra por ID do paciente
                $q->where('P.ID_PACIENTE', (int)$filtroPaciente);
            } else {
                // Caso contrário, filtra por nome (contains)
                $q->where('P.NOME_COMPL_PACIENTE', 'like', '%' . $filtroPaciente . '%');
            }
        }

        $agendamentos = $q
            ->orderBy('A.DT_AGEND', 'desc')
            ->orderBy('A.HR_AGEND_INI', 'desc')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($agendamentos);
        }

        // Caso acesse a rota direto no browser (renderiza a página)
        return view('odontologia/relatorio/agendamentos', [
            'listaAgendamentos' => $agendamentos,
        ]);
    }
}
