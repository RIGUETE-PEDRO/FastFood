<?php

namespace App\Providers;

use App\Models\Usuario;
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
        //
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
                $usuario = Usuario::find($usuario);
            }

            $view->with('usuario', $usuario);
        });
    }
}
