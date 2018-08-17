<?php
	
	namespace App\Http\Controllers;
	
	
	use App\Interfaces\CiudadInterface;
	
	class CiudadController extends ApiController {
		
		private $ciudad;
		
		
		public function __construct( CiudadInterface $ciudad) {
			$this->ciudad = $ciudad;
		}
		
		public function index(){
			
			$ciudades = $this->ciudad->listarPlano();
			
			$response = $this->generateResponse($ciudades,'200','Ciudades consultadas correctamente');
			
			return $response;
		}
		
		
		public function departamentos(){
			
			$departamentos  = $ciudades = $this->ciudad->listarDepartamentos();
			
			$response = $this->generateResponse($departamentos,'200','Departamentos consultados correctamente');
			
			return $response;
			
		}
		
		public function ciudadesAgrupado(){
			
			$ciudadesAgrupado= $this->ciudad->listarAgrupadoDepartamentos();
			
			$response = $this->generateResponse($ciudadesAgrupado,'200','Ciudades agrupadas consultadas correctamente');
			
			return $response;
			
		}
	}