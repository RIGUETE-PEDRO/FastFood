<?php

namespace App\Repository;
use Illuminate\Support\Collection;
use App\Models\ItemPedidoModel;

interface MesasRepository
{

    public function queryItensDaMesa(int $mesaId);

    public function pegarPedidoIdsDaMesa(int $mesaId);

    public function finalizarPedidosPorIds(Collection $pedidoIds);

    public function listarMesas();
    public function listarHistoricoFechamentos(int $porPagina = 12);

    public function listarFormasPagamento();

    public function pegarMesaPorId(int $id);

    public function pegarProdutosDisponiveis(array $excluirProdutoIds = []);

    public function pegarItensContaMesa(int $mesaId, bool $pago);

    public function listarItensAbertosMesa(int $mesaId);

    public function pegarItensAbertosSelecionados(int $mesaId, array $itemIds);

    public function pegarItensParaAbatimento(int $mesaId, array $itemIds);
    public function pegarItemAbertoDaMesa(int $mesaId, int $itemId);

    public function salvarItem(ItemPedidoModel $item);
    public function removerItem(ItemPedidoModel $item);
    public function atualizarTotaisPedido(int $pedidoId);

    public function criarItemPedido(array $dados);
    public function existemItensAbertosMesa(int $mesaId);

    public function desvincularMesaItensPagos(int $mesaId);

    public function pegarItensPagosParaFechamento(int $mesaId);

    public function registrarFechamentoMesa(array $dados);
    public function existeNumeroMesa(int $numeroMesa, ?int $ignorarId = null);

    public function criarMesa(array $dados);

    public function removerMesa(int $id);

    public function pegarProdutosMesa(int $id);
}
