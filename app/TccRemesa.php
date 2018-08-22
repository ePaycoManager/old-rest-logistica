<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TccRemesa extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = "tcc_remesas";
    protected $fillable = [
        'id','fecha_lote','numero_remesa','unidad_negocio','fecha_despacho','cuenta_remitente','primer_nombre_remitente','segundo_nombre_remitente','primer_apellido_remitente','segundo_apellido_remitente','razon_social_remitente','naturaleza_remitente','tipo_identificacion_remitente','telefono_remitente','direccion_remitente','ciudad_origen','tipo_identificacion_destinatario','identificacion_destinatario','primer_nombre_destinatario','segundo_nombre_destinatario','primer_apellido_destinatario','segundo_apellido_destinatario','razon_social_destinatario','naturaleza_destinatario','telefono_destinatario','direccion_destinatario','ciudad_destinatario','total_peso','total_peso_volumen','total_valor_mercancia','observaciones','url_relacion_envio','url_rotulos','img_relacion_envio','img_rotulos','mensaje_tcc','id_user'];


    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    
     
    ];
}

