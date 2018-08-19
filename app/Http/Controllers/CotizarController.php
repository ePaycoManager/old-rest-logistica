<?php

namespace App\Http\Controllers;

use App\Providers\SoapConsumeService;
use App\TccLiquidacion;
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
	        	$params = $this->getParameters($request);
	        	
	            return $cotizacion = $this->generateResponse($this->tccCotizacion($this->getParameters($request)),'true','200','Respuesta de TCC dada correctamente');
		    } else {
		        return $this->generateResponse('','false','410',' Actualmente no contamos con el operador logistico seleccionado');
	        }
		   
	    } else {
    		return $this->generateResponse('','false','410',' no se ha seleccionado operador logistico');
	    }
    }
    
    
    protected function tccCotizacion($params){
		$url = 'http://clientes.tcc.com.co/preservicios/liquidacionacuerdos.asmx?wsdl';//guardar en una tabla de configuracion general
	   
        $consultarliquidacion =  new Object_();
        $consultarliquidacion->Clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
	    $consultarliquidacion->Liquidacion = new Object_();
	    $consultarliquidacion->Liquidacion->tipoenvio=$params['tipoenvio'];
	    $consultarliquidacion->Liquidacion->idciudadorigen =$params['idciudadorigen'];
	    $consultarliquidacion->Liquidacion->idciudaddestino =$params['idciudaddestino'];
	    $consultarliquidacion->Liquidacion->valormercancia =$params['valormercancia'];
	    $consultarliquidacion->Liquidacion->boomerang = 0;
	    $consultarliquidacion->Liquidacion->cuenta = 0;
	    $consultarliquidacion->Liquidacion->fecharemesa = $params['fecharemesa'];
	    $consultarliquidacion->Liquidacion->idunidadestrategicanegocio = 2;
	    $consultarliquidacion->Liquidacion->unidades = new Object_();
	    $consultarliquidacion->Liquidacion->unidades->unidad = new Object_();
	    $consultarliquidacion->Liquidacion->unidades->unidad->numerounidades=$params['unidad']['numerounidades'];
	    $consultarliquidacion->Liquidacion->unidades->unidad->pesoreal=$params['unidad']['pesoreal'];
	    $consultarliquidacion->Liquidacion->unidades->unidad->pesovolumen=$params['unidad']['pesovolumen'];
	    $consultarliquidacion->Liquidacion->unidades->unidad->alto=$params['unidad']['alto'];
	    $consultarliquidacion->Liquidacion->unidades->unidad->largo=$params['unidad']['largo'];
	    $consultarliquidacion->Liquidacion->unidades->unidad->ancho=$params['unidad']['ancho'];
	    
	    $soapResponse = $this->soap->consumeSoap('',$consultarliquidacion,$url,'consultarliquidacion');
	    
	    $liquidacionTCC = new TccLiquidacion();
	   
	    $liquidacionTCC->id_ciudad_origen = (string)$consultarliquidacion->Liquidacion->idciudadorigen;
	    $liquidacionTCC->id_ciudad_destino = (string)$consultarliquidacion->Liquidacion->idciudaddestino;
	    $liquidacionTCC->valor_mercancia = (string)$consultarliquidacion->Liquidacion->valormercancia;
	    $liquidacionTCC->boomerang = (string)$consultarliquidacion->Liquidacion->boomerang;
	    $liquidacionTCC->cuenta = (string)$consultarliquidacion->Liquidacion->cuenta;
		$liquidacionTCC->fecha_remesa = (string)$consultarliquidacion->Liquidacion->fecharemesa;
		$liquidacionTCC->id_unidad_estrategica_negocio = (string)$consultarliquidacion->Liquidacion->idunidadestrategicanegocio;
		$liquidacionTCC->numero_unidades = (string)$consultarliquidacion->Liquidacion->unidades->unidad->numerounidades;
		$liquidacionTCC->peso_real = (string)$consultarliquidacion->Liquidacion->unidades->unidad->pesoreal;
		$liquidacionTCC->peso_volumen = (string)$consultarliquidacion->Liquidacion->unidades->unidad->pesovolumen;
		$liquidacionTCC->alto = (string)$consultarliquidacion->Liquidacion->unidades->unidad->alto;
		$liquidacionTCC->largo = (string)$consultarliquidacion->Liquidacion->unidades->unidad->largo;
		$liquidacionTCC->ancho = (string)$consultarliquidacion->Liquidacion->unidades->unidad->ancho;
		$liquidacionTCC->tipo_empaque = "0";
		$liquidacionTCC->total_despacho = (string)$soapResponse->consultarliquidacionResult->total->totaldespacho;
		$liquidacionTCC->flete = (string)$soapResponse->consultarliquidacionResult->conceptos->Concepto[0]->valor;
	    $liquidacionTCC->manejo = (string)$soapResponse->consultarliquidacionResult->conceptos->Concepto[1]->valor;
	    $liquidacionTCC->id_tcc = (string)$soapResponse->consultarliquidacionResult->idliquidacion;
	    $liquidacionTCC->save();
	    $liquidacion =  TccLiquidacion::find($liquidacionTCC->id);
	    // aca logica de negocio
	    $liquidacion->total = $soapResponse->consultarliquidacionResult->total->totaldespacho;
	    //DEFINIR SI ESTO SE DEVUELVE EN LA LOGICA DE NEGOCIO
	    $liquidacion->conceptos = $soapResponse->consultarliquidacionResult->conceptos;
	    	  
        return $liquidacion;
    }
    
}
