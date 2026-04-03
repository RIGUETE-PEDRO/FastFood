<?php

namespace App\Repositoryimpl;

use App\Models\Endereco;
use App\Models\Mesa;

class CarrinhoRepositoryimpl
{
    public  function pegarMesaSelecionada($statusPermitidos)
    {
        return Mesa::query()
            ->whereIn('status', $statusPermitidos)
            ->orderBy('numero_da_mesa')
            ->get();
    }

    public function pegarEnderecosDoUsuario($usuarioId)
    {
        return Endereco::with('cidade')
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }
}
