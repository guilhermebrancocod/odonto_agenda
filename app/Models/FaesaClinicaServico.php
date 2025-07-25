<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaesaClinicaServico extends Model
{
    protected $table = 'FAESA_CLINICA_SERVICO';

    protected $primaryKey = 'ID_SERVICO_CLINICA';

    public $timestamps = false;

    protected $fillable = [
        'ID_CLINICA',
        'SERVICO_CLINICA_DESC',
        'COD_INTERNO_SERVICO_CLINICA',
        'VALOR_SERVICO',
        'DISCIPLINA',
        'OBSERVACAO',
        'TEMPO_RECORRENCIA_MESES',
    ];

    /**
     * Define o relacionamento BelongsTo com a tabela CLINICA.
     * Um servico de clínica pertence a uma clinica.
     * 
     * @return \\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clinica(): BelongsTo
    {
        return $this->belongsTo(FaesaClinica::class, 'ID_CLINICA', 'ID_CLINICA');
    }
}
