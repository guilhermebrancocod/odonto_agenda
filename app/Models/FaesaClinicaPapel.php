<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaPapel extends Model
{
    protected $table = "FAESA_CLINICA_PAPEL";

    protected $primaryKey = 'ID';

    protected $fillable = [
        'NOME',
    ];
}
