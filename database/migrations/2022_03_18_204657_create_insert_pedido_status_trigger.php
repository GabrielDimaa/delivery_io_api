<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInsertPedidoStatusTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER tr_insert_pedido_status
            AFTER INSERT OR UPDATE OF status ON pedidos
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_insert_pedido_status();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insert_pedido_status_trigger');
    }
}
