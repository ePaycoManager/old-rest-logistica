<?php

	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use phpDocumentor\Reflection\Types\Object_;
	
	class ApiController extends Controller
	{
	    /**
		 * @param Request $request
		 */
		protected function getParameters(Request $request) {
			$params = [];
			if($request->getContent() !== "") {
				$params = json_decode($request->getContent(), true);
			} else {
				$params = [];
			}
			return $params;
		}
		
		protected function generateResponse($data = '',$status = '',$message = ''){
			$response  = new Object_();
			$response->status = '';
			$response->data = '';
			$response->message = '';
			if($data != '' || $status != '' || $message != ''){
				if($data != ''){
					$response->data = $data;
				}
				if($status != ''){
					$response->status = $status;
				}
				if($message != ''){
					$response->message = $message;
				}
				
			}
			
			return response()->json($response);
		
		}
	 
	}
