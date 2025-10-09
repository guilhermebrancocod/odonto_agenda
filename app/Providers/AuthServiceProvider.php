<?php

namespace App\Providers;

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('user.manage.view',   fn($u) => in_array($u->tipo, ['Admin', 'Coordenador']));
        Gate::define('user.manage.create', fn($u) => $u->tipo === 'Admin');
        Gate::define('user.manage.update', fn($u) => in_array($u->tipo, ['Admin', 'Coordenador']));
        Gate::define('user.manage.delete', fn($u) => $u->tipo === 'Admin');
    }
}
