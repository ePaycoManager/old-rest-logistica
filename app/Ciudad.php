<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model

{
	protected $table = 'ciudades';
	protected $fillable = ['codigo_dane', 'nombre', 'departamento','codigo_tcc','id_rest_payco'];
	
	protected $hidden = ['codigo_tcc','id_rest_payco'];
}
?>




