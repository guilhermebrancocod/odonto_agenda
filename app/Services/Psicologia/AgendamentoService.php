<?php

namespace App\Services\Psicologia;

use App\Models\FaesaClinicaAgendamento;
use App\Models\FaesaClinicaServico;
use App\Models\FaesaClinicaSala;
use App\Models\FaesaClinicaHorario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgendamentoService
{
    const ID_CLINICA = 1;

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
        ->where('ID_PSICOLOGO', session('psicologo')[1]) // Retorna apenas agendamentos vinculados ao psicólogo em questão
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

    public function criarAgendamentos(array $dados): array
    {
        $datasParaAgendar = $this->_calcularDatasParaAgendar($dados);

        $agendamentosCriados = 0;
        $diasComErro = [];

        foreach ($datasParaAgendar as $data) {
            $dados['dia_agend'] = $data->format('Y-m-d');

            // Executa todas as validações para a data atual
            $motivoFalha = $this->_validarDisponibilidade($dados);

            if ($motivoFalha === null) {
                // Se não houve falha, cria o agendamento
                $this->_criarAgendamentoUnico($dados);
                $agendamentosCriados++;
            } else {
                // Se houve falha, armazena o motivo
                $diasComErro[$data->format('d/m/Y')] = $motivoFalha;
            }
        }

        return [
            'criados' => $agendamentosCriados,
            'erros' => $diasComErro,
        ];
    }

    private function _validarDisponibilidade(array $dados): ?string
    {
        $data = $dados['dia_agend'];
        $hrIni = $dados['hr_ini'];
        $hrFim = $dados['hr_fim'];
        $idSala = $dados['id_sala_clinica'] ?? null;
        $idPsicologo = $dados['id_psicologo'] ?? null;
        $idPaciente = $dados['paciente_id'];

        // 1. Validação de Feriado
        if ($this->_isFeriado($data)) {
            return "A data selecionada é um feriado.";
        }
        
        // 2. Validação de Horário Bloqueado (Tabela FAESA_CLINICA_HORARIO)
        if (!$this->_isHorarioDisponivel($data, $hrIni, $hrFim)) {
            return "Horário bloqueado ou fora do período de atendimento.";
        }

        // 3. Validação da Sala (Se informada)
        if ($idSala) {
            // Verifica se a sala está ativa
            if (!$this->_isSalaAtiva($idSala)) {
                return "A sala selecionada está inativa.";
            }
            // Verifica se a sala já está ocupada
            if ($this->_hasConflito('ID_SALA', $idSala, $data, $hrIni, $hrFim)) {
                return "Sala já ocupada neste horário.";
            }
        }

        // 4. Validação de Conflito do Psicólogo (Se informado)
        if ($idPsicologo) {
            if ($this->_hasConflito('ID_PSICOLOGO', $idPsicologo, $data, $hrIni, $hrFim)) {
                return "Psicólogo indisponível neste horário.";
            }
        }
        
        // 5. Validação de Conflito do Paciente
        if ($this->_hasConflito('ID_PACIENTE', $idPaciente, $data, $hrIni, $hrFim)) {
            return "Paciente já possui um agendamento neste horário.";
        }

        // Se passou por todas as validações, retorna nulo (sem erros)
        return null;
    }

    private function _hasConflito(string $coluna, int $id, string $data, string $hrIni, string $hrFim): bool
    {
        return FaesaClinicaAgendamento::where('ID_CLINICA', self::ID_CLINICA)
            ->where($coluna, $id)
            ->where('DT_AGEND', $data)
            ->where('STATUS_AGEND', '<>', 'Excluido')
            ->where('STATUS_AGEND', '<>', 'Cancelado') // Adicionar outros status se necessário
            ->where(function ($query) use ($hrIni, $hrFim) {
                $query->where('HR_AGEND_INI', '<', $hrFim)
                      ->where('HR_AGEND_FIN', '>', $hrIni);
            })
            ->exists();
    }

    private function _isFeriado(string $data): bool
    {
        $dataCarbon = Carbon::parse($data)->format('Y-m-d');
        return DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_FERIADO')
            ->where('DATA', '=', $dataCarbon)
            ->exists();
    }

    private function _isHorarioDisponivel(string $data, string $horaInicio, string $horaFim): bool
    {
        // Verifica se há algum bloqueio explícito que se sobreponha ao horário desejado
        $temBloqueio = FaesaClinicaHorario::where('ID_CLINICA', self::ID_CLINICA)
            ->where('BLOQUEADO', 'S')
            ->whereDate('DATA_HORARIO_INICIAL', '<=', $data)
            ->whereDate('DATA_HORARIO_FINAL', '>=', $data)
            ->whereTime('HR_HORARIO_INICIAL', '<', $horaFim)
            ->whereTime('HR_HORARIO_FINAL', '>', $horaInicio)
            ->exists();

        return !$temBloqueio; // Se tem bloqueio, não está disponível
    }

    private function _isSalaAtiva(int $idSala): bool
    {
        $sala = FaesaClinicaSala::find($idSala);
        return $sala && $sala->ATIVO === 'S'; // Supondo 'S' para Ativo
    }

    private function _criarAgendamentoUnico(array $dados): void
    {
        // Prepara dados adicionais, como a descrição da sala, se houver
        $localAgend = null;
        if (!empty($dados['id_sala_clinica'])) {
            $sala = FaesaClinicaSala::find($dados['id_sala_clinica']);
            $localAgend = $sala ? $sala->DESCRICAO : null;
        }

        FaesaClinicaAgendamento::create([
            'ID_CLINICA'    => self::ID_CLINICA,
            'ID_PACIENTE'   => $dados['paciente_id'],
            'ID_SERVICO'    => $dados['id_servico'],
            'ID_PSICOLOGO'  => $dados['id_psicologo'] ?? null,
            'ID_SALA'       => $dados['id_sala_clinica'] ?? null,
            'DT_AGEND'      => $dados['dia_agend'],
            'HR_AGEND_INI'  => $dados['hr_ini'],
            'HR_AGEND_FIN'  => $dados['hr_fim'],
            'STATUS_AGEND'  => 'Agendado',
            'LOCAL'         => $localAgend,
            // Adicione outros campos como VALOR, OBSERVACOES, etc.
        ]);
    }

    private function _calcularDatasParaAgendar(array $dados): array
    {
        $datasParaAgendar = [];
        $dataInicio = Carbon::parse($dados['dia_agend']);

        if (($dados['tem_recorrencia'] ?? '0') === '1') {
            // Recorrência Personalizada
            $diasSemana = $dados['dias_semana'] ?? [];
            $dataFim = isset($dados['duracao_meses_recorrencia'])
                ? $dataInicio->copy()->addMonths((int) $dados['duracao_meses_recorrencia'])
                : (isset($dados['data_fim_recorrencia']) ? Carbon::parse($dados['data_fim_recorrencia']) : $dataInicio->copy()->addMonths(1));

            for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
                if (empty($diasSemana) || in_array($data->dayOfWeek, $diasSemana)) {
                    $datasParaAgendar[] = $data->copy();
                    if (empty($diasSemana)) break; // Se não especificou dias, é só a data inicial
                }
            }
        } else {
            // Recorrência baseada no serviço
            $servico = FaesaClinicaServico::find($dados['id_servico']);
            if ($servico) {
                $dataFim = null;
                $servicoDesc = strtolower($servico->SERVICO_CLINICA_DESC);

                if (in_array($servicoDesc, ['triagem', 'plantão'])) {
                    $dataFim = $dataInicio->copy()->addWeeks(2);
                } elseif ($servicoDesc === 'psicodiagnóstico') {
                    $dataFim = $dataInicio->copy()->addMonths(6);
                } elseif (in_array($servicoDesc, ['psicoterapia', 'educação'])) {
                    $dataFim = $dataInicio->copy()->addYear();
                }
                // ADICIONADO: Lógica para recorrência personalizada via tabela de serviço
                elseif (isset($servico->TEMPO_RECORRENCIA_MESES) && $servico->TEMPO_RECORRENCIA_MESES > 0) {
                    $dataFim = $dataInicio->copy()->addMonths((int) $servico->TEMPO_RECORRENCIA_MESES);
                }

                if ($dataFim) {
                    for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addWeek()) {
                        $datasParaAgendar[] = $data->copy();
                    }
                }
            }
        }

        if (empty($datasParaAgendar)) {
            $datasParaAgendar[] = $dataInicio;
        }

        return $datasParaAgendar;
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