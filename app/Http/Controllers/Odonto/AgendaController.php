<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AgendaController extends Controller
{
    /*public function showFormAgenda()
    {
        return view('odontologia/create_agenda');
    }*/

    public function createAgenda(Request $request)
    {
        $rules = [
            'ID_PACIENTE'     => ['required'],
            'status'          => ['required'],   // 11 dígitos
            'date'            => ['bail', 'required', 'date_format:d/m/Y'],
            'date_end'        => ['bail', 'required', 'date_format:d/m/Y'],

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

            'frequencia.required_if:recorrencia,2|in:1,2,3' => 'A frequência é obrigatória para recorrência.',
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

        $horarios = collect((array) $request->input('horarios', []))
            ->map(fn($v) => (string) $v)                     // normaliza p/ string
            ->map(fn($v) => (int) $v)                        // para ordenar numericamente
            ->sort()
            ->values();

        if ($horarios->isNotEmpty()) {
            $hrIni = Carbon::createFromFormat('H:i', $request->input('hr_ini'))->format('H:i:s');
            $hrFim = Carbon::createFromFormat('H:i', $request->input('hr_fim'))->format('H:i:s');
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

        /*PERIODO DO AGENDAMENTO*/
        DB::beginTransaction();

        if ($request->recorrencia === 1) {
            $dt = $dataInicio->toDateString();

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
        } else {
            $frequencia = (int) $request->input('freq'); // 1=semanal, 2=quinzenal, 3=mensal
            $rotuloRecorrencia = match ($frequencia) {
                1 => 'semanal',
                2 => 'quinzenal',
                3 => 'mensal',
                default => 'semanal',
            };

            $cursor = $dataInicio->copy()->startOfDay();
            $fim     = $dataFim->copy()->endOfDay();


            try {
                $criados = 0;

                while ($cursor->lte($fim)) {
                    $dt = $cursor->toDateString();

                    // Checagem de conflito por ocorrência
                    $existe = DB::table('FAESA_CLINICA_AGENDAMENTO as a')
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

                    if ($existe) {
                        DB::rollBack();
                        return back()->withInput()->with(
                            'alert',
                            'Conflito: já existe agendamento no mesmo box/horário em ' . $cursor->format('d/m/Y') . '.'
                        );
                    }

                    // Insere a ocorrência do dia
                    $idAgendamento = DB::table('FAESA_CLINICA_AGENDAMENTO')->insertGetId([
                        'ID_CLINICA'         => $idClinica,
                        'ID_PACIENTE'        => (int) $request->input('ID_PACIENTE'),
                        'ID_SERVICO'         => $idServico,
                        'DT_AGEND'           => $dt,
                        'DT_AGEND_FINAL'     => $dt, // cada ocorrência é pontual
                        'HR_AGEND_INI'       => $hrIni,
                        'HR_AGEND_FIN'       => $hrFim,
                        'STATUS_AGEND'       => $request->input('status'),
                        'ID_AGEND_REMARCADO' => null,
                        'RECORRENCIA'        => $rotuloRecorrencia,
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

                    $criados++;

                    // Avança o cursor conforme a frequência
                    if ($frequencia === 1) {
                        $cursor->addWeek();           // semanal
                    } elseif ($frequencia === 2) {
                        $cursor->addWeeks(2);         // quinzenal
                    } else {
                        $cursor->addMonthNoOverflow(); // mensal (evita pular para mês seguinte errado em 29-31)
                    }
                }

                DB::commit();
                return back()->with('success', "Agendamentos recorrentes criados: {$criados} ocorrência(s).");
            } catch (\Throwable $e) {
                DB::rollBack();
                report($e);
                return back()->withInput()->with('alert', 'Erro ao criar recorrência: ' . $e->getMessage());
            }
        }

        DB::commit();
        return back()->with('success', 'Agendamento pontual criado com sucesso.');
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

        return view('odontologia.create_agenda', compact('agenda', 'alunosSelecionados' /*, 'alunosMap'*/));
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
                'HR_AGEND_INI' => $request->input('hr_ini'),
                'HR_AGEND_FIN' => $request->input('hr_fim'),
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

        if ($request->input('encaminhamento') === '1') {
            $disciplinaEnc = $request->input('disciplina-enc');
            DB::table('FAESA_CLINICA_AGENDAMENTO_ENCAMINHAMENTO')->insert([
                'ID_AGENDAMENTO' => $id,
                'DISCIPLINA'     => $disciplinaEnc,
                'STATUS'     => 'DISPONIVEL',
                'CREATED_AT'     => now(),
                'UPDATED_AT'     => now()
            ]);
        } else {
            $disciplinaEnc = null;
        }

        return redirect()->back()->with('success', 'Agendamento atualizado com sucesso!');
    }

    public function defineLocalAtendimento($agendaId, $boxId)
    {
        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')->inserGetId([
            'ID_AGENDAMENTO' => $agendaId,
            'ID_BOX' => $boxId
        ]);
    }
}
