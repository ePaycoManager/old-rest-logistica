<?php
	
	namespace App\Services;
	
	
	use App\Interfaces\SoapInterface;
	
	
	use SoapClient;
	
	class SoapConsumeService implements SoapInterface
	{
		
		public function register(){
			return $this;
		}
		
		private function _client($wsdl = '') {
			$opts = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			
			$context = stream_context_create($opts);
		
			
			
			try {
				$this->client = new SoapClient($wsdl, array(
						'stream_context' => $context,
						'trace' => true
						)
				);
				
				return $this->client;
			}
			
			catch ( \Exception $e) {
				return $e->getMessage();
			}
		}
		
		public function consumeSoap($headers = null, $data = null, $wsdl = null, $function = null){
			$this->client = $this->_client($wsdl);
			
			
			if($headers != null){
				$this->client->__setSoapHeaders($headers);
			}
			try {
				
				$result = $this->client->$function($data);

				
				return $result;
			}
			
			catch (\Exception $e) {
				
				return $e;
			}
			
		}
		
		
		
	}