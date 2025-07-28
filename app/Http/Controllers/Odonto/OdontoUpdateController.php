<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdontoUpdateController extends Controller
{
    public function updatePatient(Request $request, $id)
    {
        $paciente = DB::table('FAESA_CLINICA_PACIENTE')->where('ID_PACIENTE', $id)->first();

        DB::table('FAESA_CLINICA_PACIENTE')
            ->where('ID_PACIENTE', $id)
            ->update([
                'NOME_COMPL_PACIENTE' => $request->input('nome'),
                'CPF_PACIENTE' => preg_replace('/\D/', '', $request->input('cpf')),
                'COD_SUS' => $request->input('cod_sus'),
                'DT_NASC_PACIENTE' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('dt_nasc'))->format('Y-m-d'),
                'SEXO_PACIENTE' => $request->input('sexo'),
                'CEP' => preg_replace('/\D/', '', $request->input('cep')),
                'ENDERECO' => $request->input('rua'),
                'END_NUM' => $request->input('numero'),
                'COMPLEMENTO' => $request->input('complemento'),
                'BAIRRO' => $request->input('bairro'),
                'MUNICIPIO' => $request->input('cidade'),
                'UF' => $request->input('estado'),
                'E_MAIL_PACIENTE' => $request->input('email'),
                'FONE_PACIENTE' => $request->input('celular'),
                'NOME_RESPONSAVEL' => $request->input('nome_resposavel'),
                'CPF_RESPONSAVEL' => $request->input('cpf_responsavel'),
                'OBSERVACAO' => $request->input('obs_laudo')
            ]);

        return redirect()->back()->with('success', 'Paciente atualizado com sucesso!');
    }

    public function updateService(Request $request, $idService)
    {
        $disciplinas = $request->input('disciplines', []);

        // Atualiza os dados do serviço
        DB::table('FAESA_CLINICA_SERVICO')
            ->where('ID_SERVICO_CLINICA', $idService)
            ->update([
                'ID_CLINICA' => 2,
                'SERVICO_CLINICA_DESC' => $request->input('descricao'),
                'VALOR_SERVICO' => $request->input('valor'),
                'ATIVO' => empty($disciplinas) ? 'N' : 'S',
            ]);

        // Remove todas as disciplinas associadas anteriormente
        DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')
            ->where('ID_SERVICO_CLINICA', $idService)
            ->delete();

        // Se houver disciplinas marcadas, associa novamente
        foreach ($disciplinas as $disciplina) {
            DB::table('FAESA_CLINICA_SERVICO_DISCIPLINA')->insert([
                'ID_SERVICO_CLINICA' => $idService,
                'DISCIPLINA' => $disciplina
            ]);
        }

        return redirect()->back()->with('success', 'Serviço atualizado com sucesso!');
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
        $idBox = $request->input('boxes');

        $descricaoLocal = DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->value('DESCRICAO');

        $valor = $request->input('valor') ? str_replace(',', '.', $request->input('valor')) : null;

        DB::table('FAESA_CLINICA_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $id)
            ->update([
                'ID_CLINICA' => 2,
                'ID_PACIENTE' => $request->input('ID_PACIENTE'),
                'ID_SERVICO' => $request->input('servico'),
                'DT_AGEND' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
                'DT_AGEND' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('date_end'))->format('Y-m-d'),
                'HR_AGEND_INI' => $request->input('hr_ini'),
                'HR_AGEND_FIN' => $request->input('hr_fim'),
                'STATUS_AGEND' => $request->input('status'),
                'ID_AGEND_REMARCADO' => $request->input('ID_AGEND_REMARCADO') ?: null,
                'RECORRENCIA' => $request->input('recorrencia'),
                'VALOR_AGEND' => $valor,
                'OBSERVACOES' => $request->input('obs'),
                'LOCAL' => $descricaoLocal
            ]);

        DB::table('FAESA_CLINICA_LOCAL_AGENDAMENTO')
            ->where('ID_AGENDAMENTO', $id)
            ->update([
                'ID_BOX' => $idBox
            ]);

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
