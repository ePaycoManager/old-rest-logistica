<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ConfigNegocio extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = "config_negocio";
    ////$table->increments('id');
	//	        //	        $table->string('operador');
	//	        //	        $table->string('operacion');
	//	        //	        $table->string('regla');
	//	        //	        $table->string('tipo');
	//	        //	        $table->string('valor');
   
    protected $fillable = ['id','id_cliente','usuario_epayco','url_cotizar','url_guia','url_recogida','porcentaje_recogida','porcentaje_guia','procentaje_cotizar','operador','activo'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    
    ];
}

