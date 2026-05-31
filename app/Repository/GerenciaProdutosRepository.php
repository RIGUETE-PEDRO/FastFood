<?php

namespace App\Repository;

interface GerenciaProdutosRepository
{
    public function listarProdutosComCategoria();


    public function listarCategorias();


    public function criarProduto(array $dados);


    public function buscarProdutoPorId(int $id);
}
