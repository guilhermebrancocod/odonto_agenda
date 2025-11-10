<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $table = 'FAESA_CLINICA_ODONTOLOGIA_AUDITORIA';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'AUDITABLE_TYPE',
        'AUDITABLE_ID',
        'USER_NAME',
        'EVENT',
        'OLD_VALUES',
        'NEW_VALUES',
        'IP',
        'USER_AGENT',
        'URL'
    ];

    protected $casts = [
        'OLD_VALUES' => 'array',
        'NEW_VALUES' => 'array',
    ];
}
