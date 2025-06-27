<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinica extends Model
{
    protected $table = 'FAESA_CLINICA';

    protected $primaryKey = 'ID_CLINICA';

    public $timestamps = false;

    protected $fillable = [
        'ID_CLINICA',
        'NOME_CLIN',
    ];
}
