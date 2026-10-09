<?php

namespace App\Services;

use App\Models\ProdutoModel;
use App\Repository\GarcomRepository;

class GarcomService
{
    public function __construct(
        private GarcomRepository $repository,
        private PersonalizacaoProdutoService $personalizacaoProdutoService,
    )
    {
        $this->repository = $repository;
    }

    public function adicionarAoPedido($request){
        $produtoId = $request->input('produto_id');
        $mesaId = $request->input('mesa_id');
        $quantidade = $request->input('quantidade', 1);
        $usuarioLogado = $request->user();

        if ($request->has('itens')) {
            $itens = $this->cotarItens((array) $request->input('itens', []));

            return $this->repository->adicionarProdutosAoPedido(
                $itens,
                (int) $mesaId,
                $usuarioLogado
            );
        }

        $itens = $this->cotarItens([[
            'produto_id' => $produtoId,
            'quantidade' => $quantidade,
            'observacao' => $request->input('observacao'),
            'personalizacao' => $request->input('personalizacao'),
        ]]);

        return $this->repository->adicionarProdutoAoPedido(
            $itens[0]['produto_id'],
            $mesaId,
            $itens[0]['quantidade'],
            $usuarioLogado,
            $itens[0]['observacao'],
            $itens[0]['personalizacao'],
            $itens[0]['preco_unitario'],
        );
    }

    private function cotarItens(array $itens): array
    {
        return array_map(function (array $item): array {
            $produtoId = (int) ($item['produto_id'] ?? 0);
            $produto = ProdutoModel::with(['categoria', 'bordas'])->whereKey($produtoId)->first();
            if (!$produto) {
                throw new \InvalidArgumentException('Um dos produtos não foi encontrado.');
            }

            $cotacao = $this->personalizacaoProdutoService->cotar($produto, $item['personalizacao'] ?? null);

            return [
                'produto_id' => $produtoId,
                'quantidade' => (int) ($item['quantidade'] ?? 1),
                'observacao' => trim((string) ($item['observacao'] ?? '')) ?: null,
                'personalizacao' => $cotacao['personalizacao'],
                'preco_unitario' => $cotacao['preco_unitario'],
            ];
        }, $itens);
    }

}
