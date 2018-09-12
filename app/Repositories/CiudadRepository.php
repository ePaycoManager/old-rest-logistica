<?php
	namespace App\Repositories;
	
	use App\Interfaces\CiudadInterface;
	use Illuminate\Support\Facades\DB;
	use App\Ciudad;
	
	
	class CiudadRepository implements CiudadInterface {
		
		public function listarPlano() {
			$ciudades  = Ciudad::all();
			return $ciudades;
		}
		
		public function listarDepartamentos() {
			$departamentos = DB::table('ciudades')->orderBy('departamento','asc')->distinct()->get(['departamento']);
			return $departamentos;
		}
		
		public function listarAgrupadoDepartamentos() {
			$response = array();
			$departamentos  = $this->listarDepartamentos();
			foreach ($departamentos as $departamento){
				$response[$departamento->departamento] =  DB::table('ciudades')->where('departamento',$departamento->departamento)->get();
			}
			
			return $response;
		}
		
		public function validarCiudad($codigo) {
			
			$validate = DB::table('ciudades')->where('codigo_dane',$codigo)->get()->first();
			
			if($validate){
				return true;
			} else {
				return false;
			}
			
		}
		
		public function codigoTcc($codigo) {
			
			$ciudad = DB::table('ciudades')->where('codigo_dane',$codigo)->get()->first();
			
			if($ciudad){
				return $ciudad->codigo_tcc;
			} else {
				return false;
			}
			
		}
	}