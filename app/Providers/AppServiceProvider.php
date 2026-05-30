<?php

namespace App\Providers;

use App\Repository\IndexProdutoRepository;
use App\Repository\PedidosFeitosRepository;
use App\Repositoryimpl\IndexProdutoRepositoryimpl;
use App\Repositoryimpl\PedidosFeitosRepositoryimpl;
use Carbon\Carbon;
use App\Repository\AdminRepository;
use App\Repository\AuditoriaRepository;
use App\Repository\CarrinhoRepository;
use App\Repository\GarcomRepository;
use App\Repository\SecureKeyRepository;
use App\Roles\Roles;
use App\Services\SecureKeyService;
use App\Models\UsuarioModel;
use App\Repositoryimpl\AdminRepositoryimpl;
use App\Repositoryimpl\AuditoriaRepositoryimpl;
use App\Repositoryimpl\CarrinhoRepositoryimpl;
use App\Repositoryimpl\GarcomRepositoryimpl;
use App\Repositoryimpl\SecureKeyRepositoryimpl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Repository\PedidoRepository;
use App\Repositoryimpl\PedidoRepositoryimpl;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AdminRepository::class, AdminRepositoryimpl::class);
        $this->app->bind(AuditoriaRepository::class, AuditoriaRepositoryimpl::class);
        $this->app->bind(CarrinhoRepository::class, CarrinhoRepositoryimpl::class);
        $this->app->bind(GarcomRepository::class, GarcomRepositoryimpl::class);
        $this->app->bind(SecureKeyRepository::class, SecureKeyRepositoryimpl::class);
        $this->app->bind(PedidosFeitosRepository::class,PedidosFeitosRepositoryimpl::class);
        $this->app->bind(IndexProdutoRepository::class, IndexProdutoRepositoryimpl::class);
        $this->app->bind(PedidoRepository::class, PedidoRepositoryimpl::class);
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('pt_BR');

        $resolverRole = function (string $roleName): string {
            $roleName = trim($roleName);
            $roleConst = strtoupper($roleName);

            if (defined(Roles::class . '::' . $roleConst)) {
                return (string) constant(Roles::class . '::' . $roleConst);
            }

            return strtolower($roleName);
        };

        Blade::if('role', function (string $roleName) use ($resolverRole) {
            $usuario = Auth::user();

            if (!$usuario instanceof UsuarioModel) {
                return false;
            }

            $roleName = $resolverRole($roleName);

            return app(SecureKeyService::class)->hasRole($usuario, $roleName);
        });

        Blade::if('anyrole', function (...$roleNames) use ($resolverRole) {
            $usuario = Auth::user();

            if (!$usuario instanceof UsuarioModel) {
                return false;
            }

            $roles = collect($roleNames)
                ->flatten()
                ->filter(fn($r) => is_string($r) && trim($r) !== '')
                ->map(fn($r) => $resolverRole($r));

            foreach ($roles as $roleName) {
                if (app(SecureKeyService::class)->hasRole($usuario, $roleName)) {
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
