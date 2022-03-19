<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_status', function (Blueprint $table) {
            $table->id('id_pedido_status');
            $table->foreignId('id_pedido')
                ->constrained('pedidos', 'id_pedido')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_status');
    }
}
