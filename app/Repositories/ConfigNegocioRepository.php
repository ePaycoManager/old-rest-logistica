<?php
	namespace App\Repositories;
	
	use App\Interfaces\ConfigNegocioInterface;
	use Illuminate\Support\Facades\DB;
	use App\ConfigNegocio;
	
	
	class ConfigNegocioRepository implements ConfigNegocioInterface {
		
		public function getConfig($idClient){
			$configNegocio = DB::table('config_negocio')->select('*')->where('id_cliente',$idClient)->first();
			if($configNegocio){
				return $configNegocio;
			}else{
				$configNegocio = DB::table('config_negocio')->select('*')->where('id',1)->first();
				return $configNegocio;
			}
			
		}
	}