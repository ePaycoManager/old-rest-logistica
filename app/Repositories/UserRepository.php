<?php
	namespace App\Repositories;
	
	use App\Interfaces\UserInterface;
	use Illuminate\Support\Facades\DB;
	
	
	
	class UserRepository implements UserInterface {
		
		
		
		public function getIdUserRestPagos($api_token) {
			
			if($api_token != null){
				$response =  DB::table('users')->select('id_user_rest_pagos')->where('api_token',$api_token)->first();
				$response = $response->id_user_rest_pagos;
			} else {
				$response = 'No ha enviado ningun parametro';
			}
			
			return $response;
			
			
		}
	}