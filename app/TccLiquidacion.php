<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class TccLiquidacion extends Model
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id','id_ciudad_origen','id_ciudad_destino','valor_mercancia','boomerang','cuenta','fecha_remesa','id_unidad_estrategica_negocio','numero_unidades','peso_real','peso_volumen','alto','largo','ancho','tipo_empaque'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    	'id'
     
    ];
}


