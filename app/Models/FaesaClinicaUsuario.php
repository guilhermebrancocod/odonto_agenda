<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaUsuario extends Model
{
    // CRIAR TABELA FAESA_CLINICA_USUARIO
    protected $table = 'FAESA_CLINICA_USUARIO';

    protected $primaryKey = 'ID_USUARIO_CLINICA';

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(FaesaClinica::class, 'ID_CLINICA_USUARIO', 'ID_CLINICA');
    }
}