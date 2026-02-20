<?php

namespace App\Http\Middleware;

use App\Mensagens\ErroMensagens;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsuarioAutenticado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!session()->has('usuario_logado')) {
            return redirect()->route('login.form')
                ->with('erro', ErroMensagens::PRECISA_ESTA_LOGADO);
        }

        return $next($request);
    }
}
