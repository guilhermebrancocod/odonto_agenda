<?php

namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table = 'FAESA_CLINICA_AGENDAMENTO';   // ajuste
    protected $primaryKey = 'ID_AGENDAMENTO';         // ajuste
    public $timestamps = false;                        // ou true, se existir
    protected $fillable = [
        'ID_PACIENTE', 'DATA', 'HORA', 'STATUS', 'BOX', 'DISCIPLINA', 'OBSERVACAO',
    ];
}
