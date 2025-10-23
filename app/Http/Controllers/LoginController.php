<?php



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // 1) Validar inputs
        $data = $request->validate([
            'usuario' => ['required', 'string'],
            'senha'   => ['required', 'string'],
        ]);

        // 2) Validar credenciais na API externa
        $api = $this->getApiData([
            'username' => $data['usuario'],  // ajuste as chaves se necessário
            'password' => $data['senha'],
        ]);

        if (!$api['success']) {
            return back()
                ->withErrors(['login' => $api['message'] ?? 'Usuário/Senha inválidos'])
                ->withInput($request->only('usuario'));
        }

        // 3) Usuário local
        $user = DB::table('FAESA_CLINICA_USUARIO_GERAL')
            ->where('USUARIO', $data['usuario'])
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['login' => 'Usuário sem cadastro local.'])
                ->withInput($request->only('usuario'));
        }

        // 4) Status
        if (isset($user->STATUS) && mb_strtolower($user->STATUS) !== 'ativo') {
            return back()
                ->withErrors(['login' => 'Usuário inativo.'])
                ->withInput($request->only('usuario'));
        }

        // 5) Clínicas (se houver)
        $clinicas = [];
        if (isset($user->ID_CLINICA)) {
            $clinicas[] = (int) $user->ID_CLINICA;
        }

        $tipo = isset($user->TIPO) ? $user->TIPO : null;
        $nome = isset($user->NOME) ? $user->NOME : null;

        // 6) Regenerar e salvar sessão (ESSENCIAL)
        $request->session()->regenerate();
        session([
            'usuario'     => $user,         // seu middleware lê exatamente isso
            'nome'        => $nome,
            'clinicas'    => $clinicas,
            'tipo'        => $tipo,
            'SIT_USUARIO' => 'ATIVO',
        ]);

        // (Opcional) se usar guard do Laravel:
        // Auth::loginUsingId($user->ID);

        // 7) Gate por clínica (se precisar da 2)
        if (!empty($clinicas) && !in_array(2, $clinicas, true)) {
            session()->flush();
            return redirect()->route('loginGET')
                ->with('error', 'Usuário sem acesso à clínica de Odontologia.');
        }

        // 8) Redirecionar
        return redirect()->intended(route('menu_agenda'));
    }

    public function logout(Request $request)
    {
        session()->forget(['usuario', 'clinicas', 'SIT_USUARIO']);
        if (Auth::check()) Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('loginGET');
    }

    private function getApiData(array $credentials): array
    {
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');

        try {
            $resp = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => "Bearer {$apiKey}", // se a API exigir Bearer
            ])->timeout(5)->post($apiUrl, $credentials);

            if ($resp->successful()) {
                return ['success' => true, 'data' => $resp->json()];
            }

            return [
                'success' => false,
                'message' => $resp->json('message') ?? 'Falha na autenticação',
                'status'  => $resp->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Erro de comunicação com o servidor de autenticação: ' . $e->getMessage(),
            ];
        }
    }
}
