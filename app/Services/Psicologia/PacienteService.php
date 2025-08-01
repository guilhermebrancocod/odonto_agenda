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
            ->exists();
    }

    /**
     *  CRIA NOVO PACIENTE
     */
    public function createPaciente(array $dados)
    {
        $paciente = new FaesaClinicaPaciente($dados);
        $paciente->STATUS = "Em espera";
        $paciente->save();

        return $paciente;
    }

    /**
     * Deleta um paciente (com verificação de agendamentos).
     */
    public function deletarPaciente(int $id): bool
    {
        $paciente = FaesaClinicaPaciente::findOrFail($id);

        if ($this->temAgendamentosAssociados($id)) {
            throw new \Exception('Paciente possui agendamentos associados.');
        }

        return $paciente->delete();
    }

    /**
     * Retorna uma lista de pacientes com filtros simples.
     */
    public function filtrarPacientes(array $filtros): Collection
    {
        $query = FaesaClinicaPaciente::query();

        if (!empty($filtros['search'])) {
            $query->where(function ($q) use ($filtros) {
                $q->where('NOME_COMPL_PACIENTE', 'LIKE', '%' . $filtros['search'] . '%')
                  ->orWhere('CPF_PACIENTE', 'LIKE', '%' . $filtros['search'] . '%');
            });
        }

        if (!empty($filtros['DT_NASC_PACIENTE'])) {
            $query->whereDate('DT_NASC_PACIENTE', $filtros['DT_NASC_PACIENTE']);
        }

        if (!empty($filtros['STATUS'])) {
            $query->where('STATUS', $filtros['STATUS']);
        }

        if (!empty($filtros['SEXO_PACIENTE'])) {
            $query->where('SEXO_PACIENTE', $filtros['SEXO_PACIENTE']);
        }

        if (!empty($filtros['FONE_PACIENTE'])) {
            $query->where('FONE_PACIENTE', 'LIKE', '%' . $filtros['FONE_PACIENTE'] . '%');
        }

        return $query->orderBy('ID_PACIENTE', 'desc')->get();
    }
}