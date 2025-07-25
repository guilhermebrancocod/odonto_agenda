<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaHorario extends Model
{
    protected $table = 'FAESA_CLINICA_HORARIO';

    protected $primaryKey = 'ID_BLOQUEIO';

    public $timestamps = false;

    protected $fillable = [
        'ID_BLOQUEIO',
        'USUARIO',
        'DATA_BLOQ_INICIAL',
        'DATA_BLOQ_FINAL',
        'HR_BLOQ_INICIAL',
        'HR_BLOQ_FINAL',
        'OBSERVACAO_BLOQUEIO',
    ];

    protected $casts = [
        'DT_BLOQUEIO_INICIAL' => 'date',
        'DT_BLOQUEIO_FINAL' => 'date',
        'HR_BLOQUEIO_INICIAL' => 'string',
        'HR_BLOQUEIO_FINAL' => 'string',
    ];
}
