<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TccRecogida extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = "tcc_recogidas";
    protected $fillable = [
        'id','id_cliente','cuenta_cliente','telefono_cliente','ciudad_cliente','nombre_cliente','persona_solicita','tipo_documento_solicita','identificacion_solicita','telefono_remitente','ciudad_remitente','nombre_cliente_remitente','persona_contacto_remitente','direccion_remitente','direccion_info_adicional_remitente','tipo_documento_remitente','identificacion_remitente','fecha_recogida','hora_inicial_recogida','hora_final_recogida','unidades','peso','volumen','valor_mercancia','observaciones','cdpago'];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    
     
    ];
}

