<?php

namespace App\Observers;

use App\Models\PedidoModel;
use App\Services\PedidoRealtimeService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PedidoObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private PedidoRealtimeService $pedidoRealtimeService)
    {
    }

    public function created(PedidoModel $pedido): void
    {
        $this->pedidoRealtimeService->broadcast((int) $pedido->id);
    }

    public function updated(PedidoModel $pedido): void
    {
        $this->pedidoRealtimeService->broadcast((int) $pedido->id);
    }

    public function deleted(PedidoModel $pedido): void
    {
        $this->pedidoRealtimeService->broadcast((int) $pedido->id);
    }
}
