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
        	
            $table->increments('id');
	        $table->string('operador')->nullable();
	        $table->string('operacion')->nullable();
	        $table->string('regla')->nullable();
	        $table->string('tipo')->nullable();
	        $table->string('valor')->nullable();
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
