<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;



class IndexController extends ApiController
{
	
	public function index(Request $request, $operador = null, $operacion = null)
    {
    	if($operador != null && $operacion != null) {
    		$op = $operador.ucfirst($operacion);
    		try{
			    return $this->generateResponse($this->operaciones->$op($this->getParameters($request), $request),'true','200','');
		    }
    		catch (\Exception $e){
			    return $this->generateResponse($e,'false','405','No se han enviado parametros o no existe la operacion o el operador logistico');
		    }
	    }
	}
}
