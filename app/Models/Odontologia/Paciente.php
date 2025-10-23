<?php

namespace App\Models\Odontologia;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'FAESA_CLINICA_PACIENTE';
    protected $primaryKey = 'ID_PACIENTE';
    public $timestamps = false;

    protected $fillable = [
        'NOME_COMPL_PACIENTE','COD_SUS','DT_NASC_PACIENTE','SEXO_PACIENTE','CEP','ENDERECO','END_NUM','COMPLEMENTO','BAIRRO',
        'MUNICIPIO','UF','E_MAIL_PACIENTE','FONE_PACIENTE','NOME_RESPONSAVEL','CPF_RESPONSAVEL','OBSERVACAO',
    ];
}
