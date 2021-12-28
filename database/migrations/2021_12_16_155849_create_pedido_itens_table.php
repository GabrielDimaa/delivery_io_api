<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoItensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id('id_pedido_item');
            $table->foreignId('id_pedido')
                ->constrained('pedidos', 'id_pedido')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_produto')->nullable()
                ->constrained('produtos', 'id_produto')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('descricao', 50);
            $table->double('valor_unitario');
            $table->double('quantidade');
            $table->string('descricao_subcategoria', 50);
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
        Schema::dropIfExists('pedido_itens');
    }
}
