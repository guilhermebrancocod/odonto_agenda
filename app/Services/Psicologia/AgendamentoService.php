<?php

namespace App\Services\Psicologia;

use App\Models\FaesaClinicaAgendamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgendamentoService
{
    public function getAgendamento(Request $request)
    {
        $query = FaesaClinicaAgendamento::with([
            'paciente',
            'servico',
            'clinica',
            'agendamentoOriginal',
            'remarcacoes'
        ])
        ->where('ID_CLINICA', 1);

        // Filtro por nome ou CPF do paciente
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        // Filtro por data
        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('DT_AGEND', $date);
            } catch (\Exception $e) {
                // Data inválida - ignora filtro
            }
        }

        // Filtro por hora de início
        if ($request->filled('start_time')) {
            try {
                $startTime = Carbon::createFromFormat('H:i', $request->input('start_time'))->format('H:i:s');
                $query->where('HR_AGEND_INI', '>=', $startTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // Filtro por hora de fim
        if ($request->filled('end_time')) {
            try {
                $endTime = Carbon::createFromFormat('H:i', $request->input('end_time'))->format('H:i:s');
                $query->where('HR_AGEND_FIN', '<=', $endTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('STATUS_AGEND', $request->input('status'));
        }

        // Filtro por serviço
        if ($request->filled('service')) {
            $service = $request->input('service');
            $query->whereHas('servico', function($q) use ($service) {
                $q->where('SERVICO_CLINICA_DESC', 'like', "%{$service}%");
            });
        }

        // **FILTRO POR LOCAL**
        if ($request->filled('local')) {
            $local = $request->input('local');
            $query->where('LOCAL', 'like', "%{$local}%");
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Limita o número de registros retornados
        $limit = min((int) $request->input('limit', 10), 100);

        $agendamentos = $query->limit($limit)->get();

        return response()->json($agendamentos);
    }

    public function criarAgendamento()
    {
        
    }

    public function existeConflitoAgendamento()
    {

    }

    public function existeConflitoPaciente()
    {

    }

    public function horarioEstaDisponivel()
    {
        
    }
}