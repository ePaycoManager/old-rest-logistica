<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCiudades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	protected $table = 'ciudades';
    public function up()
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->string('codigo_dane')->unique();
            $table->string('nombre')->nullable();
	        $table->string('departamento')->nullable();
	        $table->string('codigo_tcc')->nullable();
	        $table->string('id_rest_payco')->nullable();
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
        Schema::dropIfExists('ciudades');
    }
}
