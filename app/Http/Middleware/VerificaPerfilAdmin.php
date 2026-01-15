<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificaPerfilAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$tiposPermitidos)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login.form')
                ->with('erro', 'Faça login para acessar esta área.');
        }

        $tiposPermitidos = array_map('intval', $tiposPermitidos);
        if (empty($tiposPermitidos)) {
            $tiposPermitidos = [2, 3]; // 2 = Estabelecimento, 3 = Administrador
        }

        if (!in_array((int) $usuario->tipo_usuario_id, $tiposPermitidos, true)) {
            return redirect()->route('AcessoNegado')
                ->with('erro', 'Você não tem permissão para acessar essa área.');
        }

        return $next($request);
    }
}
