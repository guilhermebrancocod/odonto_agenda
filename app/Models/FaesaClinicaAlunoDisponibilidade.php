<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaalunoDisponibilidade extends Model
{
    protected $table = 'FAESA_CLINICA_aluno_DISPONIBILIDADE';

    protected $fillable = [
        'aluno_ID',
        'dia_semana',
        'hora_inicio',
        'hora_fim',
    ];

    public function aluno()
    {
        return $this->belongsTo(FaesaClinicaaluno::class, 'aluno_ID');
    }
}