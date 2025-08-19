<?php

namespace App\Http\Controllers\Odonto;

use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            'nome_resposavel' => ['nullable', 'string', 'max:100'],
            'cpf_responsavel' => ['nullable', 'regex:/^\d{11}$/'],
            'obs_laudo'       => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'nome.required'          => 'O nome é obrigatório.',
            'cpf.required'           => 'O CPF é obrigatório.',
            'cpf.regex'              => 'Informe um CPF com 11 dígitos (apenas números).',
            'dt_nasc.required'       => 'A data de nascimento é obrigatória.',
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
        $idPaciente = DB::table('FAESA_CLINICA_PACIENTE')->insertGetId([
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
            'NOME_RESPONSAVEL'    => $request->input('nome_resposavel'),
            'CPF_RESPONSAVEL'     => $request->input('cpf_responsavel'),
            'OBSERVACAO'          => $request->input('obs_laudo'),
        ]);

        return back()->with('success', 'Paciente criado com sucesso!');
    }

    public function showFormAgenda()
    {
        return view('odontologia/create_agenda');
    }

    public function fCreateAgenda(Request $request)
    {
        // 1) Validação
        $validator = Validator::make($request->all(), [
            'ID_PACIENTE'            => ['required', 'integer', 'exists:FAESA_CLINICA_PACIENTE,ID_PACIENTE'],
            'ID_BOX'                 => ['required', 'integer', 'exists:FAESA_CLINICA_BOXES,ID_BOX_CLINICA'],
            'servico'                => ['required', 'integer', 'exists:FAESA_CLINICA_SERVICO_DISCIPLINA,ID'],
            'date'                   => ['required', 'date_format:d/m/Y'],
            'date_end'               => ['nullable', 'date_format:d/m/Y'],
            'hr_ini'                 => ['required', 'date_format:H:i'],
            'hr_fim'                 => ['required', 'date_format:H:i', 'after:hr_ini'],
            'status'                 => ['required', 'string'],
            'recorrencia'            => ['required', 'in:pontual,recorrencia'],
            'dia_semana'             => ['required_if:recorrencia,recorrencia', 'array'],
            'valor'                  => ['nullable', 'regex:/^\s*R?\$?\s*\d{1,3}(\.\d{3})*(,\d{2})?\s*$/'],
        ], [
            'dia_semana.required_if' => 'Selecione ao menos um dia da semana para recorrência.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('alert', 'Verifique os campos do agendamento.');
        }

        $idClinica = 2;
        $idBox     = (int) $request->input('ID_BOX');

        // 2) Serviço pai e disciplina
        $servDisc = DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
            ->where('ID', (int) $request->input('servico'))
            ->first();

        if (!$servDisc) return back()->withInput()->with('alert', 'Serviço/Disciplina inválido.');

        $idServicoPai = (int) $servDisc->ID_SERVICO_CLINICA;
        $disciplina   = $servDisc->DISCIPLINA;

        $existePai = DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', $idServicoPai)
            ->exists();

        if (!$existePai) return back()->withInput()->with('alert', 'Serviço pai não encontrado.');

        // 3) Conversões
        $dataInicio = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))->startOfDay();
        $dataFim    = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->startOfDay();

        $hrIni = \Carbon\Carbon::createFromFormat('H:i', $request->hr_ini)->format('H:i:s');
        $hrFim = \Carbon\Carbon::createFromFormat('H:i', $request->hr_fim)->format('H:i:s');

        $rawValor = $request->input('valor');
        $valor_convert = null;
        if ($rawValor !== null && $rawValor !== '') {
            $tmp = str_replace(['R$', ' ', '.'], '', $rawValor);
            $valor_convert = (float) str_replace(',', '.', $tmp);
        }

        $descricaoLocal = DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->value('DESCRICAO');

        // 4) Lógica
        if ($request->recorrencia === 'recorrencia') {
            // mapa de nomes -> números do Carbon (0=domingo ... 6=sábado)
            $map = ['domingo' => 0, 'segunda' => 1, 'terca' => 2, 'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6];
            $selecionados = array_map(
                fn($k) => $map[$k] ?? null,
                (array) $request->input('dia_semana', [])
            );
            $selecionados = array_filter($selecionados, fn($v) => $v !== null);

            if (!$dataFim) {
                return back()->withInput()->with('alert', 'Informe a data final para recorrência.');
            }

            $data = $dataInicio->copy();
            $dateEnd = $dataFim->copy();
            $inseridos = 0;
            DB::beginTransaction();
            try {
                while ($data->lte($dataFim)) {
                    if (in_array($data->dayOfWeek, $selecionados, true)) {
                        // checa conflito nesta data
                        $conflito = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
                            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as l', 'l.ID_AGENDAMENTO', '=', 'a.ID_AGENDAMENTO')
                            ->whereDate('a.DT_AGEND', $data->toDateString())
                            ->whereDate('a.DT_AGEND', $dateEnd->toDateString())
                            ->where('a.ID_CLINICA', $idClinica)
                            ->where('l.ID_BOX', $idBox)
                            ->whereRaw('CAST(a.HR_AGEND_INI AS time) < CAST(? AS time)', [$hrFim])
                            ->whereRaw('CAST(a.HR_AGEND_FIN AS time) > CAST(? AS time)', [$hrIni])
                            ->exists();

                        if ($conflito) {
                            // pule ou trate como erro; aqui vou pular e continuar:
                            $data->addDay();
                            continue;
                        }

                        $idAg = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                            'ID_CLINICA'         => $idClinica,
                            'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                            'ID_SERVICO'         => $idServicoPai,
                            'DT_AGEND'           => $data->toDateString(),
                            'DT_AGEND_FINAL'     => $dateEnd->toDateString(),
                            'HR_AGEND_INI'       => $hrIni,
                            'HR_AGEND_FIN'       => $hrFim,
                            'STATUS_AGEND'       => $request->input('status'),
                            'ID_AGEND_REMARCADO' => null,
                            'RECORRENCIA'        => 'recorrencia',
                            'VALOR_AGEND'        => $valor_convert,
                            'OBSERVACOES'        => $request->input('obs'),
                            'LOCAL'              => $descricaoLocal,
                        ]);

                        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->insert([
                            'ID_AGENDAMENTO' => $idAg,
                            'ID_BOX'         => $idBox,
                            'DISCIPLINA'     => $disciplina,
                        ]);

                        $inseridos++;
                    }
                    $data->addDay();
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                return back()->withInput()->with('alert', 'Erro ao criar recorrência: ' . $e->getMessage());
            }

            return back()->with('success', "Recorrência criada! Ocorrências inseridas: {$inseridos}");
        }

        // pontual
        $conflitoPontual = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as l', 'l.ID_AGENDAMENTO', '=', 'a.ID_AGENDAMENTO')
            ->whereDate('a.DT_AGEND', $dataInicio->toDateString())
            ->where('a.ID_CLINICA', $idClinica)
            ->where('l.ID_BOX', $idBox)
            ->whereRaw('CAST(a.HR_AGEND_INI AS time) < CAST(? AS time)', [$hrFim])
            ->whereRaw('CAST(a.HR_AGEND_FIN AS time) > CAST(? AS time)', [$hrIni])
            ->exists();

        if ($conflitoPontual) {
            return back()->withInput()->with('alert', 'Conflito: já existe agendamento no mesmo box e horário.');
        }

        $idAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
            'ID_CLINICA'         => $idClinica,
            'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
            'ID_SERVICO'         => $idServicoPai,
            'DT_AGEND'           => $dataInicio->toDateString(),
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
        ]);

        return back()->with('success', 'Agendamento realizado com sucesso!');
    }

    public function editPatient($pacienteId)
    {
        $paciente = DB::table('FAESA_CLINICA_PACIENTE')->where('ID_PACIENTE', $pacienteId)->first();

        if (!$paciente) {
            abort(404);
        }

        return view('odontologia/create_patient', compact('paciente'));
    }

    public function editAgenda($agendaId)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->leftjoin('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'a.ID_AGENDAMENTO')
            ->leftjoin('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->leftjoin('FAESA_CLINICA_PACIENTE as p', 'p.ID_PACIENTE', '=', 'a.ID_PACIENTE')
            ->join('FAESA_CLINICA_SERVICO as s', 's.ID_SERVICO_CLINICA', '=', 'a.ID_SERVICO')
            ->where('a.ID_CLINICA', '=', 2)
            ->where('a.ID_AGENDAMENTO', $agendaId)
            ->first();

        if (!$agenda) {
            abort(404);
        }

        return view('odontologia.create_agenda', compact('agenda'));
    }

    public function createService(Request $request)
    {
        $request->validate([
            'valor' => ['regex:/^\d+(\.\d{1,2})?$/'],
            'descricao' => ['required', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
        ], [
            'valor.regex' => 'O valor deve conter apenas números (e opcionalmente decimais).',
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.regex' => 'A descrição não pode conter caracteres especiais.',
        ]);

        $valorServico = $request->input('valor');
        $descricao = $request->input('descricao');

        $disciplines = $request->input('disciplines');

        $idService = DB::table('FAESA_CLINICA_SERVICO')->insertGetId([
            'ID_CLINICA' => 2,
            'SERVICO_CLINICA_DESC' => $descricao,
            'COD_INTERNO_SERVICO_CLINICA' => 0,
            'VALOR_SERVICO' => $valorServico,
            'ATIVO' => $request->input('ativo')
        ]);

        foreach ($disciplines as $disciplina) {
            DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')->insert([
                'ID_SERVICO_CLINICA' => $idService,
                'DISCIPLINA' => $disciplina
            ]);
        }

        return redirect()->back()->with('success', 'Serviço cadastrado com sucesso!');
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
        $boxes = $request->input('boxes');

        if (!$boxes || count($boxes) === 0) {
            return redirect()->back()->with('error', 'Nenhum box foi selecionado.');
        }

        foreach ($boxes as $boxId) {
            DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insertGetId([
                'ID_CLINICA' => 2,
                'ID_BOX' => $boxId,
                'DISCIPLINA' => $request->input('disciplina'),
                'DIA_SEMANA' => $request->input('dia_semana'),
                'HR_INICIO' => $request->input('hr_inicio'),
                'HR_FIM' => $request->input('hr_fim'),
                'DT_CADASTRO' => now(),

            ]);
        }
        return redirect()->back()->with('success', ' Disciplina e dia da semana vinculado a um ou mais boxes!');
    }

    public function editBoxDiscipline(Request $request, $idBoxDiscipline)
    {

        $BoxDiscipline = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)->first();

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
}
