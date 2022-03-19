<?php

namespace App\Events\Pedido;

use App\Models\Pedido;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcompanharPedido implements ShouldBroadcast
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
        return new Channel('acompanhar_pedido.' . $this->pedido->id_pedido);
    }

    public function broadcastAs(): string
    {
        return "AcompanharPedido";
    }

    public function broadcastWith()
    {
        return array('pedido' => $this->pedido);
    }
}
