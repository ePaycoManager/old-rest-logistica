<?php
	
	namespace App\Http\Controllers;
	
	
	use App\Interfaces\CiudadInterface;
	
	class CiudadController extends ApiController {
		
		private $ciudad;
		
		
		public function __construct( CiudadInterface $ciudad) {
			$this->ciudad = $ciudad;
		}
		
		public function index(){
			
			return $this->generateResponse($this->ciudad->listarPlano(),'true','200','Ciudades consultadas correctamente');
			
		}
		
		
		public function departamentos(){
		
			return $this->generateResponse($ciudades = $this->ciudad->listarDepartamentos(),'true','200','Departamentos consultados correctamente');
			
			
			
		}
		
		public function ciudadesAgrupado(){
			
			return $this->generateResponse($this->ciudad->listarAgrupadoDepartamentos(),'true','200','Ciudades agrupadas consultadas correctamente');
	
			
		}
	}