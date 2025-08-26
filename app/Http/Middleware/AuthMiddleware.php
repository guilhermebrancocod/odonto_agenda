<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FaesaClinicaUsuario;
use App\Models\FaesaClinicaUsuarioGeral;
use Illuminate\Support\Facades\DB;
use stdClass;

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
            'logout',
            'psicologoLoginGet',
            'professorLoginGet',
            'psicologoLogout',
            'professorLogout',
        ])){
            return $next($request);
        }

        if(session()->has('usuario')) {
            if($routeName === 'psicologoLoginPost') {
                session()->forget('usuario');
            } else {
                return $next($request);
            }
        }

        // AUTENTICAÇÃO VIA POST
        if( ($routeName === 'loginPOST') || ($routeName === 'psicologoLoginPost') || ($routeName === 'professorLoginPost')) {

            // ARMAZENA CREDENCIAIS
            $credentials = [
                'username' => $request->input('login'),
                'password' => $request->input('senha'),
            ];

            // DEPENDENDO DA ROTA, CHAMA UMA API DIFERENTE PARA AUTENTICAÇÃO
            $response = ($routeName === 'psicologoLoginPost' || $routeName === 'professorLoginPost')
            ? $this->apiData($credentials)
            : $this->apiAdm($credentials);

            if($response['success']) {
                if($routeName === 'psicologoLoginPost') {
                    $validacao = $this->validarPsicologo($credentials);
                    if (!$validacao) {
                        return redirect()->back()->with('error', 'Credenciais inválidas');
                    }
                    session(['psicologo' => $validacao]);
                    return $next($request);                  
                } else if ($routeName === 'professorLoginPost') {
                    $validacao = $this->validarProfessor($credentials);
                    if (!$validacao) {
                        return redirect()->back()->with('error', 'Credenciais inválidas');
                    }   
                    session(['professor' => $validacao]);
                    return $next($request);
                } else {
                    $validacao = $this->validarADM($credentials);
                    if (!$validacao) {
                        return redirect()->back()->with('error', 'Credenciais inválidas');
                    }
                    session(['usuario' => $validacao]);
                    return $next($request);
                }                
            } else {
                return redirect()->back()->withErrors(['login' => 'Credenciais Inválidas']);
            }
        } else {
            return redirect()->back()->withErrors(['login' => 'Credenciais Inválidas']);
        }
    }

    // ALUNO E PROFESSOR
    public function apiData(array $credentials): array
    {
        // CREDENCIAIS DA API
        $apiUrl = config('services.faesa.api_psicologos_url');
        $apiKey = config('services.faesa.api_psicologos_key');

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

    public function validarPsicologo(array $credentials)
    {
        $usuario = $credentials['username'];
        $retorno[0] = $usuario;

        // BUSCA CPF DO USUÁRIO COM CÓD. DE USUÁRIO
        $cpf = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p')
        ->where('p.WINUSUARIO', 'FAESA\\' . $usuario)
        ->value('CPF');

        // VERIFICA SE É ALUNO
        $aluno = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as a')
        ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p', 'p.NOME_COMPL', '=', 'a.NOME_COMPL')
        ->where('p.CPF', $cpf)
        ->where('a.SIT_ALUNO', 'Ativo')
        ->select('a.ALUNO', 'p.NOME_COMPL', 'p.E_MAIL_COM', 'p.CELULAR')
        ->first();

        if($aluno) {
            $disciplinas = ['D009373', 'D009376', 'D009381', 'D009385', 'D009393', 'D009403', 'D009402', 'D009406', 'D009404'];
            // $disciplinas = $this->buscarDisciplinasClinica();
            $matricula = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA as m')
            ->where('m.ALUNO', $aluno->ALUNO)
            ->whereIn('m.DISCIPLINA', $disciplinas)
            ->get();

            if(!$matricula->isEmpty()) {

                $retorno[] = $aluno->ALUNO;
                $retorno[] = $aluno->NOME_COMPL;
                $retorno[] = $aluno->E_MAIL_COM;
                $retorno[] = $aluno->CELULAR;

                $retorno[] = ($matricula->map(function($item) {
                    return [
                        'DISCIPLINA' => $item->DISCIPLINA,
                        'TURMA' => $item->TURMA,
                    ];
                }))->toArray();

                return $retorno;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function validarProfessor(array $credentials)
    {
        $usuario = $credentials['username'];
        $retorno[0] = $usuario;

        $anoSemestreLetivo = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_OPCOES as o')
        ->where('o.CHAVE', 4)
        ->select('o.ANO_LETIVO', 'o.SEM_LETIVO')
        ->first();

        // BUSCA CPF DO USUÁRIO COM CÓD. DE USUÁRIO
        $cpf = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p')
        ->where('p.WINUSUARIO', 'FAESA\\' . $usuario)
        ->value('CPF');

        $docente = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_DOCENTE as d')
        ->where('d.CPF', $cpf)
        ->first();

        if($docente) {
            // $disciplinas = $this->buscarDisciplinasClinica();
            $disciplinas = ['D009373', 'D009376', 'D009381', 'D009385', 'D009393', 'D009403', 'D009402', 'D009406', 'D009404'];
            $vinculos = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_TURMA as t')
            ->where('t.NUM_FUNC', $docente->NUM_FUNC)
            ->whereIn('t.DISCIPLINA', $disciplinas)
            ->where('t.ANO', $anoSemestreLetivo->ANO_LETIVO)
            ->where('t.SEMESTRE', $anoSemestreLetivo->SEM_LETIVO)
            ->get();


            if(!$vinculos->isEmpty()) {
                $retorno[] = $docente->NUM_FUNC;
                $retorno[] = $docente->NOME_COMPL;
                $retorno[] = $docente->CPF;

                $retorno[] = ($vinculos->map(function($item) {
                    return [
                        'DISCIPLINA' => $item->DISCIPLINA,
                        'TURMA' => $item->TURMA,
                    ];
                }))->toArray();

                return $retorno;
            } else {
                return null;
            }

        } else {
            return null;
        }
    }

    public function validarADM(array $credentials)
    {
        $usuario = $credentials['username'];

        $usuarioADM = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $usuario)->where('SIT_USUARIO', 'Ativo')->get();

        return $usuarioADM->isNotEmpty() ? $usuarioADM : null;
    }

    public function buscarDisciplinasClinica()
    {
        $disciplinas = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_TURMA as t')
        ->where('t.FL_FIELD_17', 'CLINICA')->get();
        return $disciplinas->toArray();
    }
}
