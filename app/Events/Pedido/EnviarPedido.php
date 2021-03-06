<?php

namespace App\Events\Pedido;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnviarPedido implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Pedido $pedido;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('pedido');
    }

    public function broadcastAs(): string
    {
        return "Pedido";
    }

    public function broadcastWith()
    {
        return array('pedido' => $this->pedido);
    }
}
