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
        'CREATED_AT',
        'UPDATED_AT',
    ];

    public function agendamentos(): BelongsTo
    {
        return $this->hasMany(FaesaClinicaAgendamento::class, 'LOCAL', 'ID_SALA_CLINICA');
    }
}
