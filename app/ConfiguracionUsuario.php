<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ConfiguracionUsuario extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = "config_usuario";
  
    protected $fillable = ['id','id_usuario_rest_pagos','direccion','telefono','ciudad','nombre','documento','tipo_documento','tipo_persona','recogida_automatica','dias','segmento','tag'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}

