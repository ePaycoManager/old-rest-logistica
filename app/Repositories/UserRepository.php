<?php
	namespace App\Repositories;
	
	use App\Interfaces\UserInterface;
	use Illuminate\Support\Facades\DB;
	use App\User;
	
	
	class UserRepository implements UserInterface {
		
		
		
		public function getIdUserRestPagos($api_token) {
			
			if($api_token != null){
				$response =  DB::table('users')->select('Id')->where('api_token',$api_token)->first();
				$response = $response->Id;
			} else {
				$response = 'No ha enviado ningun parametro';
			}
			
			return $response;
			
			
		}
	}