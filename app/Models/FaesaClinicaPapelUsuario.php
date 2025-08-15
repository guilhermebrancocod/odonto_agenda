<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaesaClinicaPapelUsuario extends Model
{
    protected $table = 'FAESA_CLINICA_PAPEL_USUARIO';
protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'USUARIO_ID',
        'PAPEL_ID',
    ];

    public function papel()
    {
        return $this->belongsTo(FaesaClinicaPapel::class, 'ID');
    }

    public function usuario()
    {
        return $this->belongsTo(FaesaClinicaUsuarioGeral::class, 'ID');
    }
}
