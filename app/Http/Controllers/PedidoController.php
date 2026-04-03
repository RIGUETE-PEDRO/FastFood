<?php

namespace App\Http\Controllers;


use App\Services\GenericBase;
use App\Services\PedidoService;

class PedidoController extends Controller
{
    protected GenericBase $genericBase;
    protected PedidoService $PedidoService;

    public function __construct(GenericBase $genericBase, PedidoService $PedidoService)
    {
        $this->genericBase = $genericBase;
        $this->PedidoService = $PedidoService;
    }

    public function verpedido()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();


        $pedido = $this->PedidoService->pegarPedidosDoUsuario($usuarioLogado);

        return view('Pedido', [
            'usuario' => $usuarioLogado,
            'pedidos' => $pedido,
        ]);
    }
}
