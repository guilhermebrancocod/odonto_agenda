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
            'dia_recorrencia' => [
                Rule::requiredIf(fn() => (int) request('recorrencia') !== 1),
                'nullable',
                'between:0,7',
            ],
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
            'dia_recorrencia.required' => 'Escolha ao menos um dia da recorrência.',
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

        $dias = [
            '1' =>  '7:30',
            '2' =>  '8:15',
            '3' =>  '9:00',
            '4' =>  '9:45',
            '5' =>  '10:15',
            '6' =>  '11:00',
            '7' =>  '11:45',
            '8' =>  '12:30',
            '9' =>  '13:15',
            '10' => '14:00',
            '11' => '14:45',
            '12' => '15:30',
            '13' => '16:15',
            '14' => '17:00',
            '15' => '17:45',
            '16' => '18:30',
        ];

        $horarios = collect((array) $request->input('dias_semana', []))
            ->map(fn($v) => (string) $v)                     // normaliza p/ string
            ->filter(fn($v) => array_key_exists($v, $dias))  // só os válidos no mapa
            ->map(fn($v) => (int) $v)                        // para ordenar numericamente
            ->sort()
            ->values();

        if ($horarios->isNotEmpty()) {
            $minKey = (string) $horarios->first();
            $maxKey = (string) $horarios->last();

            // Usa 'G:i' porque suas horas podem vir sem zero à esquerda (ex.: "7:30")
            $hrIni = Carbon::createFromFormat('G:i', $dias[$minKey])->format('H:i:s');
            $hrFim = Carbon::createFromFormat('G:i', $dias[$maxKey])->format('H:i:s');
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
            // mapa de nomes -> números do Carbon (0=domingo ... 6=sábado)
            $map = ['segunda' => 1, 'terca' => 2, 'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6, 'domingo' => 7];
            $selecionados = array_map(
                fn($k) => $map[$k] ?? null,
                (array) $request->input('dia_recorrencia', [])
            );
            $selecionados = array_filter($selecionados, fn($v) => $v !== null);

            if (!$dataFim) {
                return back()->withInput()->with('alert', 'Informe a data final para recorrência.');
            }

            $houveDuplicata = false;
            $inseridos = 0;

            /*recorrência*/
            try {
                /* RECORRÊNCIA */
                DB::beginTransaction();

                // garanta que $dataBase seja o início do período
                $dataBase = $dataInicio->copy();          // evita mutar o original
                $dataFim  = $dataFim->copy();             // (se já for Carbon)

                // Se o front envia dias 1..7 (seg=1..dom=7), use dayOfWeekIso
                $selecionadosInt = collect((array) $request->input('dia_recorrencia', []))
                    ->map(fn($v) => (int) $v)
                    ->filter(fn($v) => $v >= 1 && $v <= 7) // se não usar domingo, pode deixar <= 6
                    ->unique()
                    ->values()
                    ->all();

                $data = $dataBase->copy();
                for ($d = $dataInicio->copy(); $d->lte($dataFim); $d->addDay()) {
                    if (in_array($data->dayOfWeekIso, $selecionadosInt, true)) {

                        $duplicado = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
                            ->where('a.ID_CLINICA', $idClinica)
                            ->whereDate('a.DT_AGEND', $data->toDateString())
                            // se as colunas forem TIME, pode trocar por where('a.HR_AGEND_INI', $hrIni) etc.
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
                            break; // aborta toda a série
                        }

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
                            'RECORRENCIA'        => $recorrencia,
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

                        $inseridos++;
                    }
                    $data->addDay();
                }

                if ($houveDuplicata) {
                    DB::rollBack();
                    return back()->withInput()->with('alert', 'Já existe agendamento no mesmo horário e box para pelo menos uma das datas.');
                }

                DB::commit();
                return back()->with('success', "Agendamento de recorrência criado com sucesso. Ocorrências inseridas: {$inseridos}");
            } catch (\Throwable $e) {
                DB::rollBack();
                return back()->withInput()->with('alert', 'Erro ao criar recorrência: ' . $e->getMessage());
            }
        }

        /*pontual*/
        DB::beginTransaction();

        if ($request->recorrencia === '1') {
            $dt = $dataInicio->toDateString(); // ou derive de $request; pontual: um único dia

            $duplicadoPontual = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
                ->where('a.ID_CLINICA', $idClinica)
                ->whereDate('a.DT_AGEND', $dt)
                // se TIME, prefira where simples:
                //->where('a.HR_AGEND_INI', $hrIni)
                //->where('a.HR_AGEND_FIN', $hrFim)
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

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->leftjoin('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'a.ID_AGENDAMENTO')
            ->leftJoin('LYCEUM_BKP_PRODUCAO.dbo.LY_DISCIPLINA as ld', 'ld.DISCIPLINA', '=', 'la.DISCIPLINA')
            ->leftjoin('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->leftjoin('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->where('a.ID_CLINICA', '=', 2)
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

        return view('odontologia.create_agenda', compact('agenda'));
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
        $data = $request->validate([
            'disciplina'   => 'required|string',
            'turma'        => 'required|string',
            'data'         => 'required|integer|between:1,7',
            'boxes'        => 'required|array|min:1',
            'boxes.*'      => 'integer',
            'horarios'     => 'required|array|min:1',
            'horarios.*'   => 'regex:/^\d{1,2}:\d{2}$/', // HH:mm
        ], [
            'required' => 'Campo obrigatório.',
            'between'  => 'Valor inválido.',
            'regex'    => 'Horário inválido (HH:mm).',
        ]);

        $hrIni = $request->input('hr_ini');
        $hrFim = $request->input('hr_fim');

        DB::transaction(function () use ($data, $hrIni, $hrFim) {
            foreach ($data['boxes'] as $boxId) {
                DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->updateOrInsert(
                    [
                        'ID_CLINICA' => 2,
                        'ID_BOX'     => (int)$boxId,
                        'DISCIPLINA' => $data['disciplina'],
                        'TURMA'      => $data['turma'],
                        'DIA_SEMANA' => (int)$data['data'],
                        'HR_INICIO'  => $hrIni,
                        'HR_FIM'     => $hrFim,
                    ],
                    ['DT_CADASTRO' => DB::raw('SYSDATETIME()')]
                );
            }
        });

        return back()->with('success', 'Horários vinculados com sucesso!');
    }


    public function editBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        $BoxDiscipline = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)->first();

        if (!$BoxDiscipline) {
            return redirect('odontologia/criarboxdisciplina')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_box_discipline', compact('BoxDiscipline'));
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
