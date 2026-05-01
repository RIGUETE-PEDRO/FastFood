<?php

namespace App\Services;

use App\Models\KeyClockAuditoriaModel;
use Illuminate\Support\Facades\Auth;

class AuditoriaRegistroService
{
    public function registrar(string $acao, string $recurso, array $detalhes = [], ?int $usuarioId = null): KeyClockAuditoriaModel
    {
        $usuarioId = $usuarioId ?? Auth::id();

        return KeyClockAuditoriaModel::create([
            'usuario_id' => $usuarioId,
            'acao' => $acao,
            'recurso' => $recurso,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'detalhes' => $detalhes,
        ]);
    }
}
