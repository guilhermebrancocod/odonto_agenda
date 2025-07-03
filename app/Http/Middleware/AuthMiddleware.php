<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuario;
use Illuminate\Database\Eloquent\Collection;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ARMAZENA ROTA QUE USUARIO QUER ACESSAR
        $routeName = $request->route()->getName();

        // CASO A ROTA QUE O USUÁRIO TENTA ACESSAR SEJA ALGUMA DESSAS, ELE PERMITE
        if(in_array($routeName, ['loginGET', 'logout'])) {
            return $next($request);
        }

        // AUTENTICAÇÃO VIA POST
        if($routeName === 'loginPOST') {
            // ARMAZENA CREDENCIAIS
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            $response = $this->getApiData($credentials);

            if($response['success']) {

                $validacao = $this->validarUsuario($credentials);

                if(!$validacao) {
                    session()->flush();
                    return redirect()->back()->with('error', "Credenciais Inválidas");
                } else {
                    // CRIA SESSÃO COM DADOS DO USUARIO DA TABELA DE USUÁRIOS
                    session(['usuario' => $validacao]);
                    return $next($response);
                }
                
            } else {
                session()->flush();
                return redirect()->back()->with('error', "Credenciais Inválidas");
            }
        }
    }

    public function getApiData(array $credentials): array
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

    public function validarUsuario(array $credentials): Collection
    {
        $username = $credentials['username'];
        $usuario = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $username)->get();
        return $usuario;
     }
}
