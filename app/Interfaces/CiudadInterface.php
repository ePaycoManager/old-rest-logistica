<?php
	
	namespace App\Interfaces;
	
	interface CiudadInterface{
		public function listarPlano();
		public function listarDepartamentos();
		public function listarAgrupadoDepartamentos();
		public function validarCiudad($codigo);
		public function codigoTcc($codigo);
		
	}