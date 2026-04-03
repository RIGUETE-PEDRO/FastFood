<?php

namespace App\Http\Middleware;

use App\Mensagens\ErroMensagens;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Keyclock
{
    public function handle(Request $request, Closure $next, ...$rolesPermitidas)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login.form')
                ->with('erro', ErroMensagens::FAZER_LOGIN_PARA_ACESSAR);
        }

        $query = DB::table('keyclock_tipo_usuario as ktu')
            ->join('roles as r', 'r.id', '=', 'ktu.role_id')
            ->where('ktu.tipo_usuario_id', (int) $usuario->tipo_usuario_id);

        // Se não informar role no middleware, basta ter qualquer role vinculada ao grupo
        if (!empty($rolesPermitidas)) {
            $query->whereIn('r.nome', $rolesPermitidas);
        }

        $temRole = $query->exists();

        if (!$temRole) {
            return redirect()->route('AcessoNegado')
                ->with('erro', ErroMensagens::CREDENCIAIS_INVALIDAS);
        }

        return $next($request);
    }
}
