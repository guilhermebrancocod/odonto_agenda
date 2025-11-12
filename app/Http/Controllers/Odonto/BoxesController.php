<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoxesController extends Controller
{
    public function createBox(Request $request)
    {
        DB::table('FAESA_CLINICA_BOXES')->insertGetId([
            'DESCRICAO' => $request->input('descricao'),
            'ATIVO' => $request->input('status'),
        ]);
        return redirect()->back()->with('success', 'Box cadastrado com sucesso!');
    }

    public function getBoxeId($boxId, Request $request)
    {
        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA', 'ATIVO')
            ->where('FAESA_CLINICA_BOXES.ID_BOX_CLINICA', '=', $boxId);

        $boxes = $query->get();

        return response()->json($boxes);
    }

    public function getBoxes(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA', 'ATIVO')
            ->where('FAESA_CLINICA_BOXES.ATIVO', '=', 'S');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query
            ->where('FAESA_CLINICA_BOXES.DESCRICAO', 'like', '%' . $search . '%');
        }

        $boxes = $query->get();

        return response()->json($boxes);
    }

    public function editBox($idBox)
    {
        $box = DB::table('FAESA_CLINICA_BOXES')->where('ID_BOX_CLINICA', $idBox)->first();

        if (!$box) {
            return redirect('odontologia/consultarbox')->with('error', 'Serviço não encontrado.');
        }

        return view('odontologia.create_box', compact('box'));
    }

    public function updateBox(Request $request, $idBox)
    {

        $box = DB::table('FAESA_CLINICA_BOXES')->where('ID_BOX_CLINICA', $idBox)->first();

        DB::table('FAESA_CLINICA_BOXES')
            ->where('ID_BOX_CLINICA', $idBox)
            ->update([
                'DESCRICAO' => $request->input('descricao'),
                'ATIVO' => $request->input('status')
            ]);

        return redirect()->back()->with('success', 'Box atualizado com sucesso!');
    }

    public function buscarBoxes(Request $request)
    {
        $boxesId = $request->input('boxesId');

        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select(
                'DESCRICAO',
                'ATIVO',
                'ID_BOX_CLINICA'
            );

        if ($boxesId) {
            $query->where('ID_BOX_CLINICA', $boxesId);
        }

        $boxes = $query->get();

        return response()->json($boxes);
    }

    public function fSelectBox(Request $request)
    {
        $query_box = $request->input('search-input');

        $selectBox = DB::table('FAESA_CLINICA_BOXES')
            ->selectRaw('top 10 DESCRICAO, ID_BOX_CLINICA')
            ->where(function ($query) use ($query_box) {
                $query->where('DESCRICAO', 'like', '%' . $query_box . '%');
            })
            ->orderBy('DESCRICAO')
            ->get();

        return view('odontologia/consult_box', compact('selectBox', 'query_box'));
    }

    public function getBoxesId($boxId, Request $request)
    {
        if (!is_numeric($boxId)) {
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA', 'ATIVO')
            ->where('ATIVO', '=', 'S')
            ->where('ID_BOX_CLINICA', '=', $boxId);

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('DESCRICAO', 'like', "%{$search}%");
        }

        $boxeId = $query->first();

        return response()->json($boxeId);
    }

    public function replaceBoxDiscipline(Request $request)
    {
        $rows = DB::table('FAESA_CLINICA_BOXES')
            ->where('ATIVO', '=', 'S')
            ->select('ID_BOX_CLINICA', 'DESCRICAO')
            ->get();

        return response()->json($rows);
    }

    public function updateReplaceBox(Request $request, int $id)
    {
        $validated = $request->validate([
            'novo_box_id'        => ['required', 'integer'],
            'box_disciplina_id'  => ['nullable', 'integer'], // opcional, já vem na URL
        ]);

        $novoBoxId = (int) $validated['novo_box_id'];
        $boxDisciplinaId = (int) ($validated['box_disciplina_id'] ?? $id);


        // Busca o box atual para evitar update desnecessário
        $atual = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
            ->select('ID_BOX')
            ->where('ID_BOX_DISCIPLINA', $boxDisciplinaId)
            ->first();

        if (!$atual) {
            return response()->json(['ok' => false, 'message' => 'Box/Disciplina não encontrado.'], 404);
        }
        if ((int)$atual->ID_BOX === $novoBoxId) {
            return response()->json(['ok' => true, 'message' => 'Nada a alterar (mesmo box).']);
        }

        try {
            $result = DB::transaction(function () use ($boxDisciplinaId, $novoBoxId) {
                // Atualiza o box “pai” (grade/agenda)
                $updDisc = DB::table('FAESA_CLINICA_BOX_DISCIPLINA')
                    ->where('ID_BOX_DISCIPLINA', $boxDisciplinaId)
                    ->update([
                        'ID_BOX'     => $novoBoxId,
                        'UPDATED_AT' => DB::raw('GETDATE()'), // SQL Server
                    ]);

                // Propaga o novo box para todos os alunos atrelados àquele box_disciplina
                $updAlunos = DB::table('FAESA_CLINICA_BOX_DISCIPLINA_ALUNO')
                    ->where('ID_BOX_DISCIPLINA', $boxDisciplinaId)
                    ->update([
                        'ID_BOX'     => $novoBoxId,
                        'UPDATED_AT' => DB::raw('GETDATE()'),
                    ]);

                return ['upd_disciplina' => $updDisc, 'upd_alunos' => $updAlunos];
            });

            return response()->json([
                'ok' => true,
                'message' => 'Box trocado com sucesso.',
                'rows' => $result, // {upd_disciplina: X, upd_alunos: Y}
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Erro ao trocar o box.',
            ], 500);
        }
    }
}
