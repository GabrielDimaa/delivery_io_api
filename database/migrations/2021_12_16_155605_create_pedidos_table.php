<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->string('codigo_pedido');
            $table->string('nome', 50);
            $table->string('telefone', 11);
            $table->string('rua', 80)->nullable();
            $table->string('bairro', 50)->nullable();
            $table->string('numero')->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('cidade', 50)->nullable();
            $table->double('valor_total');
            $table->double('troco')->nullable();
            $table->double('valor_pago')->nullable();
            $table->integer('forma_pagamento')->nullable();
            $table->integer('tipo_entrega');
            $table->integer('status');
            $table->string('observacao', 250)->nullable();
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamp('cancelado_at')->nullable();
            $table->string('tempo_estimado', 50);
            $table->foreignId('id_taxa_entrega')->nullable()
                ->constrained('taxas_entrega', 'id_taxa_entrega')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('descricao_taxa_entrega',50)->nullable();
            $table->double('valor_taxa_entrega')->nullable();
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
        Schema::dropIfExists('pedidos');
    }
}
