<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Services\AuditoriaService;

class RegistrarEventosAutenticacao
{
    /**
     * Handle the Login event.
     */
    public function handleLogin(Login $event): void
    {
        AuditoriaService::registrarLogin($event->user->id, 'sessao');
    }

    /**
     * Handle the Logout event.
     */
    public function handleLogout(Logout $event): void
    {
        AuditoriaService::registrarLogout($event->user->id);
    }

    /**
     * Handle the Failed event.
     */
    public function handleFailed(Failed $event): void
    {
        AuditoriaService::registrar(
            'login_falhou',
            'autenticacao',
            [
                'credenciais' => $event->credentials['email'] ?? 'desconhecido',
                'guard' => $event->guard,
                'timestamp' => now()->toDateTimeString(),
            ]
        );
    }
}

