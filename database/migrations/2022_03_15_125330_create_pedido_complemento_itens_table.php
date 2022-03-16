<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoComplementoItensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_complemento_itens', function (Blueprint $table) {
            $table->id('id_pedido_complemento_item');
            $table->foreignId('id_pedido_item')
                ->constrained('pedido_itens', 'id_pedido_item')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_pedido')
                ->constrained('pedidos', 'id_pedido')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_complemento')->nullable()
                ->constrained('complementos', 'id_complemento')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('descricao', 50);
            $table->double('valor_unitario');
            $table->double('valor_total');
            $table->double('quantidade');
            $table->string('descricao_categoria', 50);
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
        Schema::dropIfExists('pedido_complemento_itens');
    }
}
