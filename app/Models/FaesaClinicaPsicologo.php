<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaPsicologo extends Model
{
    protected $table = 'FAESA_CLINICA_PSICOLOGO';

    protected $fillable = [
        'ID_PSICOLOGO_CLINICA',
        'NOME_COMPL',
        'CPF',
        'MATRICULA',
        'SIT_PSICOLOGO',
    ];

    public function disponibilidades()
    {
        return $this->hasMany(FaesaClinicaPsicologoDisponibilidade::class, 'PSICOLOGO_ID');
    }
}