<?php

namespace App\Repository;

use App\Models\UsuarioModel;

interface GenericBaseRepository
{
    public function pegarUsuarioEmail(array $data);

    public function existeFuncionario(UsuarioModel $usuario);

    public function findById(int $id);

    public function findByProdutos(array $categorias);

    public function pegarProdutos();

    public function findAll();

    public function findByCidade();

    public function findFuncionarios();

    public function deleteFuncionarioEUsuario(int $usuarioId);

    public function pegarItensCarrinho(int $usuarioId);

    public function findByProdutosIsUsuario(int $usuarioId, int $produtoId);
}
