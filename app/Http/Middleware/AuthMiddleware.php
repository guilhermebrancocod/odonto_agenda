<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
        }
    }

    public function getApiData(array $credentials): array
    {
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');
    }
}
