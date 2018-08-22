<?php
	namespace App\Repositories;
	
	use App\Interfaces\OperacionesInterface;
	use App\Interfaces\SoapInterface;
	use App\Interfaces\UserInterface;
	use Illuminate\Support\Facades\DB;
	use App\User;
	use Illuminate\Http\Request;
	use App\TccLiquidacion;
	use App\TccRecogida;
	use App\TccRemesa;
	
	
	class OperacionesRepository implements OperacionesInterface {
		
		
		public function __construct(SoapInterface $soap, UserInterface $user_interface) {
			$this->soap = $soap;
			$this->user_interface = $user_interface;
		}
		
		//Inicio TCC Operaciones
		
		public function tccCotizacion($data, Request $request){
			$url = 'http://clientes.tcc.com.co/preservicios/liquidacionacuerdos.asmx?wsdl';//guardar en una tabla de configuracion general
			
			$consultarliquidacion =   new \stdClass;
			$consultarliquidacion->Clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
			$consultarliquidacion->Liquidacion =  new \stdClass;
			//$consultarliquidacion->Liquidacion->tipoenvio=$data['tipoenvio'];->esto  no tiene doc ni valor en el xml de ejemplo
			$consultarliquidacion->Liquidacion->idciudadorigen =$data['idciudadorigen'];
			$consultarliquidacion->Liquidacion->idciudaddestino =$data['idciudaddestino'];
			$consultarliquidacion->Liquidacion->valormercancia =$data['valormercancia'];
			$consultarliquidacion->Liquidacion->boomerang = $data['boomerang'];//no afecta en nada el valor de la liquidacion por paqueteria
			$consultarliquidacion->Liquidacion->cuenta = 0; // aca va codigo de ePayco para tarifa - mensajeria siempre cotiza full - para paquetes si el dcto es pie factura no muestra valores del convenio
			$consultarliquidacion->Liquidacion->fecharemesa = $data['fecharemesa'];
			$consultarliquidacion->Liquidacion->idunidadestrategicanegocio = $data['idunidadestrategicanegocio'];// 2 siempre superpone unidad 1 - -  mensajeria solo cotiza de a 1 unidad
			$consultarliquidacion->Liquidacion->unidades =  new \stdClass;
			$consultarliquidacion->Liquidacion->unidades->unidad =  new \stdClass;
			$consultarliquidacion->Liquidacion->unidades->unidad->numerounidades=$data['unidad']['numerounidades'];//que es esto??? afecta valor pero al cambiar cantidad de unidades no cambia valor retornado
			$consultarliquidacion->Liquidacion->unidades->unidad->pesoreal=$data['unidad']['pesoreal'];
			$consultarliquidacion->Liquidacion->unidades->unidad->pesovolumen=$data['unidad']['pesovolumen'];
			$consultarliquidacion->Liquidacion->unidades->unidad->alto=$data['unidad']['alto'];
			$consultarliquidacion->Liquidacion->unidades->unidad->largo=$data['unidad']['largo'];
			$consultarliquidacion->Liquidacion->unidades->unidad->ancho=$data['unidad']['ancho'];
			
			$soapResponse = $this->soap->consumeSoap('',$consultarliquidacion,$url,'consultarliquidacion');
			
			$liquidacionTCC = new TccLiquidacion();
			
			if($soapResponse->consultarliquidacionResult->respuesta->codigo != "-1"){
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
				$liquidacionTCC->id_user = $this->user_interface->getIdUserRestPagos($request->get('api_token'));
				$liquidacionTCC->save();
				$liquidacion =  TccLiquidacion::find($liquidacionTCC->id);
				// aca logica de negocio
				$liquidacion->total = $soapResponse->consultarliquidacionResult->total->totaldespacho;
				//DEFINIR SI ESTO SE DEVUELVE EN LA LOGICA DE NEGOCIO
				$liquidacion->conceptos = $soapResponse->consultarliquidacionResult->conceptos;
				
				return $liquidacion;
			} else {
				return $soapResponse;
			}
		}
		
		public function tccRemesa($data, Request $request){
			$url = 'http://clientes.tcc.com.co/preservicios/wsdespachos.asmx?wsdl';//guardar en una tabla de configuracion general
			
			$GrabarDespacho4 =   new \stdClass;
			$GrabarDespacho4->objDespacho =  new \stdClass;
			$GrabarDespacho4->objDespacho->clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
			// $GrabarDespacho4->objDespacho->codigolote='';//guardar en una tabla de configuracion general
			$GrabarDespacho4->objDespacho->fechahoralote = $data['fechahoralote'];
			$GrabarDespacho4->objDespacho->numeroremesa = $data['numeroremesa'];
			$GrabarDespacho4->objDespacho->numeroDepacho = $data['numeroDepacho'];
			$GrabarDespacho4->objDespacho->unidadnegocio = $data['unidadnegocio'];
			$GrabarDespacho4->objDespacho->fechadespacho = $data['fechadespacho'];
			$GrabarDespacho4->objDespacho->cuentaremitente = $data['cuentaremitente'];
			$GrabarDespacho4->objDespacho->sederemitente =  $data['sederemitente'];
			$GrabarDespacho4->objDespacho->primernombreremitente = $data['primernombreremitente'];
			$GrabarDespacho4->objDespacho->segundonombreremitente = $data['segundonombreremitente'];
			$GrabarDespacho4->objDespacho->primerapellidoremitente = $data['primerapellidoremitente'];
			$GrabarDespacho4->objDespacho->segundoapellidoremitente =$data['segundoapellidoremitente'];
			$GrabarDespacho4->objDespacho->razonsocialremitente = $data['razonsocialremitente'];
			$GrabarDespacho4->objDespacho->naturalezaremitente = $data['naturalezaremitente'];
			$GrabarDespacho4->objDespacho->tipoidentificacionremitente = $data['tipoidentificacionremitente'];
			$GrabarDespacho4->objDespacho->identificacionremitente = $data['identificacionremitente'];
			$GrabarDespacho4->objDespacho->telefonoremitente = $data['telefonoremitente'];
			$GrabarDespacho4->objDespacho->direccionremitente = $data['direccionremitente'];
			$GrabarDespacho4->objDespacho->ciudadorigen = $data['ciudadorigen'];
			$GrabarDespacho4->objDespacho->tipoidentificaciondestinatario = $data['tipoidentificaciondestinatario'];
			$GrabarDespacho4->objDespacho->identificaciondestinatario = $data['identificaciondestinatario'];
			$GrabarDespacho4->objDespacho->sededestinatario = $data['sededestinatario'];
			$GrabarDespacho4->objDespacho->primernombredestinatario = $data['primernombredestinatario'];
			$GrabarDespacho4->objDespacho->segundonombredestinatario = $data['segundonombredestinatario'];
			$GrabarDespacho4->objDespacho->primerapellidodestinatario = $data['primerapellidodestinatario'];
			$GrabarDespacho4->objDespacho->segundoapellidodestinatario = $data['segundoapellidodestinatario'];
			$GrabarDespacho4->objDespacho->razonsocialdestinatario = $data['razonsocialdestinatario'];
			$GrabarDespacho4->objDespacho->naturalezadestinatario = $data['naturalezadestinatario'];
			$GrabarDespacho4->objDespacho->direcciondestinatario = $data['direcciondestinatario'];
			$GrabarDespacho4->objDespacho->telefonodestinatario = $data['telefonodestinatario'];
			$GrabarDespacho4->objDespacho->ciudaddestinatario = $data['ciudaddestinatario'];
			$GrabarDespacho4->objDespacho->barriodestinatario = $data['barriodestinatario'];
			$GrabarDespacho4->objDespacho->totalpeso = $data['totalpeso'];
			$GrabarDespacho4->objDespacho->totalpesovolumen = $data['totalpesovolumen'];
			$GrabarDespacho4->objDespacho->totalvalormercancia = $data['totalvalormercancia'];
			$GrabarDespacho4->objDespacho->formapago = $data['formapago'];
			$GrabarDespacho4->objDespacho->observaciones = $data['observaciones'];
			$GrabarDespacho4->objDespacho->llevabodega = $data['llevabodega'];
			$GrabarDespacho4->objDespacho->recogebodega = $data['recogebodega'];
			$GrabarDespacho4->objDespacho->centrocostos = $data['centrocostos'];
			$GrabarDespacho4->objDespacho->totalvalorproducto = $data['totalvalorproducto'];
			
			$GrabarDespacho4->objDespacho->unidad =  new \stdClass;
			$GrabarDespacho4->objDespacho->unidad->tipounidad = $data['tipounidad'];
			$GrabarDespacho4->objDespacho->unidad->tipoempaque = $data['tipoempaque'];
			$GrabarDespacho4->objDespacho->unidad->claseempaque = $data['claseempaque'];
			$GrabarDespacho4->objDespacho->unidad->dicecontener = $data['dicecontener'];
			$GrabarDespacho4->objDespacho->unidad->cantidadunidades = $data['cantidadunidades'];
			$GrabarDespacho4->objDespacho->unidad->kilosreales = $data['kilosreales'];
			$GrabarDespacho4->objDespacho->unidad->largo = $data['largo'];
			$GrabarDespacho4->objDespacho->unidad->alto = $data['alto'];
			$GrabarDespacho4->objDespacho->unidad->ancho = $data['ancho'];
			$GrabarDespacho4->objDespacho->unidad->pesovolumen = $data['pesovolumen'];
			$GrabarDespacho4->objDespacho->unidad->valormercancia = $data['valormercancia'];
			$GrabarDespacho4->objDespacho->unidad->codigobarras = $data['codigobarras'];
			$GrabarDespacho4->objDespacho->unidad->numerobolsa = $data['numerobolsa'];
			$GrabarDespacho4->objDespacho->unidad->referencias = $data['referencias'];
			
			
			$GrabarDespacho4->objDespacho->documentoreferencia =  new \stdClass;
			$GrabarDespacho4->objDespacho->documentoreferencia->tipodocumento = $data['tipodocumento'];
			$GrabarDespacho4->objDespacho->documentoreferencia->numerodocumento = $data['numerodocumento'];
			$GrabarDespacho4->objDespacho->documentoreferencia->fechadocumento = $data['fechadocumento'];
			$GrabarDespacho4->objDespacho->numeroReferenciaCliente = $data['numeroReferenciaCliente'];
			$GrabarDespacho4->objDespacho->fuente = $data['fuente'];
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
		
		public function tccRecogida($data, Request $request){
			$url = 'http://clientes.tcc.com.co/preservicios/wsrecogidas.asmx?wsdl';//guardar en una tabla de configuracion general
			
			$solicitarRecogida =   new \stdClass;
			
			$solicitarRecogida->clave='CLIENTETCC608W3A61CJ';//guardar en una tabla de configuracion general
			$solicitarRecogida->SolicitudRecogida =  new \stdClass;
			$solicitarRecogida->SolicitudRecogida->Solicitante =  new \stdClass;
			$solicitarRecogida->SolicitudRecogida->Solicitante->IDCliente = $data['IDCliente'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->IDSucursal = '';
			$solicitarRecogida->SolicitudRecogida->Solicitante->Cuenta = $data['CuentaSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->Telefono = $data['TelefonoSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->Ciudad = $data['CiudadSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->NombreCliente = $data['NombreClienteSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->PersonaSolicita = $data['PersonaSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->TipoDocumento = $data['TipoDocumentoPersonaSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Solicitante->Identificacion = $data['IdentificacionSolicitante'];
			$solicitarRecogida->SolicitudRecogida->Remitente =  new \stdClass;
			$solicitarRecogida->SolicitudRecogida->Remitente->IDCliente = $data['IDCliente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->IDSucursal = '';
			$solicitarRecogida->SolicitudRecogida->Remitente->Telefono =  $data['TelefonoRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->Ciudad =  $data['CiudadRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->NombreCliente =  $data['NombreClienteRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->PersonaContacto =  $data['PersonaContactoRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->Direccion =  $data['DireccionRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->DireccionInfoAdicional =  $data['DireccionInfoAdicionalRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->TipoDocumento =  $data['TipoDocumentoRemitente'];
			$solicitarRecogida->SolicitudRecogida->Remitente->Identificacion =  $data['IdentificacionRemitente'];
			$solicitarRecogida->SolicitudRecogida->Servicio =  new \stdClass;
			$solicitarRecogida->SolicitudRecogida->Servicio->Fecha = $data['FechaServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->TipoServicio = '';
			$solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = $data['HoraInicialServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = $data['HoraFinalServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->Unidades = $data['UnidadesServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->Peso = $data['PesoServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->Volumen = $data['VolumenServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->ValorMercancia = $data['ValorMercanciaServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->Observaciones = $data['ObservacionesServicio'];
			$solicitarRecogida->SolicitudRecogida->Servicio->CDPago = $data['CDPagoServicio'];
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
		
		//Fin TCC Operaciones
	}