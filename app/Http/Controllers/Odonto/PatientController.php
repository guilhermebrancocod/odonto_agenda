<?php

namespace App\Http\Controllers\Odonto;

use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Odontologia\AuditLogger;

class PatientController extends Controller
{

    public function showForm()
    {
        return view('odontologia/create_patient');
    }

    public function fCreatePatient(Request $request)
    {

        $input = $request->all();
        $input['cpf']              = preg_replace('/\D/', '', $input['cpf'] ?? '');
        $input['cpf_responsavel']  = preg_replace('/\D/', '', $input['cpf_responsavel'] ?? '');
        $input['celular']          = preg_replace('/\D/', '', $input['celular'] ?? '');
        $input['cep']              = preg_replace('/\D/', '', $input['cep'] ?? '');
        $input['cod_sus']          = preg_replace('/\D/', '', $input['cod_sus'] ?? '');
        $input['estado']           = strtoupper(trim($input['estado'] ?? ''));
        $request->merge($input);

        $rules = [
            'nome'            => ['required', 'string', 'max:255'],
            'cpf'             => ['required', 'regex:/^\d{11}$/'],   // 11 dígitos
            'dt_nasc'         => ['required', 'date_format:d/m/Y', 'before:today'],
            'nome_responsavel' => [
                Rule::requiredIf(function () use ($request) {
                    try {
                        $dob = Carbon::createFromFormat('d/m/Y', $request->input('dt_nasc'));
                    } catch (\Exception $e) {
                        return false;
                    }   // se data inválida, não força
                    return $dob->age < 18;
                }),
                'nullable',
                'string',
                'max:255',
            ],
            'sexo'            => ['required', 'in:M,F'],
            'cep'             => ['required', 'regex:/^\d{8}$/'],
            'rua'             => ['nullable', 'string', 'max:255'],
            'numero'          => ['required', 'string', 'max:10'],
            'complemento'     => ['nullable', 'string', 'max:100'],
            'bairro'          => ['nullable', 'string', 'max:100'],
            'cidade'          => ['nullable', 'string', 'max:100'],
            'estado'          => ['nullable', 'alpha', 'size:2'],     // UF
            'email'           => ['required', 'email:rfc', 'max:100'],
            'celular'         => ['required', 'regex:/^\d{10,11}$/'], // 10 ou 11 dígitos
            'cod_sus'         => ['nullable', 'regex:/^\d{15}$/'],   // SUS geralmente 15 dígitos
            'cpf_responsavel' => ['nullable', 'regex:/^\d{11}$/'],
            'obs_laudo'       => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'nome.required'          => 'O nome é obrigatório.',
            'cpf.required'           => 'O CPF é obrigatório.',
            'cpf.regex'              => 'Informe um CPF com 11 dígitos (apenas números).',
            'dt_nasc.required'       => 'A data de nascimento é obrigatória.',
            'dt_nasc.required'       => 'A data de nascimento é obrigatória.',
            'nome_responsavel.required' => 'Nome do Resposável obrigatório para menores de 18 anos.',
            'dt_nasc.date_format'    => 'Use o formato de data dd/mm/aaaa.',
            'dt_nasc.before'         => 'A data de nascimento deve ser anterior a hoje.',
            'sexo.required'          => 'Informe o sexo.',
            'sexo.in'                => 'Sexo inválido (use M ou F).',
            'cep.required'           => 'CEP é obrigatório.',
            'cep.regex'              => 'CEP deve ter 8 dígitos (apenas números).',
            'numero.required'        => 'Número é obrigatório.',
            'estado.alpha'           => 'UF deve conter apenas letras.',
            'estado.size'            => 'UF deve ter 2 letras.',
            'email.required'         => 'E-mail é obrigatório',
            'email.email'            => 'E-mail inválido.',
            'celular.required'       => 'Celular é obrigatório.',
            'celular.regex'          => 'Celular deve ter 10 ou 11 dígitos (apenas números).',
            'cod_sus.regex'          => 'Cartão SUS deve ter 15 dígitos.',
            'cpf_responsavel.regex'  => 'CPF do responsável deve ter 11 dígitos.',
            'obs_laudo.max'          => 'Laudo deve ter no máximo 255 caracteres.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        // Verifica se o CPF já existe
        $validator->after(function ($v) use ($request) {
            $cpf = $request->input('cpf');
            if (DB::table('FAESA_CLINICA_PACIENTE')->where('CPF_PACIENTE', $cpf)->exists()) {
                $v->errors()->add('cpf', 'Paciente já existe com esse CPF.');
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('alert', 'Verifique os campos informados.');
        }

        try {
            $dtNasc = Carbon::createFromFormat('d/m/Y', $request->input('dt_nasc'))->format('Y-m-d');
        } catch (\Throwable $e) {
            return back()->withInput()->with('alert', 'Data de nascimento inválida.');
        }

        // Cadastro do paciente
        $row = [
            'NOME_COMPL_PACIENTE' => $request->input('nome'),
            'CPF_PACIENTE'        => $request->input('cpf'),
            'COD_SUS'             => $request->input('cod_sus'),
            'DT_NASC_PACIENTE'    => $dtNasc,
            'SEXO_PACIENTE'       => $request->input('sexo'),
            'CEP'                 => $request->input('cep'),
            'ENDERECO'            => $request->input('rua'),
            'END_NUM'             => $request->input('numero'),
            'COMPLEMENTO'         => $request->input('complemento'),
            'BAIRRO'              => $request->input('bairro'),
            'MUNICIPIO'           => $request->input('cidade'),
            'UF'                  => $request->input('estado'),
            'E_MAIL_PACIENTE'     => $request->input('email'),
            'FONE_PACIENTE'       => $request->input('celular'),
            'NOME_RESPONSAVEL'    => $request->input('nome_responsavel'),
            'CPF_RESPONSAVEL'     => $request->input('cpf_responsavel'),
            'OBSERVACAO'          => $request->input('obs_laudo'),
        ];

        // INSERT
        $idPaciente = DB::table('FAESA_CLINICA_PACIENTE')
            ->insertGetId($row, 'ID_PACIENTE'); // informe a PK no SQL Server

        // AUDITORIA
        AuditLogger::created(
            'FAESA_CLINICA_PACIENTE',
            $idPaciente,
            $row + ['ID_PACIENTE' => $idPaciente]
        );

        // Redirect PRG
        return back()->with('success', 'Paciente criado com sucesso!');
    }
}
