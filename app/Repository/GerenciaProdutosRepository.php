<?php

namespace App\Repository;

use App\Models\ProdutoModel;

interface GerenciaProdutosRepository
{
    public function listarProdutosComCategoria();


    public function listarCategorias();


    public function criarProduto(array $dados);


    public function salvarProduto(ProdutoModel $produto): ProdutoModel;


    public function buscarProdutoPorId(int $id);


    public function atualizarProduto(int $id, array $data): ?ProdutoModel;


    public function deletarProduto(int $id): bool;


    public function atualizarCarrousel(int $id, bool $noCarrousel): ProdutoModel;
}
