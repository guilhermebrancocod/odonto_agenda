<?php

namespace App\Http\Controllers\Odonto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            ->select('AUDITABLE_ID', 'OLD_VALUES', 'NEW_VALUES', 'created_at', 'updated_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows);
    }

    public function updateUser(Request $request, $userId)
    {

        $rules = [
            'nome'            => ['required', 'string', 'max:255', 'regex:/^[A-Za-zÀ-ÿ0-9\s]+$/'],
            'winusuario'      => ['nullable', 'string', 'max:50'],
            'tipo'            => ['nullable'],
        ];

        $messages = [
            'nome.required'          => 'O nome é obrigatório.',
            'winusuario.required'    => 'O usuario é obrigatório.',
            'tipo.required'          => 'O tipo é obrigatório.',
        ];

        $validator = Validator::make($rules, $messages);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('alert', 'Verifique os campos informados.');
        }

        DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->where('ID', $userId)
            ->update([
                'NOME'      => $request->input('nome'),
                'USUARIO'   => $request->input('winusuario'),
                'PESSOA'    => $request->input('pessoa'),
                'TIPO'      => $request->input('tipo'),
                'STATUS'    => $request->input('status'),
            ]);

        return redirect()->back()->with('success', 'Usuário atualizado com sucesso!');
    }

    public function buscarUsuarios(Request $request)
    {
        $usuarioId = $request->input('userId');

        $query = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select(
                'ID',
                'NOME',
                'USUARIO',
                'PESSOA',
                'TIPO',
                'STATUS',
                'TIPO'
            )
            ->where('ID_CLINICA', '=', 2);

        if ($usuarioId) {
            $query->where('ID', $usuarioId);
        }

        $user = $query->get();

        return response()->json($user);
    }

    public function buscarUsuariosLyceum(Request $request)
    {
        $search = $request->input('query'); // termo digitado no select2

        $query = DB::table('HADES.dbo.USUARIO')
            ->select('NOMEUSUARIO', 'USUARIO');

        if (!empty($search)) {
            $query->where('NOMEUSUARIO', 'like', '%' . $search . '%');
        }

        $users = $query->limit(20)->get(); // limit pra não sobrecarregar

        return response()->json($users);
    }

    public function buscarPessoaLyceum(Request $requet, $pessoa)
    {
        $pessoa = DB::table('HADES.dbo.USUARIO')
            ->where('USUARIO', $pessoa)
            ->select('NOMEUSUARIO', 'USUARIO')
            ->first();

        if (!$pessoa) {
            return response()->json([], 404); // não encontrou
        }

        return response()->json($pessoa);
    }

    public function selectUser(Request $request)
    {
        $query = $request->input('search-input');

        $selectUser = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select('NOME', 'USUARIO')
            ->where('ID_CLINICA', 2)
            ->where('NOME', 'like', '%' . $query . '%')
            ->get();

        return view('odontologia/consult_user', compact('selectUser', 'query'));
    }

    public function getUserId($userId, Request $request)
    {
        if (!is_numeric($userId)) {
            return response()->json(['error' => 'ID inválido'], 400);
        }

        $query = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->select('ID', 'NOME', 'USUARIO', 'PESSOA', 'TIPO', 'STATUS')
            ->where('STATUS', '=', 'Ativo')
            ->where('ID', '=', $userId);

        if ($request->has('query')) {
            $search = $request->query('query');
            $query->where('NOME', 'like', "%{$search}%");
        }

        $userId = $query->first();

        return response()->json($userId);
    }
}
