<?php

namespace App\Services;

use App\Repository\IndexProdutoRepository;

class IndexService
{
    protected $produtoRepository;

    public function __construct(IndexProdutoRepository $produtoRepository)
    {
        $this->produtoRepository = $produtoRepository;
    }

    public function pegarProdutosIndex()
    {
        return $this->produtoRepository->pegarProdutosIndex();

    }

    public function pegarProdutosDestaque()
    {
        return $this->produtoRepository->pegarProdutosDestaque();
    }
}
