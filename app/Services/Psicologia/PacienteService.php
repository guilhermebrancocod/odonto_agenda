<?php

namespace App\Services\Psicologia;

use App\Models\FaesaClinicaPaciente;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PacienteService
{
    /**
     * ATUALIZA STATUS DO PACIENTE PARA "Em atendimento".
     */
    public function setEmAtendimento(int $id): FaesaClinicaPaciente
    {
        $paciente = FaesaClinicaPaciente::findOrFail($id);
        $paciente->STATUS = 'Em atendimento';
        $paciente->save();

        return $paciente;
    }

    /**
     * RTORNA UM PACIENTE PELO CPF
     */
    public function getByCPF(string $cpf): ?FaesaClinicaPaciente
    {
        return FaesaClinicaPaciente::where('CPF_PACIENTE', $cpf)->first();
    }

    /**
     * ATUALIZA O STATUS DE UM PACIENTE
     */
    public function atualizarStatus(int $id, string $status): FaesaClinicaPaciente
    {
        $paciente = FaesaClinicaPaciente::findOrFail($id);
        $paciente->STATUS = $status;
        $paciente->save();
        return $paciente;
    }

    /**
     * VERIFICA SE UM PACIENTE TEM AGENDAMENTOS ASSOCIADOS
     */
    public function temAgendamentosAssociados(int $id): bool
    {
        return DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_PACIENTE', $id)
            ->where('STATUS_AGEND', '<>', 'Excluido')
            ->where('STATUS_AGEND', '<>', 'Remarcado')
            ->exists();

    }

    /**
     *  CRIA NOVO PACIENTE
     */
    public function createPaciente(array $dados): bool
    {
        $exists = DB::table('FAESA_CLINICA_PACIENTE')
                ->where('CPF_PACIENTE', $dados['CPF_PACIENTE'])
                ->exists();

        if(!$exists) {
            $paciente = new FaesaClinicaPaciente($dados);
            $paciente->STATUS = "Em espera";
            $paciente->save();
        }

        return $exists;
    }

    /**
     * Deleta um paciente (com verificação de agendamentos).
     */
    public function deletarPaciente(int $id): bool
    {
        $paciente = FaesaClinicaPaciente::findOrFail($id);

        if ($this->temAgendamentosAssociados($id)) {            
            throw new \Exception('Paciente possui agendamentos associados.');
        } else {
            // return $paciente->delete();
            $pacienteExcluido = $this->atualizarStatus($id, 'Inativo');
            if(empty($paciente)) {
                return False;
            } else {
                return True;
            }
        }
    }

    /**
     * Retorna uma lista de pacientes com filtros simples.
     */
    public function filtrarPacientes(array $filtros): Collection
    {
        $query = FaesaClinicaPaciente::query();

        // Filtro por nome ou CPF
        if (!empty($filtros['search'])) {
            $query->where('STATUS', '<>', 'Inativo')
            ->where(function ($q) use ($filtros) {
                $q->where('NOME_COMPL_PACIENTE', 'LIKE', '%' . $filtros['search'] . '%')
                ->orWhere('CPF_PACIENTE', 'LIKE', '%' . $filtros['search'] . '%');
            });
        }

        // Filtro por data de nascimento
        if (!empty($filtros['DT_NASC_PACIENTE'])) {
            $query->whereDate('DT_NASC_PACIENTE', $filtros['DT_NASC_PACIENTE']);
        }

        // Filtro por status
        if (!empty($filtros['STATUS']) && $filtros['STATUS']) {
            $query->where('STATUS', $filtros['STATUS']);
        }

        // Filtro por sexo
        if (!empty($filtros['SEXO_PACIENTE'])) {
            $query->where('SEXO_PACIENTE', $filtros['SEXO_PACIENTE']);
        }

        // Filtro por telefone
        if (!empty($filtros['FONE_PACIENTE'])) {
            $query->where('FONE_PACIENTE', 'LIKE', '%' . $filtros['FONE_PACIENTE'] . '%');
        }

        return $query->orderBy('ID_PACIENTE', 'desc')->get();
    }

    public function setAtivo($id)
    {
        $paciente = FaesaClinicaPaciente::findOrFail($id);
        $paciente->STATUS = 'Em espera';
        $paciente->save();
        return $paciente;
    }
}