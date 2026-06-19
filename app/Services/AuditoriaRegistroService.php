<?php

namespace App\Services;

use App\Models\SecureKeyAuditoriaModel;
use App\Repository\AuditoriaRepository;
use Illuminate\Support\Facades\Auth;

class AuditoriaRegistroService
{
    public function __construct(private AuditoriaRepository $repository)
    {
    }

    public function registrar(string $acao, string $recurso, array $detalhes = [], ?int $usuarioId = null): SecureKeyAuditoriaModel
    {
        $usuarioId = $usuarioId ?? Auth::id();

        return $this->repository->registrar([
            'usuario_id' => $usuarioId,
            'acao' => substr($acao, 0, 50),
            'recurso' => substr($recurso, 0, 100),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'detalhes' => $detalhes,
        ]);
    }
}
