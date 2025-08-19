<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuario;
use App\Models\FaesaClinicaUsuarioGeral;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ARMAZENA ROTA QUE USUARIO QUER ACESSAR
        $routeName = $request->route()->getName();

        if(!$routeName) {
            return $next($request);
        }

        // CASO A ROTA QUE O USUÁRIO TENTA ACESSAR SEJA ALGUMA DESSAS, ELE PERMITE SEGUIR ADIANTE
        if(in_array($routeName, ['loginGET', 'loginPsicologoGET', 'logout', 'logout-psicologo'])) {
            return $next($request);
        }

        // AUTENTICAÇÃO VIA POST
        if($routeName === 'loginPOST' || $routeName === 'loginPsicologoPOST') {
            // ARMAZENA CREDENCIAIS
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            // DEPENDENDO DA ROTA, CHAMA UMA API DIFERENTE PARA AUTENTICAÇÃO
            $response = $routeName === 'loginPsicologoPOST'
            ? $this->getApiDataPsicologo($credentials)
            : $this->getApiDataAdmin($credentials);

            if($response['success']) {
                $validacao = $this->validarUsuario($credentials);

                if ($validacao->isEmpty()) {
                    return redirect()->back()->with('error', "Usuário Inativo");
                } else {
                    if($routeName === "loginPsicologoPOST") {
                        if($validacao->first()->TIPO === "Psicologo") {
                            session(['psicologo' => $validacao]);
                        } else {
                            return redirect()->back()->with('error','Usuário deve ser psicologo');
                        }
                    } else {
                        session(['usuario' => $validacao]);
                    }
                    return $next($request);
                }
                
            } else {
                session()->flush();
                return redirect()->back()->with('error', "Credenciais Inválidas");
            }
        }

         // Se não for loginPOST, nem rotas liberadas, pode fazer aqui a checagem da sessão por exemplo
        if (!session()->has('usuario') && !session()->has('psicologo')) {
            if (str_starts_with($routeName, 'psicologo')) {
                return redirect()->route('psicologoLoginGet'); // vai para /psicologo/login
            } else {
                return redirect()->route('loginGET'); // mantém o login normal
            }
        }

        return $next($request);
    }

    public function getApiDataAdmin(array $credentials)
    {
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');

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

    public function getApiDataPsicologo(array $credentials)
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

    public function validarUsuario(array $credentials)
    {
        $username = $credentials['username'];

        $usuario = FaesaClinicaUsuarioGeral::where('USUARIO', $username)
        ->where('STATUS', '=', 'Ativo')
        ->get();
        return $usuario;
    }
}