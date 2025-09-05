<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuario;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ARMAZENA ROTA QUE USUARIO QUER ACESSAR
        $routeName = $request->route()->getName();

        $rotasLiberadas = ['loginGET', 'logout'];

        if(!$routeName) {
            return $next($request);
        }

        // CASO A ROTA QUE O USUÁRIO TENTA ACESSAR SEJA ALGUMA DESSAS, ELE PERMITE SEGUIR ADIANTE
        if(in_array($routeName, [
            'loginGET',
            'logout',
        ])){
            return $next($request);
        }

        if(session()->has('usuario')) {
            return $next($request);
        }

        // AUTENTICAÇÃO VIA POST
        if( $routeName === 'loginPOST') {
            // ARMAZENA CREDENCIAIS
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            // DEPENDENDO DA ROTA, CHAMA UMA API DIFERENTE PARA AUTENTICAÇÃO
            $response = $this->apiAdm($credentials);

            if($response['success']) {
                $validacao = $this->validarADM($credentials);

                if (!$validacao) {
                    return redirect()->back()->with('error', 'Credenciais inválidas');
                }
                session(['usuario' => $validacao]);
                return $next($request);
            }

            session()->flush();
            return redirect()->route('loginGET')->with('error', "Credenciais Inválidas");

        } else {
            if (!in_array($routeName, $rotasLiberadas)) {
                if (!session()->has('usuario')) {
                    return redirect()->route('loginGET');
                }
            }
        }
    }

    // USUÁRIO ADM
    public function apiAdm(array $credentials): array
    {
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');

        try {
            $response = Http::withHeaders([
                'Accept'        => "application/json",
                'Authorization' => $apiKey
            ])
            ->timeout(5)
            ->post($apiUrl, $credentials);

            if($response->successful()) {
                return [
                    'success' => true
                ];
            } else {
                return [
                    'success' => false
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function validarADM(array $credentials)
    {
        $usuario = $credentials['username'];

        $usuarioADM = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $usuario)->where('SIT_USUARIO', 'Ativo')->get();

        return $usuarioADM->isNotEmpty() ? $usuarioADM : null;
    }
}
