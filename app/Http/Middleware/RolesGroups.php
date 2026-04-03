<?php

namespace App\Http\Middleware;

class RolesGroups
{
    public function handle($request, \Closure $next)
    {
        // Lógica para verificar os grupos de papéis do usuário
        // Exemplo: Verificar se o usuário pertence a um grupo específico

        return $next($request);
    }
}
