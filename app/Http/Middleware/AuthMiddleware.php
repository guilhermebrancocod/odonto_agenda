<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuarioGeral; // ajuste conforme seu modelo real
use App\Models\FaesaClinicaUsuario;      // usado no validarADM

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Nome da rota atual
        $routeName = optional($request->route())->getName();

        // Se a rota não tiver nome, deixa passar
        if (!$routeName) {
            return $next($request);
        }

        // Rotas liberadas sem autenticação
        $rotasLiberadas = [
            'loginGET',
            'loginPOST',
            'logout',
            'alunoLoginGet',
            'alunoLoginPost',
            'alunoLogout',
            'professorLoginGet',
            'professorLoginPost',
            'professorLogout',
        ];

        // Se a rota estiver liberada, segue
        if (in_array($routeName, $rotasLiberadas, true)) {
            // Se for uma rota de POST de login, processa autenticação aqui
            if (in_array($routeName, ['loginPOST','alunoLoginPost','professorLoginPost'], true)) {
                return $this->processarLogin($request, $routeName, $next);
            }
            return $next($request);
        }

        // Já logado?
        if (session()->has('usuario') || session()->has('aluno') || session()->has('professor')) {
            return $next($request);
        }

        // Não logado: redireciona para o login correto por prefixo
        if (str_starts_with($routeName, 'aluno')) {
            return redirect()->route('alunoLoginGet');
        }

        if (str_starts_with($routeName, 'professor')) {
            return redirect()->route('professorLoginGet');
        }

        return redirect()->route('loginGET');
    }

    /**
     * Processa autenticação para as rotas de login POST.
     */
    private function processarLogin(Request $request, string $routeName, Closure $next)
    {
        $credentials = [
            'username' => $request->input('login'),
            'password' => $request->input('senha'),
        ];

        // Chama API (única; ajuste se houver endpoints diferentes por perfil)
        $response = $this->getApiData($credentials);

        if (!$response['success']) {
            session()->flush();
            return redirect()->back()->with('error', $response['message'] ?? 'Credenciais Inválidas');
        }

        // Valida usuário no banco
        $validacao = $this->validarUsuario($credentials);

        if (is_null($validacao)) {
            return redirect()->back()->with('error', 'Usuário Inativo');
        }

        // Seta a sessão conforme a rota de login utilizada
        if ($routeName === 'alunoLoginPost') {
            if (($validacao->TIPO ?? null) === 'aluno') {
                session(['aluno' => $validacao]);
            } else {
                return redirect()->back()->with('error', 'Usuário deve ser aluno');
            }
        } elseif ($routeName === 'professorLoginPost') {
            if (($validacao->TIPO ?? null) === 'Professor') {
                session(['professor' => $validacao]);
            } else {
                return redirect()->back()->with('error', 'Usuário deve ser Professor');
            }
        } else { // loginPOST padrão (recepção/usuário geral)
            // Se quiser checar recepção especificamente, troque a condição abaixo
            session(['usuario' => $validacao]);
        }

        // Autenticado, segue o fluxo normal
        return $next($request);
    }

    /**
     * Chamada à API de autenticação.
     */
    private function getApiData(array $credentials): array
    {
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');

        try {
            $http = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => $apiKey,
            ])->timeout(5);

            $resp = $http->post($apiUrl, $credentials);

            if ($resp->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'message' => $resp->json('message') ?? 'Falha na autenticação',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Valida usuário ativo no banco.
     * Retorna o modelo ou null.
     */
    private function validarUsuario(array $credentials): ?FaesaClinicaUsuarioGeral
    {
        $username = $credentials['username'] ?? null;

        if (!$username) {
            return null;
        }

        return FaesaClinicaUsuarioGeral::where('USUARIO', $username)
            ->where('STATUS', 'Ativo')
            ->first();
    }

    /**
     * Exemplo de validação de ADM (mantida sua ideia).
     */
    private function validarADM(array $credentials)
    {
        $usuario = $credentials['username'] ?? null;
        if (!$usuario) {
            return null;
        }

        $usuarioADM = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $usuario)
            ->where('SIT_USUARIO', 'Ativo')
            ->get();

        return $usuarioADM->isNotEmpty() ? $usuarioADM : null;
    }
}