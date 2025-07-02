<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaUsuario extends Model
{
    protected $table = 'FAESA_CLINICA_USUARIO';

    protected $primaryKey = 'ID_USUARIO';

    public function clinica(): BelongsTo
    {
        return $this->belongsTo(FaesaClinica::class, 'ID_CLINICA', 'ID_CLINICA');
    }
}