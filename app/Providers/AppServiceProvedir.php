<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // 1) Tenta via Auth
            $name = optional(Auth::user())->name;

            // 2) Fallback: sessão 'usuario' + consulta no banco
            if (!$name) {
                $rows  = collect(Arr::wrap(session('usuario')));
                $first = $rows->first();

                $idUsuario = data_get($first, 'ID') ?? data_get($first, 'id_usuario');
                $loginRaw  = data_get($first, 'USUARIO') ?? data_get($first, 'login');
                $loginKey  = $loginRaw ? mb_strtolower(trim($loginRaw)) : null;

                if ($idUsuario || $loginKey) {
                    $cacheKey = 'display_name.' . ($idUsuario ?: "login:{$loginKey}");

                    // Se estiver em outra conexão, use: DB::connection('sqlsrv')->table(...)
                    $name = Cache::store('file')->remember($cacheKey, 600, function () use ($idUsuario, $loginKey) {
                        $q = DB::table('FAESA_CLINICA_USUARIO');

                        if ($idUsuario) {
                            $q->where('ID', $idUsuario);
                        } else {
                            // normaliza comparação por login
                            $q->whereRaw('LOWER(LTRIM(RTRIM(USUARIO))) = ?', [$loginKey]);
                        }

                        $val = $q->value('NOME');
                        return $val ? trim((string) $val) : null;
                    });
                }
            }

            $view->with('currentUser', [
                'name' => $name ?: 'Usuário',
            ]);
        });
    }
}