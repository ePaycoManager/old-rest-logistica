<?php

namespace App\Http\Controllers;

use App\Providers\SoapConsumeService;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Interfaces\SoapInterface;
use phpDocumentor\Reflection\Types\Object_;


class CotizarController extends ApiController
{
	public function __construct(SoapInterface $soap)
	{
		$this->soap = $soap;
	}
	
	public function Cotizar(Request $request, $operador = null)
    {
    	if($operador != null) {
	        if ($operador == 'tcc') {
	            return $cotizacion = $this->generateResponse($this->tccCotizacion($this->getParameters($request)),'true','200','Respuesta de TCC dada correctamente');
		    } else {
		        return $this->generateResponse('','false','410',' Actualmente no contamos con el operador logistico seleccionado');
	        }
		   
	    } else {
    		return $this->generateResponse('','false','410',' no se ha seleccionado operador logistico');
	    }
    }
    
    
    protected function tccCotizacion($params){
		$date = new \DateTime('now');
        $params['clave']='CLIENTETCC608W3A61CJ';
        $consultarliquidacion =  new Object_();
        $consultarliquidacion->Clave='CLIENTETCC608W3A61CJ';
	    $consultarliquidacion->Liquidacion = new Object_();
	    $consultarliquidacion->Liquidacion->tipoenvio='';
	    $consultarliquidacion->Liquidacion->idciudadorigen ='76001000';
	    $consultarliquidacion->Liquidacion->idciudaddestino ='05001000';
	    $consultarliquidacion->Liquidacion->valormercancia =165000;
	    $consultarliquidacion->Liquidacion->boomerang = 0;
	    $consultarliquidacion->Liquidacion->cuenta = 0;
	    $consultarliquidacion->Liquidacion->fecharemesa = '08/02/2018';
	    $consultarliquidacion->Liquidacion->idunidadestrategicanegocio = 2;
	    $consultarliquidacion->Liquidacion->unidades = new Object_();
	    $consultarliquidacion->Liquidacion->unidades->unidad = new Object_();
	    $consultarliquidacion->Liquidacion->unidades->unidad->numerounidades=1;
	    $consultarliquidacion->Liquidacion->unidades->unidad->pesoreal=1;
	    $consultarliquidacion->Liquidacion->unidades->unidad->pesovolumen=1;
	    $consultarliquidacion->Liquidacion->unidades->unidad->alto=0;
	    $consultarliquidacion->Liquidacion->unidades->unidad->largo=0;
	    $consultarliquidacion->Liquidacion->unidades->unidad->ancho=0;
	    
	    
        
        
        return $this->soap->consumeSoap('',$consultarliquidacion,'http://clientes.tcc.com.co/preservicios/liquidacionacuerdos.asmx?wsdl','consultarliquidacion');
    }
    
}
