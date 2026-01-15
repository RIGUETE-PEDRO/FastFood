<?php


namespace App\Services;

use App\Models\Pedido;

class PedidoService
{
   public function pegarPedidosDoUsuario($usuarioId)
   {
   $pedidos = Pedido::with([
      'statusRelacionamento',
      'endereco.cidade',
      'itens.produto',
      'formaPagamento'
     ])
         ->where('usuario_id', $usuarioId)
         ->orderByDesc('created_at')
         ->get();

      return $pedidos;
   }
}
