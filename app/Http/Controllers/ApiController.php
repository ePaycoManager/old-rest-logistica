<?php

	namespace App\Http\Controllers;
	
	use Illuminate\Http\Request;
	use App\Interfaces\OperacionesInterface;

	class ApiController extends Controller
	{
		public function __construct(OperacionesInterface $op)
		{
			$this->operaciones = $op;
		}
		
		/**
		 * @param Request $request
		 */
		protected function getParameters(Request $request) {
			
			$params = [];
			if($request->getContent() !== "") {
			
				$params = json_decode($request->getContent(),true);
				if(!isset($params)){
					$params = $request->getContent();
				}
				
			} else {
				
				$params = $request->getContent();
				
			}
			
			
			
			return $params;
		}
		
		/**
		 * @param Mixed $data
		 * @param Mixed $status
		 * @param Mixed $message
		 */
		
		protected function generateResponse($data = '',$status = '', $code = '',$message = ''){
			$response  =  new \stdClass;
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
