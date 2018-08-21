<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTccRemesas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    protected $table = 'tcc_remesas';
    public function up()
    {
        Schema::create('tcc_remesas', function (Blueprint $table) {
            $table->increments('id');
	        $table->dateTime('fecha_lote')->nullable();
	        $table->string('numero_remesa')->nullable();
	        $table->string('unidad_negocio')->nullable();
	        $table->dateTime('fecha_despacho')->nullable();
	        $table->string('cuenta_remitente')->nullable();
	        $table->string('primer_nombre_remitente')->nullable();
	        $table->string('segundo_nombre_remitente')->nullable();
	        $table->string('primer_apellido_remitente')->nullable();
	        $table->string('segundo_apellido_remitente')->nullable();
	        $table->string('razon_social_remitente')->nullable();
	        $table->string('naturaleza_remitente')->nullable();
	        $table->string('tipo_identificacion_remitente')->nullable();
	        $table->string('telefono_remitente')->nullable();
	        $table->string('direccion_remitente')->nullable();
	        $table->string('ciudad_origen')->nullable();
	        $table->string('tipo_identificacion_destinatario')->nullable();
	        $table->string('identificacion_destinatario')->nullable();
	        $table->string('primer_nombre_destinatario')->nullable();
	        $table->string('segundo_nombre_destinatario')->nullable();
	        $table->string('primer_apellido_destinatario')->nullable();
	        $table->string('segundo_apellido_destinatario')->nullable();
	        $table->string('razon_social_destinatario')->nullable();
	        $table->string('naturaleza_destinatario')->nullable();
	        $table->string('telefono_destinatario')->nullable();
	        $table->string('direccion_destinatario')->nullable();
	        $table->string('ciudad_destinatario')->nullable();
	        $table->string('total_peso')->nullable();
	        $table->string('total_peso_volumen')->nullable();
	        $table->string('total_valor_mercancia')->nullable();
	        $table->string('observaciones')->nullable();
	        $table->string('url_relacion_envio')->nullable();
	        $table->string('url_rotulos')->nullable();
	        $table->string('img_relacion_envio')->nullable();
	        $table->string('img_rotulos')->nullable();
	        $table->string('mensaje_tcc')->nullable();
	        
	        
	        
	        
	        
            
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
        Schema::dropIfExists('tcc_remesas');
    }
}
