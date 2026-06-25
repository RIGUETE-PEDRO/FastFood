<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PedidosAtualizados implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(private array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pedidos.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pedidos.atualizados';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
