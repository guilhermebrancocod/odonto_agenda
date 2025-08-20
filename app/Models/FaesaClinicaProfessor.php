<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaProfessor extends Model
{
    protected $table = 'FAESA_CLINICA_PROFESSOR';

    protected $fillable = [
        'USUARIO',
        'ID_CLINICA',
        'NOME_PROFESSOR',
        'CPF',
        'SIT_PROFESSOR',
        'STATUS'
    ];
}
