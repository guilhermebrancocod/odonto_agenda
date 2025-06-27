<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaPaciente extends Model
{
    protected $table = 'FAESA_CLINICA_PACIENTE';

    protected $primaryKey = 'ID_PACIENTE';

    public $timestamps = false;

    protected $fillable = [
        'CPF_PACIENTE',
        'NOME_COMPL_PACIENTE',
        'DT_NASC_PACIENTE',
        'SEXO_PACIENTE',
        'ENDERECO',
        'END_NUM',
        'END_COMPL',
        'BAIRRO',
        'UF',
        'CEP',
        'FONE_PACIENTE',
        'E_MAIL_PACIENTE'
    ];

    /**
     * Atributos que devem ser 'castados' para tipos nativos
     * É útil para converter strings 
     */
    protected $casts = [
        'DT_NASC_PACIENTE' => 'date',
    ];
}
