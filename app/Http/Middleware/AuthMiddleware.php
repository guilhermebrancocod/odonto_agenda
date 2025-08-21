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
        if(in_array($routeName, [
            'loginGET',
            'psicologoLoginGet',
            'professorLoginGet',
            'logout',
            'psicologoLogout',
            'professorLogout',
            ]))
            {
            return $next($request);
        }

        // AUTENTICAÇÃO VIA POST
        if( ($routeName === 'loginPOST')
            ||
            ($routeName === 'psicologoLoginPost')
            ||
            ($routeName === 'professorLoginPost')
            ) {
            // ARMAZENA CREDENCIAIS
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            // DEPENDENDO DA ROTA, CHAMA UMA API DIFERENTE PARA AUTENTICAÇÃO
            $response = $routeName === 'psicologoLoginPost'
            ? $this->getApiDataPsicologo($credentials)
            : $this->getApiData($credentials);

            if($response['success']) {

                // VALIDA USUÁRIO NO BANCO DE DADOS
                $validacao = $this->validarUsuario($credentials);

                if ($validacao->is_null) {

                    return redirect()->back()->with('error', "Usuário Inativo");
                    
                } else {
                    
                    if($routeName === "psicologoLoginPost") {
                        
                        if($validacao->TIPO === "Psicologo") {

                            session(['psicologo' => $validacao]);
                            
                        } else {

                            return redirect()->back()->with('error','Usuário deve ser Psicólogo');
                        }

                    } else if ($routeName === "professorLoginPost") {

                        if($validacao->first()->TIPO === "Professor") {

                            session(['professor' => $validacao]);

                        } else {

                            return redirect()->back()->with('error', 'Usuário deve ser Professor');

                        }

                    } else if ($routeName === "recepcaoMenu") {

                        if($validacao->TIPO === "Recepcao") {

                            session(['recepcao' => $validacao]);

                        } else {

                            return redirect()->back()->with('error', 'Usuário deve ser Recepcionista');

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

        if ( 
                ( !session()->has('usuario') )
            && ( !session()->has('psicologo') )
            && ( !session()->has('professor') )
        ) {

            if (str_starts_with($routeName,
            'psicologo')) {

                return redirect()->route('psicologoLoginGet');

            } else if (str_starts_with($routeName,
            'professor')) {

                return redirect()->route('professorLoginGet');

            } else {

                return redirect()->route('loginGET');
            }
        }
        return $next($request);
    }

    public function getApiData(array $credentials): array
    {
        // CREDENCIAIS DA API
        $apiUrl = config('services.faesa.api_url');
        $apiKey = config('services.faesa.api_key');

        try {
            $response = Http::withHeaders([
                'Accept' => "application/json",
                'Authorization' => $apiKey
            ])
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

    public function getApiDataPsicologo(array $credentials): array
    {
        $apiUrl = config('services.faesa.api_psicologos_url');
        $apiKey = config('services.faesa.api_psicologos_key');
        
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

    // VALIDA USUÁRIO NO BANCO DE DADOS
    public function validarUsuario(array $credentials): FaesaClinicaUsuarioGeral
    {
        $username = $credentials['username'];
        $usuario = FaesaClinicaUsuarioGeral::where('USUARIO', $username)
        ->where('STATUS', '=', 'Ativo')
        ->first();
        return $usuario;
    }
}