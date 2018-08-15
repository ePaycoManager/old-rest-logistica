<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTccCotizaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tcc_cotizaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_ciudad_origen')->nullable();
	        $table->string('id_ciudad_destino')->nullable();
	        $table->string('valor_mercancia')->nullable();
	        $table->string('boomerang')->nullable();
	        $table->string('cuenta')->nullable();
	        $table->dateTime('fecha_remesa')->nullable();
	        $table->string('numero_unidades')->nullable();
	        $table->string('peso_real')->nullable();
	        $table->string('peso_volumen')->nullable();
	        $table->string('alto')->nullable();
	        $table->string('largo')->nullable();
	        $table->string('ancho')->nullable();
	        $table->string('tipo_empaque')->nullable();
	        
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
        Schema::dropIfExists('tcc_cotizaciones');
    }
}
