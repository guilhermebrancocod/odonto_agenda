<?php

namespace App\Http\Controllers\Odonto;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoUpdateController extends Controller
{
    public function updatePatient(Request $request, $id)
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
            'nome'            => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
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
            'cep'             => ['nullable', 'regex:/^\d{8}$/'],
            'rua'             => ['nullable', 'string', 'max:255'],
            'numero'          => ['nullable', 'string', 'max:10'],
            'complemento'     => ['nullable', 'string', 'max:100'],
            'bairro'          => ['nullable', 'string', 'max:100'],
            'cidade'          => ['nullable', 'string', 'max:100'],
            'estado'          => ['nullable', 'alpha', 'size:2'],     // UF
            'email'           => ['nullable', 'email:rfc', 'max:100'],
            'celular'         => ['nullable', 'regex:/^\d{10,11}$/'], // 10 ou 11 dígitos
            'cod_sus'         => ['nullable', 'regex:/^\d{15}$/'],   // SUS geralmente 15 dígitos
            'nome_resposavel' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
            'cpf_responsavel' => ['nullable', 'regex:/^\d{11}$/'],
            'obs_laudo'       => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'nome.required'          => 'O nome é obrigatório.',
            'nome.regex'             => 'O nome não pode conter caracteres especiais.',
            'cpf.required'           => 'O CPF é obrigatório.',
            'cpf.regex'              => 'Informe um CPF com 11 dígitos (apenas números).',
            'nome_responsavel'       => 'Responsável obrigatório para menores de 18 anos.',
            'dt_nasc.required'       => 'A data de nascimento é obrigatória.',
            'dt_nasc.date_format'    => 'Use o formato de data dd/mm/aaaa.',
            'dt_nasc.before'         => 'A data de nascimento deve ser anterior a hoje.',
            'sexo.required'          => 'Informe o sexo.',
            'sexo.in'                => 'Sexo inválido (use M ou F).',
            'cep.regex'              => 'CEP deve ter 8 dígitos (apenas números).',
            'estado.alpha'           => 'UF deve conter apenas letras.',
            'estado.size'            => 'UF deve ter 2 letras.',
            'email.email'            => 'E-mail inválido.',
            'celular.regex'          => 'Celular deve ter 10 ou 11 dígitos (apenas números).',
            'cod_sus.regex'          => 'Cartão SUS deve ter 15 dígitos.',
            'nome_resposavel.regex' => 'O nome do responsável inválido.',
            'cpf_responsavel.regex'  => 'CPF do responsável deve ter 11 dígitos.',
            'obs_laudo.max'          => 'Laudo deve ter no máximo 255 caracteres.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

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

        $paciente = DB::table('FAESA_CLINICA_PACIENTE')
            ->where('ID_PACIENTE', $id)
            ->first();

        if (!$paciente) {
            return redirect()->back()->with('error', 'Paciente não encontrado.');
        }

