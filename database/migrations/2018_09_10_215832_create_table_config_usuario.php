<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableConfigUsuario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	protected $table = "config_usuario";
    public function up()
    {
        Schema::create('config_usuario', function (Blueprint $table) {
        	//recogida_automatica','dias','segmento'
            $table->increments('id');
	        $table->string('id_usuario_rest_pagos');
	        $table->string('direccion')->nullable();
	        $table->string('telefono')->nullable();
	        $table->string('ciudad')->nullable();
	        $table->string('nombre')->nullable();
	        $table->string('documento')->nullable();
	        $table->string('tipo_documento')->nullable();
	        $table->string('tipo_persona')->nullable();
	        $table->boolean('recogida_automatica')->nullable();
	        $table->string('dias')->nullable();
	        $table->string('segmento')->nullable();
	        $table->string('tag')->nullable();
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
        Schema::dropIfExists('config_usuario');
    }
}
