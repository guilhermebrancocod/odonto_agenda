<?php

namespace App\Http\Controllers\Odonto\Relatorios;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioEncaminhamentoController extends Controller
{

    public function Encaminhamento(Request $request)
    {
        $data_ini       = $request->input('data_ini');
        $data_fim       = $request->input('data_fim');
        $status         = $request->input('status');
        $filtroPaciente = $request->input('filtroPaciente');

        $q = DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO as E')
            ->join('FAESA_CLINICA_AGENDAMENTO as A', 'A.ID_AGENDAMENTO', '=', 'E.ID_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE as P', 'P.ID_PACIENTE', '=', 'A.ID_PACIENTE')
            ->select([
                'A.ID_AGENDAMENTO',
                'E.ID_NOVO_AGENDAMENTO',
                'E.ID',
                'P.NOME_COMPL_PACIENTE as PACIENTE',
                'A.DT_AGEND as DATA',
                'E.DISCIPLINA',
                'E.STATUS as STATUS',
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
            $q->whereDate('A.DT_AGEND', '<=', $fim->toDateString());
        }

        // --- Filtro por status ---
        if (!is_null($status) && $status !== '') {
            $q->where('E.STATUS', $status);
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

        $encaminhamentos = $q
            ->orderBy('A.DT_AGEND', 'desc')
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($encaminhamentos);
        }

        // Caso acesse a rota direto no browser (renderiza a página)
        return view('odontologia/relatorio/encaminhamentos', [
            'listaEncaminhamentos' => $encaminhamentos,
        ]);
    }
}
