<?php

namespace App\Services;

use App\Repository\GarcomRepository;

class GarcomService
{
    public function __construct(private GarcomRepository $repository)
    {
        $this->repository = $repository;
    }

    public function adicionarAoPedido($request){
        $produtoId = $request->input('produto_id');
        $mesaId = $request->input('mesa_id');
        $quantidade = $request->input('quantidade', 1);
        $usuarioLogado = $request->user();

        if ($request->has('itens')) {
            return $this->repository->adicionarProdutosAoPedido(
                (array) $request->input('itens', []),
                (int) $mesaId,
                $usuarioLogado
            );
        }

        return $this->repository->adicionarProdutoAoPedido($produtoId, $mesaId, $quantidade, $usuarioLogado);
    }

}
