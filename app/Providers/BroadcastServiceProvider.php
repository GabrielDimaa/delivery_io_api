<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        Broadcast::channel('pedido', function ($pedido) {
            return !is_null($pedido->id_pedido);
        });

        Broadcast::channel('acompanhar_pedido.{id}', function ($pedido) {
            return !is_null($pedido->id_pedido);
        });
    }
}
