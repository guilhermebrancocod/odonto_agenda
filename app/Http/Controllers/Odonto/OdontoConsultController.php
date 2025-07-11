<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->join('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->select(
                'a.ID_AGENDAMENTO',
                'a.DT_AGEND',
                'a.HR_AGEND_INI',
                'a.HR_AGEND_FIN',
                'a.ID_SERVICO',
                's.SERVICO_CLINICA_DESC',
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


    public function listaAgendamentoId($pacienteId)
    {
        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO', 'FAESA_CLINICA_SERVICO.ID_SERVICO_CLINICA', '=', 'FAESA_CLINICA_AGENDAMENTO.ID_SERVICO')
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
            ->first();

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

        // Consulta os dados do banco
        $agendamentos = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->join('FAESA_CLINICA_PACIENTE', 'FAESA_CLINICA_AGENDAMENTO.ID_PACIENTE', '=', 'FAESA_CLINICA_PACIENTE.ID_PACIENTE')
            ->select(
                'FAESA_CLINICA_AGENDAMENTO.ID_AGENDAMENTO as id',
                'FAESA_CLINICA_AGENDAMENTO.DT_AGEND',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_INI',
                'FAESA_CLINICA_AGENDAMENTO.HR_AGEND_FIN',
                'FAESA_CLINICA_AGENDAMENTO.OBSERVACOES',
                'FAESA_CLINICA_AGENDAMENTO.STATUS_AGEND',
                'FAESA_CLINICA_PACIENTE.NOME_COMPL_PACIENTE as paciente'
            )
            // opcional: filtrar pelo range
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('FAESA_CLINICA_AGENDAMENTO.DT_AGEND', [$start, $end]);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
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
                        'status' => $item->STATUS_AGEND,
                    ]
                ];
            });

        return response()->json($agendamentos);
    }
}
