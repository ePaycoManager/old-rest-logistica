<?php

	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use phpDocumentor\Reflection\Types\Mixed_;
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
		
		/**
		 * @param Mixed $data
		 * @param Mixed $status
		 * @param Mixed $message
		 */
		
		protected function generateResponse($data = '',$status = '', $code='',$message = ''){
			$response  = new Object_();
			$response->status = '';
			$response->code = '';
			$response->message = '';
			$response->data = '';
			if($data != '' || $status != '' || $message != '' || $code != ''){
				if($data != ''){
					$response->data = $data;
				}
				if($status != ''){
					$response->status = $status;
				}
				if($code != ''){
					$response->code = $code;
				}
				if($message != ''){
					$response->message = $message;
				}
			}
			return response()->json($response);
		}
	}
