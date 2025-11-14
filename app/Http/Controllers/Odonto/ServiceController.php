<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
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

        $servico = DB::table('FAESA_CLINICA_SERVICO')->where('ID_SERVICO', $idService)->first();

        if (!$servico) {
            return redirect('/odontologia/consultarservico')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_service', compact('servico'));
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
            ->where('ID_SERVICO', $idService)
            ->update([
                'ID_CLINICA' => 2,
                'SERVICO_CLINICA_DESC' => $descricao,
                'VALOR_SERVICO' => $valorServico,
                'ATIVO' => $ativo
            ]);

        return back()->with('success', 'Procedimento atualizado com sucesso!');
    }

    public function listaServicosId(int $servicoId)
    {
        // serviço (sem depender de disciplina)
        $row = DB::table('FAESA_CLINICA_SERVICO as s')
            ->where('s.ID_SERVICO', $servicoId)
            ->select(
                's.ID_SERVICO as id',
                's.SERVICO_CLINICA_DESC as descricao',
                's.VALOR_SERVICO as valor',
                's.ATIVO as ativo'
            )
            ->limit(1)             // garante apenas UMA disciplina
            ->first();

        if (!$row) {
            return response()->json(['erro' => 'Serviço não encontrado'], 404);
        }
        return response()->json([
            'id'                => $row->id,
            'descricao'         => $row->descricao,
            'valor'             => $row->valor,
            'ativo'             => $row->ativo,
        ]);
    }

    public function procedimento(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_SERVICO')
            ->select('ID_SERVICO', 'SERVICO_CLINICA_DESC')
            ->where('ID_CLINICA', '=', 2)
            ->where('ATIVO', '=', 'S');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('SERVICO_CLINICA_DESC', 'like', '%' . $search . '%');
        }

        $procedimentos = $query->get();

        return response()->json($procedimentos);
    }

    public function fSelectService(Request $request)
    {
        $query_servico = $request->input('search-input');

        $selectService = DB::table('FAESA_CLINICA_SERVICO')
            ->select('SERVICO_CLINICA_DESC')
            ->where(function ($query) use ($query_servico) {
                $query->where('SERVICO_CLINICA_DESC', 'like', '%' . $query_servico . '%');
            })
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return view('odontologia/consult_servico', compact('selectService', 'query_servico'));
    }

    public function buscarProcedimentos(Request $request)
    {
        $query = $request->input('query');

        $procedimentos =  DB::table('FAESA_CLINICA_SERVICO')
            ->select(
                'FAESA_CLINICA_SERVICO.ID_SERVICO',
                'SERVICO_CLINICA_DESC',
                'VALOR_SERVICO',
                'ATIVO'
            )
            ->where('SERVICO_CLINICA_DESC', 'like', '%' . $query . '%')
            ->where('ID_CLINICA', '=', 2)
            ->get();

        return response()->json($procedimentos);
    }
}
