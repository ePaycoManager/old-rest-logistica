<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model

{
	protected $table = 'ciudades';
	protected $fillable = ['codigo_dane', 'nombre', 'departamento'];
}
?>




