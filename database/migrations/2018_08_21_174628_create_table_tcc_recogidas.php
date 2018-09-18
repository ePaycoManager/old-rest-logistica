<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTccRecogidas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	protected $table = 'tcc_recogidas';
    public function up()
    {
        Schema::create('tcc_recogidas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_cliente')->nullable();
	        $table->string('cuenta_cliente')->nullable();
	        $table->string('telefono_cliente')->nullable();
	        $table->string('ciudad_cliente')->nullable();
	        $table->string('nombre_cliente')->nullable();
	        $table->string('persona_solicita')->nullable();
	        $table->string('tipo_documento_solicita')->nullable();
	        $table->string('identificacion_solicita')->nullable();
	        $table->string('telefono_remitente')->nullable();
	        $table->string('ciudad_remitente')->nullable();
	        $table->string('nombre_cliente_remitente')->nullable();
	        $table->string('persona_contacto_remitente')->nullable();
	        $table->string('direccion_remitente')->nullable();
	        $table->string('direccion_info_adicional_remitente')->nullable();
	        $table->string('tipo_documento_remitente')->nullable();
	        $table->string('identificacion_remitente')->nullable();
	        $table->dateTime('fecha_recogida')->nullable();
	        $table->string('hora_inicial_recogida')->nullable();
	        $table->string('hora_final_recogida')->nullable();
	        $table->string('unidades')->nullable();
	        $table->string('peso')->nullable();
	        $table->string('volumen')->nullable();
	        $table->string('valor_mercancia')->nullable();
	        $table->string('observaciones')->nullable();
	        $table->string('cdpago')->nullable();
	        $table->string('id_tcc')->nullable();
	        $table->string('id_user')->nullable();
	        $table->string('id_remesa')->nullable();
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
        Schema::dropIfExists('tcc_recogidas');
    }
}
