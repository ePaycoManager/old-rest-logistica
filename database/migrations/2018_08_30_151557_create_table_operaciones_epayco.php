<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableOperacionesEpayco extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	protected $table = 'operaciones_epayco';
    public function up()
    {
        Schema::create('operaciones_epayco', function (Blueprint $table) {
            $table->increments('id');
	        $table->string('operacion')->nullable();
	        $table->string('operador')->nullable();
	        $table->string('valor_operador')->nullable();
	        $table->string('valor_payco')->nullable();
	        $table->integer('id_operacion_operador')->nullable();
	        $table->integer('id_cliente')->nullable();
	        $table->float('iva')->nullable();
	        $table->float('flete_operador')->nullable();
	        $table->float('manejo_operador')->nullable();
	        $table->float('flete_epayco')->nullable();
	        $table->float('manejo_epayco')->nullable();
	        $table->string('factura_cliente')->nullable();
	        $table->boolean('id_recogida')->nullable();
	        $table->boolean('recogida_automatica')->nullable();
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
        Schema::dropIfExists('operaciones_epayco');
    }
}
