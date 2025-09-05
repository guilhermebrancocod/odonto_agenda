<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaSala extends Model
{
    protected $table = 'FAESA_CLINICA_SALA';

    protected $primaryKey = 'ID_SALA_CLINICA';

    public $timestamps = true;

    protected $fillable = [
        'DESCRICAO',
        'ATIVO',
        'DISCIPLINA',
        'CREATED_AT',
        'UPDATED_AT',
        'SIT_SALA'
    ];
}
