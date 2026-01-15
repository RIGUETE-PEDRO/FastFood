<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\GenericBase;
use App\Services\PedidoService;

class PedidoController extends Controller
{
    public function verpedido()
    {

        $genericBase = new GenericBase();
        $usuarioLogado = $genericBase->pegarUsuarioLogado();

        $PedidoService = new PedidoService();
        $pedido = $PedidoService->pegarPedidosDoUsuario($usuarioLogado);

        return view('Pedido', [
            'usuario' => $usuarioLogado,
            'pedidos' => $pedido,
        ]);
    }


  




}
