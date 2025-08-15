<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuarioGeral;
use Termwind\Components\Dd;

class AuthPsicologoMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();

        $rotasLiberadas = ['loginPsicologoGET', 'loginPsicologoPOST', 'logout-psicologo'];

        // if (in_array('admin', session()->get('usuario')->pluck('TIPO')->toArray())) {
        //     return $next($request);
        // }

         // 2️⃣ Redireciona psicólogos autenticados longe da página de login

        if (session()->has('psicologo') && in_array($routeName, ['loginPsicologoGET', 'loginPsicologoPOST'])) {
            return redirect()->route('psicologo.dashboard');
        }

        if ($routeName === 'loginPsicologoPOST' && $request->isMethod('post')) {
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            $response = $this->getApiData($credentials);

            if ($response['success']) {
                $validacao = $this->validarUsuarioPsicologo($credentials);

                if ($validacao->isEmpty()) {
                    return redirect()->back()->with('error', "Usuário Inativo");
                }

                // Salva o psicólogo na sessão e adiciona ID_CLINICA
                $psicologo = $validacao->first();
                $psicologo->ID_CLINICA = 1;
                session(['psicologo' => $psicologo]);

                return $next($request);
            }

            session()->flush();
            return redirect()->route('loginPsicologoGET')->with('error', "Credenciais Inválidas");
        }

        // 3️⃣ Para rotas protegidas, verifica se há sessão
        if (!in_array($routeName, $rotasLiberadas)) {
            if (!session()->has('psicologo')) {
                return redirect()->route('loginPsicologoGET');
            }
        }

        return $next($request);
    }

    public function getApiData(array $credentials)
    {
        $apiUrl = config('services.faesa.api_psicologos_url');
        $apiKey = config('services.faesa.api_psicologos_key');

        try {
            $response = Http::withHeaders([
                'Accept' => "application/json",
                'Authorization' => $apiKey
            ])->timeout(5)->post($apiUrl, $credentials);

            if($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Credenciais Inválidas',
                'status'  => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // VALIDA USUÁRIO PSICÓLOGO
    public function validarUsuarioPsicologo(array $credentials)
    {
        $username = $credentials['username'];
        $usuario = FaesaClinicaUsuarioGeral::where('USUARIO', $username)
            ->where('STATUS', '=', 'Ativo')
            ->get();
        return $usuario;
    }
}