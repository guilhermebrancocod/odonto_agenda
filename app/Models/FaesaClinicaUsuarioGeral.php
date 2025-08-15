<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaUsuarioGeral extends Model
{
    protected $table = "FAESA_CLINICA_USUARIO_GERAL";

    protected $fillable = [
        'USUARIO',
        'NOME',
        'ID_CLINICA',
        'TIPO',
        'STATUS',
        'CREATED_AT',
        'UPDATED_AT',
    ];
}