        DB::table('FAESA_CLINICA_PACIENTE')
            ->where('ID_PACIENTE', $id)
            ->update([
                'NOME_COMPL_PACIENTE' => $request->input('nome'),
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

        return redirect()->back()->with('success', 'Paciente atualizado com sucesso!');
    }

    public function updateProcedures(Request $request, $idService)
    {
        $request->validate([
            'descricao' => ['required', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
        ], [
            'descricao.required' => 'O campo descrição é obrigatório.',
            'descricao.regex' => 'A descrição não pode conter caracteres especiais.',
        ]);
        $valorServico = $request->input('valor');
        $descricao = $request->input('descricao');
        $ativo = $request->input('ativo');

        // Atualiza os dados do serviço
        DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', $idService)
            ->update([
                'ID_CLINICA' => 2,
                'SERVICO_CLINICA_DESC' => $descricao,
                'VALOR_SERVICO' => $valorServico,
                'ATIVO' => $ativo
            ]);

        return back()->with('success', 'Procedimento atualizado com sucesso!');
    }

    public function updateBox(Request $request, $idBox)
    {

        $box = DB::table('FAESA_CLINICA_BOXES')->where('ID_BOX_CLINICA', $idBox)->first();

        DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->update([
                'ID_CLINICA' => 2,
                'DESCRICAO' => $request->input('descricao'),
                'ATIVO' => $request->input('status')
            ]);

        return redirect()->back()->with('success', 'Box atualizado com sucesso!');
    }

    public function updateAgenda(Request $request, $id)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')->where('ID_AGENDAMENTO', $id)->first();
        if (!$agenda) {
            return back()->withErrors('Agendamento não encontrado.');
        }

        $idClinica = 2;
        $idBox     = $request->input('ID_BOX');
        $diaStr = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d');
        $dateEnd = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->format('Y-m-d');

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

        $valor_convert = $request->input('valor');
        if ($valor_convert === null || $valor_convert === '') {
            $valor_convert = null;
        } else {
            $tmp = str_replace(['R$', ' ', '.'], '', $valor_convert);
            $valor_convert = (float) str_replace(',', '.', $tmp);
        }

        $descricaoLocal = DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->value('DESCRICAO') ?? null;

        $disciplina = DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', (int) $request->input('procedimento'))
            ->value('DISCIPLINA');

        $servico = DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', (int) $request->input('procedimento')) // se o PK for outro (ex.: ID_BOX_DISCIPLINA), ajuste aqui
            ->value('ID_SERVICO_CLINICA');

        DB::table('FAESA_CLINICA_AGENDAMENTO as a')
            ->join('FAESA_CLINICA_LOCAL_AGENDAMENTO as la', 'la.ID_AGENDAMENTO', '=', 'A.ID_AGENDAMENTO')
            ->join('FAESA_CLINICA_BOXES as cb', 'cb.ID_BOX_CLINICA', '=', 'la.ID_BOX')
            ->where('la.ID_AGENDAMENTO', $id)
            ->update([
                'ID_CLINICA' => $idClinica,
                'ID_PACIENTE' => $request->input('ID_PACIENTE'),
                'ID_SERVICO' => $servico,
                'DT_AGEND' => $diaStr,
                'DT_AGEND_FINAL' => $dateEnd,
                'HR_AGEND_INI' => $hrIni,
                'HR_AGEND_FIN' => $hrFim,
                'STATUS_AGEND' => $request->input('status'),
                'ID_AGEND_REMARCADO' => $request->input('ID_AGEND_REMARCADO') ?: null,
                'RECORRENCIA' => $request->input('recorrencia'),
                'VALOR_AGEND' => $valor_convert,
                'OBSERVACOES' => $request->input('obs'),
                'LOCAL' => $descricaoLocal
            ]);

        $updLocal = ['DISCIPLINA' => $request->input('disciplina')];

        if ($request->filled('ID_BOX')) {
            $updLocal['ID_BOX'] = (int) $request->input('ID_BOX');
        }

        /*DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $id)
            ->update($updLocal);

        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $id)
            ->update([
                'ID_BOX'     => $descricaoLocal,
                'DISCIPLINA' => $disciplina,
            ]);*/

        return redirect()->back()->with('success', 'Agendamento atualizado com sucesso!');
    }

    public function editStatus(Request $request, $agendaId)
    {

        $agenda = DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $agendaId)
            ->update([
                'STATUS_AGEND' => $request->input('status'),
                'MENSAGEM' => $request->input('mensagem')
            ]);

        return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso']);
    }

    public function updateBoxDiscipline(Request $request, $idBoxDiscipline)
    {
        $disciplina = $request->input('disciplina');
        $diaSemana = $request->input('dia_semana');
        $hrInicio = $request->input('hr_inicio');
        $hrFim = $request->input('hr_fim');
        $boxesSelecionados = $request->input('boxes', []);

        // Busca boxes já cadastrados no banco para essa disciplina
        $boxesAntigos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->pluck('ID_BOX')
            ->toArray();

        // Calcula boxes a adicionar (estão no novo, mas não estavam no banco)
        $boxesParaAdicionar = array_diff($boxesSelecionados, $boxesAntigos);

        // Calcula boxes a remover (estavam no banco, mas não estão no novo)
        $boxesParaRemover = array_diff($boxesAntigos, $boxesSelecionados);

        // Remove os desmarcados
        if (!empty($boxesParaRemover)) {
            DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
                ->whereIn('ID_BOX', $boxesParaRemover)
                ->delete();
        }

        // Adiciona os novos
        foreach ($boxesParaAdicionar as $boxId) {
            DB::table('FAESA_CLINICA_BOX_DISCIPLINA')->insert([
                'ID_CLINICA' => 2,
                'ID_BOX' => $boxId,
                'DISCIPLINA' => $disciplina,
                'DIA_SEMANA' => $diaSemana,
                'HR_INICIO' => $hrInicio,
                'HR_FIM' => $hrFim,
                'DT_CADASTRO' => now()
            ]);
        }

        // Atualiza os campos comuns da disciplina (caso precise atualizar o horário, por exemplo)
        DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->where('ID_BOX_DISCIPLINA', $idBoxDiscipline)
            ->update([
                'DISCIPLINA' => $disciplina,
                'DIA_SEMANA' => $diaSemana,
                'HR_INICIO' => $hrInicio,
                'HR_FIM' => $hrFim,
                'DT_CADASTRO' => now()
            ]);

        return redirect()->back()->with('success', 'Disciplinas e boxes atualizados com sucesso!');
    }
}
