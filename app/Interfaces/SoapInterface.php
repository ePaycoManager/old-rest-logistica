<?php
	
	namespace App\Interfaces;
	
	interface SoapInterface{
		public function consumeSoap($headers , $data , $wsdl , $function);
	}