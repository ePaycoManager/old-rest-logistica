<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableConfigNegocio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_negocio', function (Blueprint $table) {
        	//'id','id_cliente','usuario_epayco','url_cotizar','url_guia','url_recogida','porcentaje_recogida','porcentaje_guia','procentaje_cotizar','activo'
            $table->increments('id');
	        $table->string('usuario_epayco')->nullable();
	        $table->string('url_cotizar')->nullable();
	        $table->string('url_guia')->nullable();
	        $table->string('url_recogida')->nullable();
	        $table->string('porcentaje_recogida')->nullable();
	        $table->string('porcentaje_guia')->nullable();
	        $table->string('porcentaje_cotizar')->nullable();
	        $table->boolean('activo')->nullable();
	        $table->string('operador')->nullable();
	        $table->string('id_cliente')->nullable();
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
        Schema::dropIfExists('config_negocio');
    }
}
