<?php
	
	namespace App\Interfaces;
	use Illuminate\Http\Request;
	
	
	interface OperacionesInterface{
		public function tccCotizacion($data,Request $request);
		public function tccRemesa($data,Request $request);
		public function tccRecogida($data,Request $request);
		
	}