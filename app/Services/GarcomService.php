<?php

namespace App\Services;

use App\Repository\GarcomRepository;

class GarcomService
{
    public function __construct(private GarcomRepository $repository)
    {
    }

    public function adicionarAoPedido($request){
        $produtoId = $request->input('produto_id');
        $mesaId = $request->input('mesa_id');
        $quantidade = $request->input('quantidade', 1);
        $usuarioLogado = $request->user();

        return $this->repository->adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado);
    }

}
