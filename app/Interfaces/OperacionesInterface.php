<?php
	
	namespace App\Interfaces;
	use Illuminate\Http\Request;
	
	
	interface OperacionesInterface{
		public function tccCotizacion($data, Request $request);
		public function tccGuia($data, Request $request);
		public function tccRecogida($data, Request $request);
		public function tccRecogidaCron($data, Request $request);
		public function listaGuias($data, Request $request);
		public function configuracionUsuario($data, Request $request);
		public function configuracionEditar($data, Request $request);
	}