<?php

namespace App\Providers;

use App\Repository\AdminRepository;
use App\Repository\CarrinhoRepository;
use App\Repository\GarcomRepository;
use App\Repository\KeyClockRepository;
use App\Roles\Role;
use App\Services\KeyClockService;
use App\Models\UsuarioModel;
use App\Repositoryimpl\AdminRepositoryimpl;
use App\Repositoryimpl\CarrinhoRepositoryimpl;
use App\Repositoryimpl\GarcomRepositoryimpl;
use App\Repositoryimpl\KeyClockRepositoryimpl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AdminRepository::class, AdminRepositoryimpl::class);
        $this->app->bind(CarrinhoRepository::class, CarrinhoRepositoryimpl::class);
        $this->app->bind(GarcomRepository::class, GarcomRepositoryimpl::class);
        $this->app->bind(KeyClockRepository::class, KeyClockRepositoryimpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $resolverRole = function (string $roleName): string {
            $roleName = trim($roleName);
            $roleConst = strtoupper($roleName);

            if (defined(Role::class . '::' . $roleConst)) {
                return (string) constant(Role::class . '::' . $roleConst);
            }

            return strtolower($roleName);
        };

        Blade::if('role', function (string $roleName) use ($resolverRole) {
            $usuario = Auth::user();

            if (!$usuario instanceof UsuarioModel) {
                return false;
            }

            $roleName = $resolverRole($roleName);

            return app(KeyClockService::class)->hasRole($usuario, $roleName);
        });

        Blade::if('anyrole', function (...$roleNames) use ($resolverRole) {
            $usuario = Auth::user();

            if (!$usuario instanceof UsuarioModel) {
                return false;
            }

            $roles = collect($roleNames)
                ->flatten()
                ->filter(fn ($r) => is_string($r) && trim($r) !== '')
                ->map(fn ($r) => $resolverRole($r));

            foreach ($roles as $roleName) {
                if (app(KeyClockService::class)->hasRole($usuario, $roleName)) {
                    return true;
                }
            }

            return false;
        });

        View::composer('*', function ($view) {
            $usuario = session('usuario_logado');

            if (!$usuario && Auth::check()) {
                $usuario = Auth::user();
            }

            if (is_numeric($usuario)) {
                $usuario = UsuarioModel::find($usuario);
            }

            $view->with('usuario', $usuario);
        });
    }
}
