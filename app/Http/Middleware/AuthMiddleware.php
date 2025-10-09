<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Aceita usuário logado via guard OU via sessão 'usuario'
        $authUser    = $request->user();          // guard padrão do Laravel (opcional)
        $sessionUser = session('usuario');        // seu objeto salvo na sessão

        // Não autenticado
        if (!$authUser && !$sessionUser) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('loginGET'));
        }

        // Usuário de referência (prioriza guard, senão sessão)
        $user = $authUser ?? $sessionUser;

        // Bloqueio por status (opcional)
        $status = (string) ($user->status ?? $user->STATUS ?? '');
        if ($status !== '' && mb_strtolower($status) !== 'ativo') {
            abort(Response::HTTP_FORBIDDEN, 'Seu usuário está inativo.');
        }

        // Se nenhuma role foi passada, apenas segue
        if (empty($roles)) {
            return $next($request);
        }

        // Normaliza papéis permitidos
        $allowed = array_map(
            fn ($r) => mb_strtolower(trim((string) $r)),
            $roles
        );

        // Lê o tipo do usuário (cobre 'tipo' e 'TIPO')
        $tipo = (string) ($user->tipo ?? $user->TIPO ?? '');
        $tipoNorm = mb_strtolower(trim($tipo));

        if (!in_array($tipoNorm, $allowed, true)) {
            abort(Response::HTTP_FORBIDDEN, 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
