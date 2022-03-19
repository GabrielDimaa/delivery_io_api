<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInsertPedidoStatusFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fnc_insert_pedido_status()
            RETURNS TRIGGER AS
            \$BODY$
            BEGIN
	            INSERT INTO pedido_status(id_pedido, status, created_at, updated_at)
	            VALUES (NEW.id_pedido, NEW.status, NEW.updated_at, NEW.updated_at);

	            RETURN NEW;
            END;
            \$BODY$
            LANGUAGE plpgsql;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insert_pedido_status_function');
    }
}
