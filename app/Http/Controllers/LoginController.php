<?php
	
	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use Illuminate\Validation\ValidationException;
	use Illuminate\Support\Facades\Hash;
	use App\User;
	
	class LoginController extends Controller {
		public function login( Request $request ) {
			
//			$rules = [
//				'public_key' => 'required',
//			];
//
//			$customMessages = [
//				'required' => ':attribute tidak boleh kosong'
//			];
//			$this->validate( $request, $rules, $customMessages );
			$public_key = $request->header( 'php-auth-user' );
			try {
				$login = User::where( 'public_key', $public_key )->first();
				if ( $login ) {
					
					try {
						$api_token = sha1( $login->public_key . time() );
						
						$create_token     = User::where( 'public_key', $login->public_key )->update( [ 'api_token' => $api_token ] );
						$res['status']    = true;
						$res['message']   = 'Login exitoso';
						$res['data']      = $login;
						$res['api_token'] = $api_token;
						
						return response( $res, 200 );
						
						
					} catch ( \Illuminate\Database\QueryException $ex ) {
						$res['status']  = false;
						$res['message'] = $ex->getMessage();
						
						return response( $res, 500 );
					}
					
				} else {
					$validar_comercio = $this->validarCliente( $public_key );
					if ( $validar_comercio ) {
						$user             = new User();
						$user->public_key = $public_key;
						$user->save();
						$api_token = sha1( $public_key . time() );
						$create_token     = User::where( 'public_key', $user->public_key )->update( [ 'api_token' => $api_token ] );
						$res['status']    = true;
						$res['message']   = 'Login exitoso';
						$res['data']      = $user;
						$res['api_token'] = $api_token;
						
						return response( $res, 200 );
					} else {
						$res['success'] = false;
						$res['message'] = 'No ha validado el comercio correctamente.';
						
						return response( $res, 401 );
					}
					
				}
			} catch ( \Illuminate\Database\QueryException $ex ) {
				$res['success'] = false;
				$res['message'] = $ex->getMessage();
				
				return response( $res, 500 );
			}
		}
		
		
		public function validarCliente( $public_key ) {
			$curl = curl_init();
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => "http://localhost/restpagos/index.php/api/login/check",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"public_key\"\r\n\r\n".$public_key."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
				CURLOPT_HTTPHEADER => array(
					"Cache-Control: no-cache",
					"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
				),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);
			$response = json_decode($response);
			
			if ($err) {
				return false;
			} else {
				if(is_object($response)){
					return true;
				} else{
					return false;
				}
				
			}
		}
	}