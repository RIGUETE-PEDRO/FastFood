<?php

namespace App\Providers;

use App\Repository\AdminRepository;
use App\Repository\CarrinhoRepository;
use App\Repository\KeyClockRepository;
use App\Models\UsuarioModel;
use App\Repositoryimpl\AdminRepositoryimpl;
use App\Repositoryimpl\CarrinhoRepositoryimpl;
use App\Repositoryimpl\KeyClockRepositoryimpl;
use Illuminate\Support\Facades\Auth;
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
        $this->app->bind(KeyClockRepository::class, KeyClockRepositoryimpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
