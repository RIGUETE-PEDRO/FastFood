<?php

namespace App\Services;

use App\Models\SecureKeyAuditoriaModel;
use Illuminate\Support\Facades\Auth;

class AuditoriaRegistroService
{
    public function registrar(string $acao, string $recurso, array $detalhes = [], ?int $usuarioId = null): SecureKeyAuditoriaModel
    {
        $usuarioId = $usuarioId ?? Auth::id();

        return SecureKeyAuditoriaModel::create([
            'usuario_id' => $usuarioId,
            'acao' => $acao,
            'recurso' => $recurso,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'detalhes' => $detalhes,
        ]);
    }
}
