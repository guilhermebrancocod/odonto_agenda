<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuario;

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

                if ($validacao->isEmpty()) {
                    return redirect()->back()->with('error', "Usuário Inativo");
                } else {
                    session(['usuario' => $validacao]);
                    return $next($request);
                }
                
            } else {
                session()->flush();
                return redirect()->back()->with('error', "Credenciais Inválidas");
            }
        }

         // Se não for loginPOST, nem rotas liberadas, pode fazer aqui a checagem da sessão por exemplo
        if (!session()->has('usuario')) {
            return redirect()->route('loginGET');
        }

        return $next($request);
    }

    public function getApiData(array $credentials)
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

    public function validarUsuario(array $credentials)
    {
        $username = $credentials['username'];
        $usuario = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $username)
        ->where('SIT_USUARIO', '=', 'Ativo')
        ->get();
        return $usuario;
    }
}