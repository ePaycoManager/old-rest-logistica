<?php

namespace App\Http\Controllers;

use App\Providers\SoapConsumeService;
use App\TccRecogida;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Interfaces\SoapInterface;
use phpDocumentor\Reflection\Types\Object_;


class RecogidaController extends ApiController
{
	
	
	public function Recogida(Request $request, $operador = null)
    {
    	if($operador != null) {
	        if ($operador == 'tcc') {
	        	return $cotizacion = $this->generateResponse($this->tccRecogida($this->getParameters($request),$request),'true','200','Respuesta de TCC dada correctamente');
		    } else {
		        return $this->generateResponse('','false','410',' Actualmente no contamos con el operador logistico seleccionado');
	        }
		   
	    } else {
    		return $this->generateResponse('','false','410',' no se ha seleccionado operador logistico');
	    }
    }
    
    
    protected function tccRecogida($params, Request $request){
		$url = 'http://clientes.tcc.com.co/preservicios/wsrecogidas.asmx?wsdl';//guardar en una tabla de configuracion general
	
	    $solicitarRecogida =  new Object_();
	    
	    $solicitarRecogida->clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
	    $solicitarRecogida->SolicitudRecogida = new Object_();
	    $solicitarRecogida->SolicitudRecogida->Solicitante = new Object_();
	    $solicitarRecogida->SolicitudRecogida->Solicitante->IDCliente = $params['IDCliente'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->IDSucursal = '';
	    $solicitarRecogida->SolicitudRecogida->Solicitante->Cuenta = $params['CuentaSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->Telefono = $params['TelefonoSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->Ciudad = $params['CiudadSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->NombreCliente = $params['NombreClienteSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->PersonaSolicita = $params['PersonaSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->TipoDocumento = $params['TipoDocumentoPersonaSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Solicitante->Identificacion = $params['IdentificacionSolicitante'];
	    $solicitarRecogida->SolicitudRecogida->Remitente = new Object_();
	    $solicitarRecogida->SolicitudRecogida->Remitente->IDCliente = $params['IDCliente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->IDSucursal = '';
	    $solicitarRecogida->SolicitudRecogida->Remitente->Telefono =  $params['TelefonoRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->Ciudad =  $params['CiudadRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->NombreCliente =  $params['NombreClienteRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->PersonaContacto =  $params['PersonaContactoRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->Direccion =  $params['DireccionRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->DireccionInfoAdicional =  $params['DireccionInfoAdicionalRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->TipoDocumento =  $params['TipoDocumentoRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Remitente->Identificacion =  $params['IdentificacionRemitente'];
	    $solicitarRecogida->SolicitudRecogida->Servicio = new Object_();
	    $solicitarRecogida->SolicitudRecogida->Servicio->Fecha = $params['FechaServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->TipoServicio = '';
	    $solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = $params['HoraInicialServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = $params['HoraFinalServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->Unidades = $params['UnidadesServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->Peso = $params['PesoServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->Volumen = $params['VolumenServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->ValorMercancia = $params['ValorMercanciaServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->Observaciones = $params['ObservacionesServicio'];
	    $solicitarRecogida->SolicitudRecogida->Servicio->CDPago = $params['CDPagoServicio'];
	    $solicitarRecogida->recogida = '';
	    $solicitarRecogida->respuesta = '';
	    $solicitarRecogida->mensaje = 'r';
	  
	    $soapResponse = $this->soap->consumeSoap('',$solicitarRecogida,$url,'SolicitarRecogida');
	    
	    $recogidaTCC = new TccRecogida();
	    $date = new \DateTime('now');
	    
	    if($soapResponse->recogida != -1) {
		
		    $recogidaTCC->id_cliente  = $solicitarRecogida->SolicitudRecogida->Solicitante->IDCliente;
		    $recogidaTCC->cuenta_cliente = $solicitarRecogida->SolicitudRecogida->Solicitante->Cuenta;
		    $recogidaTCC->telefono_cliente = $solicitarRecogida->SolicitudRecogida->Solicitante->Telefono;
		    $recogidaTCC->ciudad_cliente = $solicitarRecogida->SolicitudRecogida->Solicitante->Ciudad;
		    $recogidaTCC->nombre_cliente = $solicitarRecogida->SolicitudRecogida->Solicitante->NombreCliente;
		    $recogidaTCC->persona_solicita = $solicitarRecogida->SolicitudRecogida->Solicitante->PersonaSolicita;
		    $recogidaTCC->tipo_documento_solicita = $solicitarRecogida->SolicitudRecogida->Solicitante->TipoDocumento;
		    $recogidaTCC->identificacion_solicita = $solicitarRecogida->SolicitudRecogida->Solicitante->Identificacion;
		    $recogidaTCC->telefono_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->Telefono;
		    $recogidaTCC->ciudad_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->Ciudad;
		    $recogidaTCC->nombre_cliente_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->NombreCliente;
		    $recogidaTCC->persona_contacto_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->PersonaContacto;
		    $recogidaTCC->direccion_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->Direccion;
		    $recogidaTCC->direccion_info_adicional_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->DireccionInfoAdicional;
		    $recogidaTCC->tipo_documento_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->TipoDocumento;
		    $recogidaTCC->identificacion_remitente = $solicitarRecogida->SolicitudRecogida->Remitente->Identificacion;
		    $recogidaTCC->fecha_recogida = $solicitarRecogida->SolicitudRecogida->Servicio->Fecha;
		    $recogidaTCC->hora_inicial_recogida = $solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial;
		    $recogidaTCC->hora_final_recogida = $solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal;
		    $recogidaTCC->unidades = $solicitarRecogida->SolicitudRecogida->Servicio->Unidades;
		    $recogidaTCC->peso = $solicitarRecogida->SolicitudRecogida->Servicio->Peso;
		    $recogidaTCC->volumen = $solicitarRecogida->SolicitudRecogida->Servicio->Volumen;
		    $recogidaTCC->valor_mercancia = $solicitarRecogida->SolicitudRecogida->Servicio->ValorMercancia;
		    $recogidaTCC->observaciones = $solicitarRecogida->SolicitudRecogida->Servicio->Observaciones;
		    $recogidaTCC->cdpago = $solicitarRecogida->SolicitudRecogida->Servicio->CDPago;
		    $recogidaTCC->id_tcc = $soapResponse->recogida;
		    $recogidaTCC->id_user = $this->user_interface->getIdUserRestPagos($request->get('api_token'));
		    $recogidaTCC->save();
		    $recogidaTCC = TccRecogida::find( $recogidaTCC->id );
		
		    // aca logica de negocio
		
		    return $recogidaTCC;
	    }
	    else {
	    	return $soapResponse;
	    }
	    
    }
    
}
