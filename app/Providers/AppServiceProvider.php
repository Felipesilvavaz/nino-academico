<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // RF03 / RN06: controle de acesso por perfil.
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('cadastros', fn (User $user) => in_array($user->perfil, ['admin', 'tecnico'], true));
        Gate::define('operar', fn (User $user) => in_array($user->perfil, ['admin', 'tecnico'], true));
    }
}
