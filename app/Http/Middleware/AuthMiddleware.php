<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\FaesaClinicaUsuario; // Usado apenas para o Administrador

class AuthMiddleware
{
    /**
     * Ponto de entrada do middleware.
     */
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();

        // Rotas que não precisam de autenticação
        $rotasLiberadas = ['loginGET', 'loginPOST', 'logout'];

        // Se a rota estiver na lista de liberadas, processa e continua
        if (in_array($routeName, $rotasLiberadas, true)) {
            if ($routeName === 'loginPOST') {
                return $this->processarLogin($request, $next);
            }
            return $next($request);
        }

        // Se o usuário JÁ ESTIVER LOGADO (com a sessão 'usuario'), permite o acesso
        if (session()->has('usuario')) {
            return $next($request);
        }

        // Se não está logado e a rota não é pública, redireciona para o login
        return redirect()->route('loginGET')->with('error', 'Você precisa fazer login para acessar esta página.');
    }

    /**
     * Processa a tentativa de login do formulário.
     */
    private function processarLogin(Request $request, Closure $next)
    {
        $credentials = [
            'username' => $request->input('login'),
            'password' => $request->input('senha'),
        ];
        $perfil = $request->input('perfil');

        if (!$perfil) {
            return redirect()->back()->with('error', 'Selecione um perfil para continuar.');
        }

        // 1. Autenticação via API (agora ciente do perfil)
        $response = $this->getApiData($credentials, $perfil);

        if (!$response['success']) {
            session()->flush();
            return redirect()->back()->with('error', $response['message'] ?? 'Credenciais Inválidas');
        }

        // 2. Validação no banco de dados local conforme o perfil
        $usuarioValidado = null;
        switch ($perfil) {
            case 'aluno':
                $usuarioValidado = $this->validarAluno($credentials);
                break;
            case 'professor':
                $usuarioValidado = $this->validarProfessor($credentials);
                break;
            case 'admin':
                $usuarioValidado = $this->validarAdmin($credentials);
                break;
            default:
                return redirect()->back()->with('error', 'Perfil inválido selecionado.');
        }

        // 3. Verifica se a validação local encontrou um usuário válido
        if (is_null($usuarioValidado)) {
            $errorMessage = $perfil === 'aluno' ? 'Aluno sem permissão de acesso' : 'Usuário sem permissão de acesso para este perfil';
            return redirect()->back()->with('error', $errorMessage);
        }

        // 4. SUCESSO: Armazena dados na sessão de forma unificada
        session([
            'usuario' => $usuarioValidado, // Chave única para todos os perfis
            'perfil'  => $perfil
        ]);

        return $next($request);
    }

    /**
     * Chama a API de autenticação correta com base no perfil.
     */
    private function getApiData(array $credentials, string $perfil): array
    {
        $apiUrl = '';
        $apiKey = '';

        // Administrador usa uma API, Aluno/Professor usam outra
        if ($perfil === 'admin') {
            // Use as credenciais da API para administradores (do seu primeiro arquivo)
            $apiUrl = config('services.faesa.api_url');
            $apiKey = config('services.faesa.api_key');
        } else {
            // Aluno e Professor usam a mesma API (dos seus arquivos de aluno/professor)
            $apiUrl = config('services.faesa.api_psicologos_url');
            $apiKey = config('services.faesa.api_psicologos_key');
        }
        
        // Verifica se as chaves de configuração existem
        if (!$apiUrl || !$apiKey) {
            return ['success' => false, 'message' => 'API de autenticação não configurada para este perfil.'];
        }

        try {
            $response = Http::withHeaders([
                'Accept' => "application/json",
                'Authorization' => $apiKey
            ])->timeout(5)->post($apiUrl, $credentials);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => 'Credenciais Inválidas'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro ao conectar com o serviço de autenticação.'];
        }
    }

    /**
     * VALIDA ALUNO (lógica copiada do seu AuthPsicologoMiddleware)
     */
    private function validarAluno(array $credentials)
    {
        $usuario = $credentials['username'];
        $retorno[0] = $usuario;

        $cpf = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p')
            ->where('p.WINUSUARIO', 'FAESA\\' . $usuario)
            ->value('CPF');

        if (!$cpf) return null;

        $aluno = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_ALUNO as a')
            ->join('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA as p', 'p.NOME_COMPL', '=', 'a.NOME_COMPL')
            ->where('p.CPF', $cpf)
            ->where('a.SIT_ALUNO', 'Ativo')
            ->select('a.ALUNO', 'p.NOME_COMPL', 'p.E_MAIL_COM', 'p.CELULAR')
            ->first();

        if ($aluno) {
            $disciplinas = ['D009373', 'D009376', 'D009381', 'D009385', 'D009393', 'D009403', 'D009402', 'D009406', 'D009404'];
            $matricula = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_MATRICULA as m')
                ->where('m.ALUNO', $aluno->ALUNO)
                ->whereIn('m.DISCIPLINA', $disciplinas)
                ->get();

            if (!$matricula->isEmpty()) {
                $retorno[] = $aluno->ALUNO;
                $retorno[] = $aluno->NOME_COMPL;
                $retorno[] = $aluno->E_MAIL_COM;
                $retorno[] = $aluno->CELULAR;
                $retorno[] = $matricula->map(fn($item) => ['DISCIPLINA' => $item->DISCIPLINA, 'TURMA' => $item->TURMA])->toArray();
                return $retorno;
            }
        }
        return null;
    }

    /**
     * VALIDA PROFESSOR (lógica copiada do seu AuthProfessorMiddleware)
     */
    private function validarProfessor(array $credentials)
    {
        $usuario = $credentials['username'];
        $retorno[0] = $usuario;

        $anoSemestreLetivo = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_OPCOES')->where('CHAVE', 4)->select('ANO_LETIVO', 'SEM_LETIVO')->first();
        
        if(!$anoSemestreLetivo) return null;

        $cpf = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_PESSOA')->where('WINUSUARIO', 'FAESA\\' . $usuario)->value('CPF');
        
        if (!$cpf) return null;

        $docente = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_DOCENTE')->where('CPF', $cpf)->first();

        if ($docente) {
            $disciplinas = ['D009373', 'D009376', 'D009381', 'D009385', 'D009393', 'D009403', 'D009402', 'D009406', 'D009404'];
            $vinculos = DB::table('LYCEUM_BKP_PRODUCAO.dbo.LY_TURMA as t')
                ->where('t.NUM_FUNC', $docente->NUM_FUNC)
                ->whereIn('t.DISCIPLINA', $disciplinas)
                ->where('t.ANO', $anoSemestreLetivo->ANO_LETIVO)
                ->where('t.SEMESTRE', $anoSemestreLetivo->SEM_LETIVO)
                ->get();

            if (!$vinculos->isEmpty()) {
                $retorno[] = $docente->NUM_FUNC;
                $retorno[] = $docente->NOME_COMPL;
                $retorno[] = $docente->CPF;
                $retorno[] = $vinculos->map(fn($item) => ['DISCIPLINA' => $item->DISCIPLINA, 'TURMA' => $item->TURMA])->toArray();
                return $retorno;
            }
        }
        return null;
    }
    
    /**
     * VALIDA ADMINISTRADOR (lógica do seu primeiro AuthMiddleware)
     */
    private function validarAdmin(array $credentials)
    {
        $admin = FaesaClinicaUsuario::where('ID_USUARIO_CLINICA', $credentials['username'])
            ->where('SIT_USUARIO', 'Ativo')
            ->where('ID_CLINICA', 1)
            ->first();

        // Retorna um array para manter o padrão dos outros perfis, ou null se não encontrar
        return $admin ? $admin->toArray() : null;
    }
}