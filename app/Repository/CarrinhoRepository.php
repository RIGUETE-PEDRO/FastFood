<?php

namespace App\Repository;

interface CarrinhoRepository
{
    public function pegarMesaSelecionada($statusPermitidos);

    public function pegarEnderecosDoUsuario($usuarioId);

    public function buscarProdutoPorId(int $produtoId);

    public function buscarItemCarrinhoPorUsuarioEProduto(int $usuarioId, int $produtoId);

    public function criarItemCarrinho(array $dados);

    public function buscarItemCarrinhoComProduto(int $id, int $usuarioId);

    public function buscarItemCarrinho(int $id, int $usuarioId);

    public function listarCidades();

    public function cidadeExiste(int $cidadeId): bool;

    public function buscarEnderecoPorIdEUsuario(int $enderecoId, int $usuarioId);

    public function criarEndereco(array $dados);

    public function quantidadeEnderecosUsuario(int $usuarioId): int;

    public function listarItensSelecionadosCarrinho(int $usuarioId);

    public function criarItemPedido(array $dados);

    public function pegarMesaPorId(int $id);

    public function calcularTotalAbertoMesa(int $mesaId): float;

    public function removerItensSelecionadosCarrinho(int $usuarioId): void;

    public function somarValorSelecionadoCarrinho(int $usuarioId): float;

    public function criarPedido(array $dados);
}
