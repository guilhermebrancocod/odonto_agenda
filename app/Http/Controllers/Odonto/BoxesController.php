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
            'ID_CLINICA' => 2,
            'DESCRICAO' => $request->input('descricao'),
            'ATIVO' => $request->input('status'),
        ]);
        return redirect()->back()->with('success', 'Box cadastrado com sucesso!');
    }

    public function getBoxes(Request $request)
    {
        $query = DB::table('FAESA_CLINICA_BOXES')
            ->select('DESCRICAO', 'ID_BOX_CLINICA', 'ATIVO');

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('FAESA_CLINICA_BOXES.DESCRICAO', 'like', '%' . $search . '%');
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
                'ID_CLINICA' => 2,
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
            )
            ->where('ID_CLINICA', '=', 2);

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
            ->select('DESCRICAO')
            ->where(function ($query) use ($query_box) {
                $query->where('DESCRICAO', 'like', '%' . $query_box . '%');
            })
            ->where('ID_CLINICA', '=', 2)
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
}
