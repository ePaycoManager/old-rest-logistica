<?php

namespace App\Http\Controllers;

use App\Providers\SoapConsumeService;
use App\TccRemesa;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Interfaces\SoapInterface;
use phpDocumentor\Reflection\Types\Object_;


class RemesaController extends ApiController
{
	
	
	public function Remesa(Request $request, $operador = null)
    {
    	if($operador != null) {
	        if ($operador == 'tcc') {
	        	return $cotizacion = $this->generateResponse($this->tccRemesa($this->getParameters($request), $request),'true','200','Respuesta de TCC dada correctamente');
		    } else {
		        return $this->generateResponse('','false','410',' Actualmente no contamos con el operador logistico seleccionado');
	        }
		   
	    } else {
    		return $this->generateResponse('','false','410',' no se ha seleccionado operador logistico');
	    }
    }
    
    
    protected function tccRemesa($params, Request $request){
		$url = 'http://clientes.tcc.com.co/preservicios/wsdespachos.asmx?wsdl';//guardar en una tabla de configuracion general
	
	    $GrabarDespacho4 =  new Object_();
	    $GrabarDespacho4->objDespacho = new Object_();
	    $GrabarDespacho4->objDespacho->clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
	   // $GrabarDespacho4->objDespacho->codigolote='';//guardar en una tabla de configuracion general
	    $GrabarDespacho4->objDespacho->fechahoralote = $params['fechahoralote'];
	    $GrabarDespacho4->objDespacho->numeroremesa = $params['numeroremesa'];
	    $GrabarDespacho4->objDespacho->numeroDepacho = $params['numeroDepacho'];
	    $GrabarDespacho4->objDespacho->unidadnegocio = $params['unidadnegocio'];
	    $GrabarDespacho4->objDespacho->fechadespacho = $params['fechadespacho'];
	    $GrabarDespacho4->objDespacho->cuentaremitente = $params['cuentaremitente'];
	    $GrabarDespacho4->objDespacho->sederemitente =  $params['sederemitente'];
	    $GrabarDespacho4->objDespacho->primernombreremitente = $params['primernombreremitente'];
	    $GrabarDespacho4->objDespacho->segundonombreremitente = $params['segundonombreremitente'];
	    $GrabarDespacho4->objDespacho->primerapellidoremitente = $params['primerapellidoremitente'];
	    $GrabarDespacho4->objDespacho->segundoapellidoremitente =$params['segundoapellidoremitente'];
	    $GrabarDespacho4->objDespacho->razonsocialremitente = $params['razonsocialremitente'];
	    $GrabarDespacho4->objDespacho->naturalezaremitente = $params['naturalezaremitente'];
	    $GrabarDespacho4->objDespacho->tipoidentificacionremitente = $params['tipoidentificacionremitente'];
	    $GrabarDespacho4->objDespacho->identificacionremitente = $params['identificacionremitente'];
	    $GrabarDespacho4->objDespacho->telefonoremitente = $params['telefonoremitente'];
	    $GrabarDespacho4->objDespacho->direccionremitente = $params['direccionremitente'];
	    $GrabarDespacho4->objDespacho->ciudadorigen = $params['ciudadorigen'];
	    $GrabarDespacho4->objDespacho->tipoidentificaciondestinatario = $params['tipoidentificaciondestinatario'];
	    $GrabarDespacho4->objDespacho->identificaciondestinatario = $params['identificaciondestinatario'];
	    $GrabarDespacho4->objDespacho->sededestinatario = $params['sededestinatario'];
	    $GrabarDespacho4->objDespacho->primernombredestinatario = $params['primernombredestinatario'];
	    $GrabarDespacho4->objDespacho->segundonombredestinatario = $params['segundonombredestinatario'];
	    $GrabarDespacho4->objDespacho->primerapellidodestinatario = $params['primerapellidodestinatario'];
	    $GrabarDespacho4->objDespacho->segundoapellidodestinatario = $params['segundoapellidodestinatario'];
	    $GrabarDespacho4->objDespacho->razonsocialdestinatario = $params['razonsocialdestinatario'];
	    $GrabarDespacho4->objDespacho->naturalezadestinatario = $params['naturalezadestinatario'];
	    $GrabarDespacho4->objDespacho->direcciondestinatario = $params['direcciondestinatario'];
	    $GrabarDespacho4->objDespacho->telefonodestinatario = $params['telefonodestinatario'];
	    $GrabarDespacho4->objDespacho->ciudaddestinatario = $params['ciudaddestinatario'];
	    $GrabarDespacho4->objDespacho->barriodestinatario = $params['barriodestinatario'];
	    $GrabarDespacho4->objDespacho->totalpeso = $params['totalpeso'];
	    $GrabarDespacho4->objDespacho->totalpesovolumen = $params['totalpesovolumen'];
	    $GrabarDespacho4->objDespacho->totalvalormercancia = $params['totalvalormercancia'];
	    $GrabarDespacho4->objDespacho->formapago = $params['formapago'];
	    $GrabarDespacho4->objDespacho->observaciones = $params['observaciones'];
	    $GrabarDespacho4->objDespacho->llevabodega = $params['llevabodega'];
	    $GrabarDespacho4->objDespacho->recogebodega = $params['recogebodega'];
	    $GrabarDespacho4->objDespacho->centrocostos = $params['centrocostos'];
	    $GrabarDespacho4->objDespacho->totalvalorproducto = $params['totalvalorproducto'];
	    
	    $GrabarDespacho4->objDespacho->unidad = new Object_();
	    $GrabarDespacho4->objDespacho->unidad->tipounidad = $params['tipounidad'];
	    $GrabarDespacho4->objDespacho->unidad->tipoempaque = $params['tipoempaque'];
	    $GrabarDespacho4->objDespacho->unidad->claseempaque = $params['claseempaque'];
	    $GrabarDespacho4->objDespacho->unidad->dicecontener = $params['dicecontener'];
	    $GrabarDespacho4->objDespacho->unidad->cantidadunidades = $params['cantidadunidades'];
	    $GrabarDespacho4->objDespacho->unidad->kilosreales = $params['kilosreales'];
	    $GrabarDespacho4->objDespacho->unidad->largo = $params['largo'];
	    $GrabarDespacho4->objDespacho->unidad->alto = $params['alto'];
	    $GrabarDespacho4->objDespacho->unidad->ancho = $params['ancho'];
	    $GrabarDespacho4->objDespacho->unidad->pesovolumen = $params['pesovolumen'];
	    $GrabarDespacho4->objDespacho->unidad->valormercancia = $params['valormercancia'];
	    $GrabarDespacho4->objDespacho->unidad->codigobarras = $params['codigobarras'];
	    $GrabarDespacho4->objDespacho->unidad->numerobolsa = $params['numerobolsa'];
	    $GrabarDespacho4->objDespacho->unidad->referencias = $params['referencias'];
	    
	    
	    $GrabarDespacho4->objDespacho->documentoreferencia = new Object_();
	    $GrabarDespacho4->objDespacho->documentoreferencia->tipodocumento = $params['tipodocumento'];
	    $GrabarDespacho4->objDespacho->documentoreferencia->numerodocumento = $params['numerodocumento'];
	    $GrabarDespacho4->objDespacho->documentoreferencia->fechadocumento = $params['fechadocumento'];
	    $GrabarDespacho4->objDespacho->numeroReferenciaCliente = $params['numeroReferenciaCliente'];
	    $GrabarDespacho4->objDespacho->fuente = $params['fuente'];
	    $GrabarDespacho4->objDespacho->generarDocumentos = true;
	    $GrabarDespacho4->respuesta = '';//no definido en doc
	
	    $soapResponse = $this->soap->consumeSoap('',$GrabarDespacho4,$url,'GrabarDespacho4');
	    
	    $remesaTCC = new TccRemesa();
	    $date = new \DateTime('now');
	    if($soapResponse->respuesta != "-1"){
		    $remesaTCC->fecha_lote = $date;
		    $remesaTCC->numero_remesa =  $soapResponse->remesa;
		    $remesaTCC->unidad_negocio = $GrabarDespacho4->objDespacho->unidadnegocio;
		    $remesaTCC->fecha_despacho = $GrabarDespacho4->objDespacho->fechadespacho;
		    $remesaTCC->cuenta_remitente = $GrabarDespacho4->objDespacho->cuentaremitente;
		    $remesaTCC->primer_nombre_remitente = $GrabarDespacho4->objDespacho->primernombreremitente;
		    $remesaTCC->segundo_nombre_remitente = $GrabarDespacho4->objDespacho->segundonombreremitente;
		    $remesaTCC->primer_apellido_remitente = $GrabarDespacho4->objDespacho->primerapellidoremitente;
		    $remesaTCC->segundo_apellido_remitente = $GrabarDespacho4->objDespacho->segundoapellidoremitente;
		    $remesaTCC->razon_social_remitente = $GrabarDespacho4->objDespacho->razonsocialremitente;
		    $remesaTCC->naturaleza_remitente = $GrabarDespacho4->objDespacho->naturalezaremitente;
		    $remesaTCC->tipo_identificacion_remitente = $GrabarDespacho4->objDespacho->tipoidentificacionremitente;
		    $remesaTCC->telefono_remitente = $GrabarDespacho4->objDespacho->telefonoremitente;
		    $remesaTCC->direccion_remitente = $GrabarDespacho4->objDespacho->direccionremitente;
		    $remesaTCC->ciudad_origen = $GrabarDespacho4->objDespacho->ciudadorigen;
		    $remesaTCC->tipo_identificacion_destinatario = $GrabarDespacho4->objDespacho->tipoidentificaciondestinatario;
		    $remesaTCC->identificacion_destinatario = $GrabarDespacho4->objDespacho->identificaciondestinatario;
		    $remesaTCC->primer_nombre_destinatario = $GrabarDespacho4->objDespacho->primernombredestinatario;
		    $remesaTCC->segundo_nombre_destinatario = $GrabarDespacho4->objDespacho->segundonombredestinatario;
		    $remesaTCC->primer_apellido_destinatario = $GrabarDespacho4->objDespacho->primerapellidodestinatario;
		    $remesaTCC->segundo_apellido_destinatario = $GrabarDespacho4->objDespacho->segundoapellidodestinatario;
		    $remesaTCC->razon_social_destinatario = $GrabarDespacho4->objDespacho->razonsocialdestinatario;
		    $remesaTCC->naturaleza_destinatario = $GrabarDespacho4->objDespacho->naturalezadestinatario;
		    $remesaTCC->telefono_destinatario = $GrabarDespacho4->objDespacho->telefonodestinatario;
		    $remesaTCC->direccion_destinatario = $GrabarDespacho4->objDespacho->direcciondestinatario;
		    $remesaTCC->ciudad_destinatario = $GrabarDespacho4->objDespacho->ciudaddestinatario;
		    $remesaTCC->total_peso = $GrabarDespacho4->objDespacho->totalpeso;
		    $remesaTCC->total_peso_volumen = $GrabarDespacho4->objDespacho->totalpesovolumen;
		    $remesaTCC->total_valor_mercancia = $GrabarDespacho4->objDespacho->totalvalormercancia;
		    $remesaTCC->observaciones = $GrabarDespacho4->objDespacho->observaciones;
		    $remesaTCC->url_relacion_envio =  $soapResponse->URLRelacionEnvio;
		    $remesaTCC->url_rotulos =  $soapResponse->URLRotulos;
		    $remesaTCC->img_relacion_envio =  $soapResponse->IMGRelacionEnvio;
		    $remesaTCC->img_rotulos =  $soapResponse->IMGRotulos;
		    $remesaTCC->mensaje_tcc =  $soapResponse->mensaje;
		    $remesaTCC->id_user = $this->user_interface->getIdUserRestPagos($request->get('api_token'));
		    $remesaTCC->save();
		    $remesa =  TccRemesa::find($remesaTCC->id);
		    // aca logica de negocio
			
		    return $remesa;
	    }
	    else {
	    	return $soapResponse;
	    }
	    
    }
    
}
