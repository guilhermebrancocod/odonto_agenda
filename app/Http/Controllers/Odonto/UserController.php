<?php

namespace App\Http\Controllers\Odonto;

use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\Odontologia\AuditLogger;

class UserController extends Controller
{

    public function editUser($userId)
    {
        $user = DB::table('FAESA_CLINICA_USUARIO_GERAL')->where('ID', $userId)->first();

        if (!$user) {
            abort(404);
        }

        return view('odontologia/create_user', compact('user'));
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
