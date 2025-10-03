<?php

namespace App\Http\Controllers\Odonto;

use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Odontologia\AuditLogger;

class OdontoCreateController extends Controller
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

    public function showFormAgenda()
    {
        return view('odontologia/create_agenda');
    }

    public function fCreateAgenda(Request $request)
    {

        

        $rules = [
            'ID_PACIENTE'     => ['required'],
            'status'          => ['required'],   // 11 dígitos
            'date'            => ['bail', 'required', 'date_format:d/m/Y'],
            'date_end'        => ['bail', 'required', 'date_format:d/m/Y'],
            'date'        => ['bail', 'required', 'date_format:d/m/Y'],
            'date_end'    => ['bail', 'required', 'date_format:d/m/Y'],

            'disciplina'  => ['required'],
            'ID_BOX'      => ['required'],
            'turma'       => ['required'],
            'procedimento' => ['required'],

            'recorrencia' => ['required', 'integer', 'in:1,2'],
        ];

        $messages = [
            'ID_PACIENTE.required'  => 'O paciente é obrigatório.',
            'status.required'       => 'O status é obrigatório.',

            'date.required'         => 'Informe a data inicial.',
            'date.date_format'      => 'Data inicial deve estar no formato dd/mm/aaaa.',
            'date_end.required'     => 'Informe a data final.',
            'date_end.date_format'  => 'Data final deve estar no formato dd/mm/aaaa.',

            'disciplina.required'   => 'A disciplina é obrigatória.',
            'ID_BOX.required'       => 'O box é obrigatório.',
            'turma.required'        => 'A turma é obrigatória.',
            'procedimento.required' => 'O procedimento é obrigatório.',
            'recorrencia.required'  => 'A recorrência é obrigatória.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($v) use ($request) {
            if ($v->errors()->has('date') || $v->errors()->has('date_end')) {
                return; // já falhou formato/required; não compara agora
            }
            if (!$request->filled('date') || !$request->filled('date_end')) {
                return; // alguma está vazia; deixa o 'required' atuar
            }

            try {
                $ini = Carbon::createFromFormat('d/m/Y', $request->input('date'))->startOfDay();
                $fim = Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->startOfDay();
            } catch (\Throwable $e) {
                // se quebrar aqui, as mensagens de date_format já cobrem
                return;
            }

            if ($fim->lt($ini)) {
                $v->errors()->add('date_end', 'A data final deve ser igual ou posterior à data inicial.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $idClinica = 2;
        $idBox     = (int) $request->input('ID_BOX');
        $idServico = $request->input('procedimento');
        $disciplina   = $request->input('disciplina');
        $disciplinaEnc = $request->input('disciplina-enc');
        $turma = $request->input('turma');
        $recorrencia = $request->input('recorrencia');

        $ini = Carbon::createFromFormat('d/m/Y', $request->input('date'))->startOfDay();
        $fim   = $request->filled('date_end')
            ? Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->startOfDay()
            : $ini->copy();

        if ($fim->lt($ini)) {
            [$ini, $fim] = [$fim, $ini];
        }

        if ($ini->equalTo($fim)) {
            // PONTUAL: checa apenas o dia
            $ehFeriado = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_FERIADO')
                ->whereDate('DATA', $ini->toDateString())
                ->exists();

            if ($ehFeriado) {
                return back()->withInput()->with('alert', 'Data do agendamento será feriado.');
            }
        } else {
            // PERÍODO: checa qualquer feriado entre ini e fim (inclusivo)
            // forma sargável: [ini, fim+1dia)
            $fimExclusivo = $fim->copy()->addDay()->startOfDay();

            $temFeriado = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_FERIADO')
                ->where('DATA', '=', $ini)
                ->where('DATA', '=',  $fimExclusivo)
                ->exists();

            if ($temFeriado) {
                return back()->withInput()->with('alert', 'Data do agendamento será feriado.');
            }
        }

        // 3) Conversões
        $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))->startOfDay();
        $dataFim    = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->startOfDay();

        $horarios = collect((array) $request->input('dias_semana', []))
            ->map(fn($v) => (string) $v)                     // normaliza p/ string
            ->map(fn($v) => (int) $v)                        // para ordenar numericamente
            ->sort()
            ->values();

        if ($horarios->isNotEmpty()) {
            $minKey = (string) $horarios->first();
            $maxKey = (string) $horarios->last();
        } else {
            // fallback: se nada foi enviado em dias_semana, usa os campos do formulário
            $hrIni = Carbon::createFromFormat('H:i', $request->input('hr_ini'))->format('H:i:s');
            $hrFim = Carbon::createFromFormat('H:i', $request->input('hr_fim'))->format('H:i:s');
        }

        $rawValor = $request->input('valor');
        $valor_convert = null;
        if ($rawValor !== null && $rawValor !== '') {
            $tmp = str_replace(['R$', ' ', '.'], '', $rawValor);
            $valor_convert = (float) str_replace(',', '.', $tmp);
        }

        $descricaoLocal = DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->value('DESCRICAO');

        // 4) Lógica de agendamento recorrência ou pontual (se recorrência deve agendamento entre a data inicial e a data final se pontual dia inicial e final igual, só um agendamento)
        if ($recorrencia === '2') {
            /* RECORRÊNCIA */
            DB::beginTransaction();

            // saneia datas base
            $dataBase = $dataInicio->copy();
            $dataFim  = $dataFim->copy();

            // código de frequência vindo do front: 0=semana, 1=quinzena, 2=mês
            $freqCode = (int) $request->input('dia_recorrencia', 0);
            if (!in_array($freqCode, [0, 1, 2], true)) {
                DB::rollBack();
                return back()->withInput()->with('alert', 'Tipo de recorrência inválido (use 0=semanal, 1=quinzenal, 2=mensal).');
            }

            // função para avançar a data conforme a frequência
            $step = function (Carbon $d) use ($freqCode) {
                return match ($freqCode) {
                    0 => $d->addWeek(),             // semanal
                    1 => $d->addWeeks(2),           // quinzenal
                    2 => $d->addMonthNoOverflow(),  // mensal (sem “estourar” fim de mês)
                    default => $d->addWeek(),
                };
            };

            $houveDuplicata = false;
            $inseridos = 0;

            // primeira ocorrência é a própria $dataBase
            for ($data = $dataBase->copy(), $guard = 0; $data->lte($dataFim); $step($data), $guard++) {

                // guarda para evitar loop infinito por algum bug (ex.: 120 ocorrências)
                if ($guard > 120) {
                    $houveDuplicata = true;
                    break;
                }

                // checagem de conflito no mesmo box/horário/data
                $duplicado = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
                    ->where('a.ID_CLINICA', $idClinica)
                    ->whereDate('a.DT_AGEND', $data->toDateString())
                    ->whereRaw('CAST(a.HR_AGEND_INI AS time) = CAST(? AS time)', [$hrIni])
                    ->whereRaw('CAST(a.HR_AGEND_FIN  AS time) = CAST(? AS time)', [$hrFim])
                    ->whereExists(function ($q) use ($idBox) {
                        $q->select(DB::raw(1))
                            ->from('FAESA_CLINICA_LOCAL_AGENDAMENTO as l')
                            ->whereColumn('l.ID_AGENDAMENTO', 'a.ID_AGENDAMENTO')
                            ->where('l.ID_BOX', $idBox);
                    })
                    ->exists();

                if ($duplicado) {
                    $houveDuplicata = true;
                    break; // aborta toda a série (se preferir, apenas pule esta e continue)
                }

                // INSERE AGENDAMENTO
                $idAg = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                    'ID_CLINICA'         => $idClinica,
                    'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                    'ID_SERVICO'         => $idServico,
                    'DT_AGEND'           => $data->toDateString(),
                    'DT_AGEND_FINAL'     => $data->toDateString(), // ocorrência de 1 dia
                    'HR_AGEND_INI'       => $hrIni,
                    'HR_AGEND_FIN'       => $hrFim,
                    'STATUS_AGEND'       => $request->input('status'),
                    'ID_AGEND_REMARCADO' => null,
                    'RECORRENCIA'        => $recorrencia,          // mantém seu campo original
                    'VALOR_AGEND'        => $valor_convert,
                    'OBSERVACOES'        => $request->input('obs'),
                    'LOCAL'              => $descricaoLocal,
                ]);

                DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                    'ID_AGENDAMENTO' => $idAg,
                    'ID_BOX'         => $idBox,
                    'DISCIPLINA'     => $disciplina,
                    'TURMA'          => $request->input('turma'),
                ]);

                $alunos = (array) $request->input('alunos_do_agendamento', []);
                if (empty($alunos)) {
                }

                $idAg  = (int) $idAg;                   // seu id do agendamento
                $idBox = (int) $idBox;                  // opcional
                $now   = now();

                $rows = array_map(fn($ra) => [
                    'ID_CLINICA'     => 2,
                    'ID_AGENDAMENTO' => $idAg,
                    'ALUNO'          => (string) $ra,
                    'ID_BOX'         => $idBox,
                    'DOCENTE'        => 1111,
                    'ANO_LETIVO'     => 2025,
                    'SEMESTRE'       => 2,
                    'CREATED_AT'     => $now,
                    'UPDATED_AT'     => $now,
                ], $alunos);

                DB::table('FAESA_CLINICA_AGENDAMENTO_ALUNO')->insert($rows);


                DB::table('FAESA_CLINICA_AGENDAMENTO_FINANCEIRO')->insert([
                    'ID_AGENDAMENTO' => $idAg,
                    'VALOR_TOTAL' => $request->input('valor'),
                    'FORMA_PAG' => $request->input('forma-pag'),
                    'PARCELAS_PAG' => $request->input('qtd_parcelas'),
                    'DIA_VENC' => $request->input('dia_venc'),
                    'CREATED_AT'     => now(),
                    'UPDATED_AT'     => now()
                ]);

                $inseridos++;
            }

            if ($houveDuplicata) {
                DB::rollBack();
                return back()->withInput()->with('alert', 'Já existe agendamento no mesmo horário e box para pelo menos uma das datas.');
            }

            DB::commit();
            return back()->with('success', "Agendamento de recorrência criado com sucesso. Ocorrências inseridas: {$inseridos}");
        }

        /*pontual*/
        DB::beginTransaction();

        if ($request->recorrencia === '1') {
            $dt = $dataInicio->toDateString(); // ou derive de $request; pontual: um único dia

            $duplicadoPontual = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
                ->where('a.ID_CLINICA', $idClinica)
                ->whereDate('a.DT_AGEND', $dt)
                ->whereRaw('CAST(a.HR_AGEND_INI AS time) = CAST(? AS time)', [$hrIni])
                ->whereRaw('CAST(a.HR_AGEND_FIN  AS time) = CAST(? AS time)', [$hrFim])
                ->whereExists(function ($q) use ($idBox) {
                    $q->select(DB::raw(1))
                        ->from('FAESA_CLINICA_LOCAL_AGENDAMENTO as l')
                        ->whereColumn('l.ID_AGENDAMENTO', 'a.ID_AGENDAMENTO')
                        ->where('l.ID_BOX', $idBox);
                })
                ->exists();

            if ($duplicadoPontual) {
                DB::rollBack();
                return back()->withInput()->with('alert', 'Conflito: já existe agendamento no mesmo box e horário.');
            }

            $idAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                'ID_CLINICA'         => $idClinica,
                'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                'ID_SERVICO'         => $idServico,
                'DT_AGEND'           => $dt,
                'DT_AGEND_FINAL'     => $dt, // pontual: mesma data
                'HR_AGEND_INI'       => $hrIni,
                'HR_AGEND_FIN'       => $hrFim,
                'STATUS_AGEND'       => $request->input('status'),
                'ID_AGEND_REMARCADO' => null,
                'RECORRENCIA'        => 'pontual',
                'VALOR_AGEND'        => $valor_convert,
                'OBSERVACOES'        => $request->input('obs'),
                'LOCAL'              => $descricaoLocal,
            ]);

            DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                'ID_AGENDAMENTO' => $idAgendamento,
                'ID_BOX'         => $idBox,
                'DISCIPLINA'     => $disciplina,
                'TURMA'          => $turma,
            ]);
        }

        DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')->insert([
            'ID_AGENDAMENTO' => $idAgendamento,
            'DISCIPLINA'     => $disciplinaEnc,
            'STATUS'     => 'DISPONIVEL',
            'CREATED_AT'     => now(),
            'UPDATED_AT'     => now()
        ]);

        DB::commit();
        return back()->with('success', 'Agendamento pontual criado com sucesso.');
    }

    public function editPatient($pacienteId)
    {
        if (!ctype_digit((string)$pacienteId)) {
            abort(404); // ou 422
        }

        $paciente = DB::table('FAESA_CLINICA_PACIENTE')
            ->where('ID_PACIENTE', (int)$pacienteId)
            ->first();

        if (!$paciente) abort(404);

        return view('odontologia/create_patient', compact('paciente'));
    }

    public function editUser($userId)
    {
        $user = DB::table('FAESA_CLINICA_USUARIO_GERAL')->where('ID', $userId)->first();

        if (!$user) {
            abort(404);
        }

        return view('odontologia/create_user', compact('user'));
    }

    public function editAgenda($agendaId)
    {
        // 1) Cabeçalho do agendamento (sem AA aqui)
        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->leftJoin('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'a.ID_AGENDAMENTO')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as ld', 'ld.DISCIPLINA', '=', 'la.DISCIPLINA')
            ->leftJoin('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->leftJoin('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->where('a.ID_CLINICA', 2)
            ->where('a.ID_AGENDAMENTO', $agendaId)
            ->select(
                'a.ID_AGENDAMENTO',
                'a.ID_SERVICO',
                'a.ID_PACIENTE',
                'a.DT_AGEND',
                'a.DT_AGEND_FINAL',
                'a.HR_AGEND_INI',
                'a.HR_AGEND_FIN',
                'la.ID_BOX',
                'a.LOCAL',
                'a.OBSERVACOES',
                'cb.DESCRICAO',
                'a.RECORRENCIA',
                'la.DISCIPLINA',
                'ld.NOME AS DISCIPLINA_NOME',
                'la.TURMA',
                'a.UPDATED_AT',
                'a.VALOR_AGEND',
                's.SERVICO_CLINICA_DESC',
                'p.NOME_COMPL_PACIENTE'
            )
            ->first();

        if (!$agenda) {
            abort(404);
        }

        // 2) Alunos selecionados para este agendamento (apenas IDs)
        $alunosSelecionados = DB::table('FAESA_CLINICA_AGENDAMENTO_ALUNO as aa')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as a', 'a.ALUNO', '=', 'aa.ALUNO')
            ->where('aa.ID_AGENDAMENTO', $agendaId)
            ->pluck('a.NOME_COMPL', 'aa.ALUNO')   // valor, chave
            ->toArray();

        // (Opcional) Se quiser nomes também (ajuste a tabela/colunas conforme seu schema):
        // $alunosMap = DB::table('SUA_TABELA_ALUNO as a')
        //     ->whereIn('a.ALUNO', $alunosSelecionados)
        //     ->pluck('a.NOME_COMPL', 'a.ALUNO'); // [id => nome]
        return view('odontologia.create_agenda', compact('agenda', 'alunosSelecionados' /*, 'alunosMap'*/));
    }


    public function createProcedures(Request $request)
    {
        $request->validate([
            'descricao' => ['required', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
        ], [
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.regex' => 'A descrição não pode conter caracteres especiais.',
        ]);

        $valorServico = $request->input('valor');
        $descricao = $request->input('descricao');

        DB::table('FAESA_CLINICA_SERVICO')->insertGetId([
            'ID_CLINICA' => 2,
            'SERVICO_CLINICA_DESC' => $descricao,
            'COD_INTERNO_SERVICO_CLINICA' => 0,
            'VALOR_SERVICO' => $valorServico,
            'ATIVO' => $request->input('ativo')
        ]);

        return redirect()->back()->with('success', 'Procedimento cadastrado com sucesso!');
    }

    public function editService($idService)
    {

        $servico = DB::table('FAESA_CLINICA_SERVICO')->where('ID_SERVICO_CLINICA', $idService)->first();

        if (!$servico) {
            return redirect('/odontologia/consultarservico')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_service', compact('servico'));
    }


    public function createBox(Request $request)
    {
        $idBox = DB::table('FAESA_CLINICA_BOXES')->insertGetId([
            'ID_CLINICA' => 2,
            'DESCRICAO' => $request->input('descricao'),
            'ATIVO' => $request->input('status'),
        ]);
        return redirect()->back()->with('success', 'Box cadastrado com sucesso!');
    }

    public function createUsuario(Request $request)
    {
        DB::table('FAESA_CLINICA_USUARIO_GERAL')->insertGetId([
            'ID_CLINICA' => 2,
            'USUARIO' => $request->input('winusuario'),
            'NOME' => $request->input('nome'),
            'TIPO' => $request->input('tipo'),
            'PESSOA' => $request->input('pessoa'),
            'STATUS' => $request->input('status'),
        ]);
        return redirect()->back()->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function editBox($idBox)
    {
        $box = DB::table('FAESA_CLINICA_BOXES')->where('ID_BOX_CLINICA', $idBox)->first();

        if (!$box) {
            return redirect('odontologia/consultarbox')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_box', compact('box'));
    }

    public function createBoxDiscipline(Request $request)
    {
        // 1) Validação
        $data = $request->validate([
            'disciplina'  => ['required', 'string'],
            'turma'       => ['required', 'string'],
            'data'        => ['required', 'integer', 'between:1,7'],     // dia da semana
            'boxes'       => ['required', 'array', 'min:1'],
            'boxes.*'     => ['integer'],
            'horarios'    => ['required', 'array', 'min:1'],
            'horarios.*'  => ['regex:/^\d{1,2}:\d{2}$/'],              // HH:mm
            'alocacoes'   => ['required', 'array', 'min:1'],             // mapa: [boxId => [alunos...]]
            'alocacoes.*' => ['array', 'min:1'],
            'alocacoes.*.*' => ['string', 'max:20'],                    // RA/matrícula
        ], [
            'required' => 'Campo obrigatório.',
            'between'  => 'Valor inválido.',
            'regex'    => 'Horário inválido (HH:mm).',
        ]);

        // 2) Normaliza hr_ini / hr_fim a partir de horarios[]
        $toMin = function (string $h) {
            [$H, $M] = array_map('intval', explode(':', $h));
            return $H * 60 + $M;
        };
        $mins  = array_map($toMin, $data['horarios']);
        $min   = min($mins);
        $max = max($mins);
        $hrIni = sprintf('%02d:%02d', intdiv($min, 60), $min % 60) . ':00'; // -> '07:30:00'
        $hrFim = sprintf('%02d:%02d', intdiv($max, 60), $max % 60) . ':00'; // -> '09:00:00'

        // 3) Ano/Semestre (ajuste se vier do request)
        $ano = (int) now()->year;
        $sem = now()->month <= 6 ? 1 : 2;

        DB::transaction(function () use ($data, $hrIni, $hrFim, $ano, $sem) {

            $idsBoxDisc = []; // [boxId => id_box_disciplina]

            // 3.1) Garante/obtém a linha de regra para CADA box
            foreach ($data['boxes'] as $boxId) {
                $attrs = [
                    'ID_CLINICA' => 2,
                    'ID_BOX'     => (int) $boxId,
                    'DISCIPLINA' => $data['disciplina'],
                    'TURMA'      => $data['turma'],
                    'DIA_SEMANA' => (int) $data['data'],
                    'HR_INICIO'  => $hrIni,
                    'HR_FIM'     => $hrFim,
                ];

                // tenta achar a regra
                $row = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->where($attrs)->first();

                if ($row) {
                    $idBoxDisc = (int) $row->ID_BOX_DISCIPLINA;
                } else {
                    // cria e pega o ID
                    $idBoxDisc = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insertGetId(
                        $attrs + ['DT_CADASTRO' => DB::raw('SYSDATETIME()')]
                    );
                }

                $idsBoxDisc[(int)$boxId] = $idBoxDisc;
            }

            // 3.2) Grava vínculos aluno↔box_disciplina (recorrente)
            foreach ($data['alocacoes'] as $boxId => $alunos) {
                $boxId = (int) $boxId;
                if (!isset($idsBoxDisc[$boxId])) {
                    // box não listado em boxes[]: ignore ou lance erro 422
                    continue;
                }
                $idBoxDisc = $idsBoxDisc[$boxId];

                // capacidade por box (ex.: 2)
                if (count($alunos) > 2) {
                    abort(422, "Box {$boxId} excedeu capacidade (2).");
                }

                // monta linhas para upsert
                $rows = [];
                foreach ($alunos as $alunoId) {
                    $rows[] = [
                        'ID_BOX_DISCIPLINA' => $idBoxDisc,
                        'ID_BOX'            => $boxId,
                        'ALUNO'             => (string) $alunoId,
                        'ANO'               => $ano,
                        'SEMESTRE'          => $sem,
                        'STATUS'            => 'ATIVO',
                        'CREATED_AT'        => DB::raw('SYSDATETIME()'),
                        'UPDATED_AT'        => DB::raw('SYSDATETIME()'),
                    ];
                }

                // upsert garante 1 linha por (regra, aluno, ano, semestre)
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')->upsert(
                    $rows,
                    ['ID_BOX_DISCIPLINA', 'ALUNO', 'ANO', 'SEMESTRE'],
                    ['STATUS', 'UPDATED_AT']
                );
            }
        });

        return back()->with('success', 'Vínculos salvos com sucesso!');
    }

    public function editBoxDiscipline(Request $request, int $idBoxDiscipline)
    {
        // 1) REGRA (linha única na tabela base)
        $regra = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->first();

        if (!$regra) {
            return redirect('odontologia/criarboxdisciplina')
                ->with('error', 'Serviço não encontrado.');
        }

        // filtros opcionais
        $ano = $request->integer('ano');
        $sem = $request->integer('semestre');

        // 2) ALUNOS vinculados (recorrência)
        $alunos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO as BDA')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as A', 'A.ALUNO', '=', 'BDA.ALUNO')
            ->where('BDA.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->when($ano, fn($q) => $q->where('BDA.ANO', $ano))
            ->when($sem, fn($q) => $q->where('BDA.SEMESTRE', $sem))
            ->where('BDA.STATUS', '=', 'ATIVO')
            ->select('BDA.ALUNO', 'A.NOME_COMPL', 'BDA.ANO', 'BDA.SEMESTRE')
            ->orderBy('A.NOME_COMPL')
            ->get();

        $nome = DB::table('FAESA_CLINICA_BOX_DISCIPLINA as BD')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.ly_disciplina as D', 'D.DISCIPLINA', '=', 'BD.DISCIPLINA')
            ->where('BD.ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->value('D.NOME');

        $alunosIds = $alunos->pluck('ALUNO')->all();

        // 3) Empacota tudo numa única var pra view
        $boxDiscipline = (object) [
            'regra'       => $regra,                       // tem DISCIPLINA, TURMA, DIA_SEMANA, HR_INI/HR_FIM, ID_BOX etc.
            'alunos'      => $alunos,                      // lista com nome
            'alunosIds'   => $alunosIds,                   // só ids (pra marcar checkboxes)
            'disciplina'  => $regra->DISCIPLINA,
            'nome'        => $nome,
            'turma'       => $regra->TURMA,
            'dia_semana'  => $regra->DIA_SEMANA,
            // cuidado com o nome das colunas no seu banco: use HR_INI/HR_FIM OU HR_INICIO/HR_FIM conforme existir
            'hr_ini'      => $regra->HR_INI   ?? $regra->HR_INICIO ?? null,
            'hr_fim'      => $regra->HR_FIM   ?? null,
            'box_id'      => $regra->ID_BOX,
            'ano'         => $ano,
            'semestre'    => $sem,
            'disciplina_nome' => $nome
        ];

        return view('odontologia.create_box_discipline', compact('boxDiscipline'));
    }

    public function defineLocalAtendimento($agendaId, $boxId)
    {
        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->inserGetId([
            'ID_AGENDAMENTO' => $agendaId,
            'ID_BOX' => $boxId
        ]);
    }

    public function historyPaciente($id)
    {
        $rows = DB::table('FAESA_CLINICA_ODONTOLOGIA_AUDITORIA')
            ->where('AUDITABLE_ID', $id)
            ->where('EVENT', '=', 'created')
            ->select('AUDITABLE_ID', 'OLD_VALUES', 'NEW_VALUES', 'created_at', 'updated_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows);
    }
}
