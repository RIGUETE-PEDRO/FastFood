<?php

namespace App\Repositoryimpl;


use App\Models\EnderecoModel;
use App\Models\MesaModel;

class CarrinhoRepositoryimpl
{
    public  function pegarMesaSelecionada($statusPermitidos)
    {
        return MesaModel::query()
            ->whereIn('status', $statusPermitidos)
            ->orderBy('numero_da_mesa')
            ->get();
    }

    public function pegarEnderecosDoUsuario($usuarioId)
    {
        return EnderecoModel::with('cidade')
            ->where('usuario_id', $usuarioId)
            ->orderByDesc('created_at')
            ->get();
    }
}
