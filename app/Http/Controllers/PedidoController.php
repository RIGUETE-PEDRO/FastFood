<?php

namespace App\Http\Controllers;


use App\Services\GenericBase;
use App\Services\PedidoService;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    protected GenericBase $genericBase;
    protected PedidoService $PedidoService;

    public function __construct(GenericBase $genericBase, PedidoService $PedidoService)
    {
        $this->genericBase = $genericBase;
        $this->PedidoService = $PedidoService;
    }

    public function pedidosFiltro(Request $request)
    {
        $usuarioLogado =  $this->genericBase->hasLogado();


        $pedido = $this->PedidoService->filtrarPedidosDataNome($request);

        return view('Admin.Pedidos', [
            'usuario' => $usuarioLogado,
            'pedidos' => $pedido,
        ]);
    }

    public function verpedido()
    {
        $usuarioLogado =  $this->genericBase->hasLogado();


        $pedido = $this->PedidoService->pegarPedidosDoUsuario((int) ($usuarioLogado?->id ?? 0));

        return view('Pedido', [
            'usuario' => $usuarioLogado,
            'pedidos' => $pedido,
        ]);
    }
}
