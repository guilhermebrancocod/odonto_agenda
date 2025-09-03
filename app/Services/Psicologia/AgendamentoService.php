<?php

namespace App\Services\Psicologia;

use App\Models\FaesaClinicaAgendamento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AgendamentoService
{
    // RETORNA AGENDAMENTOS PARA ADM
    public function getAgendamento(Request $request)
    {
        $query = FaesaClinicaAgendamento::with([
            'paciente',
            'servico',
            'clinica',
            'agendamentoOriginal',
            'remarcacoes'
        ])
        ->where('ID_CLINICA', 1)
        ->where('STATUS_AGEND', '<>', 'Excluido');

        // Filtro por nome ou CPF do paciente
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        if ($request->filled('psicologo')) {
            $psicologo = $request->input('psicologo');

            $query->whereHas('psicologo', function($q) use ($psicologo) {
                $q->where('ALUNO', 'like', "{$psicologo}%")
                ->orWhere('NOME_COMPL', 'like', "%{$psicologo}%");
            });
        }

        // FILTRO POR DATA
        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('DT_AGEND', $date);
            } catch (\Exception $e) {
                // DATA INVÁLIDA - IGNORA FILTRO
            }
        }

        // FILTRO POR HORA DE INÍCIO
        if ($request->filled('start_time')) {
            try {
                $startTime = Carbon::createFromFormat('H:i', $request->input('start_time'))->format('H:i:s');
                $query->where('HR_AGEND_INI', '>=', $startTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // FILTRO POR HORA DE FIM
        if ($request->filled('end_time')) {
            try {
                $endTime = Carbon::createFromFormat('H:i', $request->input('end_time'))->format('H:i:s');
                $query->where('HR_AGEND_FIN', '<=', $endTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // FILTRO POR STATUS
        if ($request->filled('status')) {
            $query->where('STATUS_AGEND', $request->input('status'));
        }

        // FILTRO POR SERVIÇO
        if ($request->filled('service')) {
            $service = $request->input('service');
            $query->whereHas('servico', function($q) use ($service) {
                $q->where('SERVICO_CLINICA_DESC', 'like', "%{$service}%");
            });
        }

        // FILTRO POR VALOR
        if ($request->filled('valor')) {
            $valorFormatado = str_replace(',', '.', $request->input('valor'));
            $query->where('VALOR_AGEND', '=', $valorFormatado);
        }

        // FILTRO POR LOCAL
        if ($request->filled('local')) {
            $local = $request->input('local');
            $query->where('LOCAL', 'like', "%{$local}%");
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Limita o número de registros retornados - Limite de 100
        $limit = min((int) $request->input('limit', 10), 100);

        $agendamentos = $query->limit($limit)->get();

        return response()->json($agendamentos);
    }

    // RETORNA AGENDAMENTOS PARA PICOLOGO
    public function getAgendamentosForPsicologo(Request $request)
    {
        $query = FaesaClinicaAgendamento::with([
            'paciente',
            'servico',
            'clinica',
            'agendamentoOriginal',
            'remarcacoes'
        ])
        ->where('ID_CLINICA', 1)
        ->where('ID_PSICOLOGO', $request->input('id_psicologo')) // Retorna apenas agendamentos vinculados ao psicólogo em questão
        ->where('STATUS_AGEND', '<>', 'Excluido');

        // Filtro por nome ou CPF do paciente
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('paciente', function($q) use ($search) {
                $q->where('NOME_COMPL_PACIENTE', 'like', "%{$search}%")
                ->orWhere('CPF_PACIENTE', 'like', "%{$search}%");
            });
        }

        if ($request->filled('psicologo')) {
            $psicologo = $request->input('psicologo');

            $query->whereHas('psicologo', function($q) use ($psicologo) {
                $q->where('ALUNO', 'like', "{$psicologo}%")
                ->orWhere('NOME_COMPL', 'like', "%{$psicologo}%");
            });
        }

        // FILTRO POR DATA
        if ($request->filled('date')) {
            try {
                $date = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('DT_AGEND', $date);
            } catch (\Exception $e) {
                // DATA INVÁLIDA - IGNORA FILTRO
            }
        }

        // FILTRO POR HORA DE INÍCIO
        if ($request->filled('start_time')) {
            try {
                $startTime = Carbon::createFromFormat('H:i', $request->input('start_time'))->format('H:i:s');
                $query->where('HR_AGEND_INI', '>=', $startTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // FILTRO POR HORA DE FIM
        if ($request->filled('end_time')) {
            try {
                $endTime = Carbon::createFromFormat('H:i', $request->input('end_time'))->format('H:i:s');
                $query->where('HR_AGEND_FIN', '<=', $endTime);
            } catch (\Exception $e) {
                // Hora inválida - ignora filtro
            }
        }

        // FILTRO POR STATUS
        if ($request->filled('status')) {
            $query->where('STATUS_AGEND', $request->input('status'));
        }

        // FILTRO POR SERVIÇO
        if ($request->filled('service')) {
            $service = $request->input('service');
            $query->whereHas('servico', function($q) use ($service) {
                $q->where('SERVICO_CLINICA_DESC', 'like', "%{$service}%");
            });
        }

        // FILTRO POR VALOR
        if ($request->filled('valor')) {
            $valorFormatado = str_replace(',', '.', $request->input('valor'));
            $query->where('VALOR_AGEND', '=', $valorFormatado);
        }

        // FILTRO POR LOCAL
        if ($request->filled('local')) {
            $local = $request->input('local');
            $query->where('LOCAL', 'like', "%{$local}%");
        }

        $query->orderBy('DT_AGEND', 'desc');

        // Limita o número de registros retornados - Limite de 100
        $limit = min((int) $request->input('limit', 10), 100);

        $agendamentos = $query->limit($limit)->get();

        return response()->json($agendamentos);
    } 

    public function criarAgendamento()
    {
        
    }

    public function criarAgendamentoPsicologo(Request $request)
    {
        dd($request);
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

    // ADICIONA MENSAGEM DE MOTIVO DE CANCELAMENTO AO AGENDAMENTO
    public function addMensagemCancelamento($id, String $msg)
    {
        $agendamento = FaesaClinicaAgendamento::findOrFail($id);
        $agendamento->STATUS_AGEND = "Cancelado";
        $agendamento->MENSAGEM = $msg;
        $agendamento->save();

        return $agendamento;
    }
}