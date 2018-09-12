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
	use App\OperacionesEpayco;
	use App\ConfigNegocio;
	use App\ConfiguracionUsuario;
	use App\Interfaces\CiudadInterface;
	use App\Interfaces\ConfigNegocioInterface;
	
	
	class OperacionesRepository implements OperacionesInterface {
		
		
		public function __construct(SoapInterface $soap, UserInterface $user_interface, CiudadInterface $ciudad_interface, ConfigNegocioInterface $config_negocio) {
			$this->soap = $soap;
			$this->user_interface = $user_interface;
			$this->ciudad_inteface = $ciudad_interface;
			$this->config_negocio = $config_negocio;
		}
		
		//Inicio TCC Operaciones
		
		public function tccCotizacion($data, Request $request){
			//guardar en una tabla de configuracion general
			$idUsuario = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
			$configUsuario = DB::table('config_usuario')->select('*')->where('id_usuario_rest_pagos',$idUsuario)->where('id',$data['id_configuracion'])->first();
			$configNegocio = $this->config_negocio->getConfig($this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' )));
			$url = $configNegocio->url_cotizar;
			
			$consultarliquidacion =   new \stdClass;
			$consultarliquidacion->Clave=$configNegocio->usuario_epayco;//guardar en una tabla de configuracion general
			$consultarliquidacion->Liquidacion =  new \stdClass;
			$consultarliquidacion->Liquidacion->tipoenvio='';//esto  no tiene doc ni valor en el xml de ejemplo
			$consultarliquidacion->Liquidacion->idciudadorigen = $configUsuario->ciudad;
			$consultarliquidacion->Liquidacion->idciudaddestino =$data['ciudad_destino'];
			$consultarliquidacion->Liquidacion->valormercancia =$data['valor_mercancia'];
			$consultarliquidacion->Liquidacion->boomerang = 0;//no aplica para cotizaciones pero el soap no funciona si no se envia
			$consultarliquidacion->Liquidacion->cuenta = 0; // aca va codigo de ePayco para tarifa - mensajeria siempre cotiza full - para paquetes si el dcto es pie factura no muestra valores del convenio
			$fecha = new \DateTime('now');
			$consultarliquidacion->Liquidacion->fecharemesa = isset($data['fecha_despacho'])?$data['fecha_despacho']:$fecha->format('m/d/Y');
			if($data['tipo_envio'] == '1' || $data['tipo_envio'] == '2'){
				$consultarliquidacion->Liquidacion->idunidadestrategicanegocio = $data['tipo_envio'];// 2 siempre superpone unidad 1 - -  mensajeria solo cotiza de a 1 unidad
			} else {
				return 'El tipo envio no tiene un parametro valido';
			}
			
			$consultarliquidacion->Liquidacion->unidades =  new \stdClass;
			$consultarliquidacion->Liquidacion->unidades->unidad =  new \stdClass;
			$consultarliquidacion->Liquidacion->unidades->unidad->numerounidades=$data['unidad']['cantidad_unidades'];//que es esto??? afecta valor pero al cambiar cantidad de unidades no cambia valor retornado
			$consultarliquidacion->Liquidacion->unidades->unidad->pesoreal=$data['unidad']['peso_real'];
			$consultarliquidacion->Liquidacion->unidades->unidad->pesovolumen=1;
			$consultarliquidacion->Liquidacion->unidades->unidad->alto=$data['unidad']['alto'];
			$consultarliquidacion->Liquidacion->unidades->unidad->largo=$data['unidad']['largo'];
			$consultarliquidacion->Liquidacion->unidades->unidad->ancho=$data['unidad']['ancho'];
			$consultarliquidacion->Liquidacion->unidades->unidad->timpoempaque='';
			
			$soapResponse = $this->soap->consumeSoap('',$consultarliquidacion,$url,'consultarliquidacion');
			
			
			
			if($soapResponse->consultarliquidacionResult->respuesta->codigo != "-1"){
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
				$liquidacionTCC->id_user = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
				$liquidacionTCC->save();
				$liquidacion =  TccLiquidacion::find($liquidacionTCC->id)->first();
				// aca logica de negocio
				
				$operacionEpayco = new OperacionesEpayco();
				$operacionEpayco->operacion = 'cotizaciones';
				$operacionEpayco->operador = 'tcc';
				$operacionEpayco->valor_operador = $liquidacionTCC->total_despacho;
				$valorEpayco = 0;
				if($configNegocio){
					$operacionEpayco->flete_operador = $soapResponse->consultarliquidacionResult->conceptos->Concepto[0]->valor;
					$operacionEpayco->flete_epayco = $soapResponse->consultarliquidacionResult->conceptos->Concepto[0]->valor + (($soapResponse->consultarliquidacionResult->conceptos->Concepto[0]->valor * $configNegocio->porcentaje_cotizar)/100);
					$operacionEpayco->manejo_operador = $soapResponse->consultarliquidacionResult->conceptos->Concepto[1]->valor;
					$operacionEpayco->manejo_epayco = $soapResponse->consultarliquidacionResult->conceptos->Concepto[1]->valor + (($soapResponse->consultarliquidacionResult->conceptos->Concepto[1]->valor * $configNegocio->porcentaje_cotizar)/100);
					
					$valorEpayco = $operacionEpayco->flete_epayco+$operacionEpayco->manejo_epayco;
					
				}
				$operacionEpayco->valor_payco = $valorEpayco;
				$operacionEpayco->id_operacion_operador = $liquidacionTCC->id;
				$operacionEpayco->id_cliente= $liquidacionTCC->id_user;
				$operacionEpayco->save();
				$response = array(
					'valor'=>$operacionEpayco->valor_payco ,
					'operador'=>$operacionEpayco->operador ,
					'flete'=>$operacionEpayco->flete_epayco,
					'manejo'=>$operacionEpayco->manejo_epayco,
					'id'=>$operacionEpayco->id,
					'fecha_registro'=>$operacionEpayco->created_at->format('Y-m-d'),
					);
				
				//fin logica de negocio
				
				
				return $response;
			} else {
				return $soapResponse;
			}
		}
		
		public function tccGuia($data, Request $request){
			$idUsuario = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
			$configUsuario = DB::table('config_usuario')->select('*')->where('id_usuario_rest_pagos',$idUsuario)->where('id',$data['id_configuracion'])->first();
			$configNegocio = $this->config_negocio->getConfig($request->header( 'php-auth-user' ));
			$url = $configNegocio->url_guia;
			
			$dataCotizacion =array(
					'ciudad_destino' => $data['ciudad_destinatario'],
					'valor_mercancia' => $data['valor_mercancia'],
					'fecha_despacho' => $data['fecha_despacho'],
					'tipo_envio'=>$data['tipo_envio'],
					'unidad'=>array('peso_real'=>$data['kilos_reales'],
						'cantidad_unidades'=>$data['cantidad_unidades'],
			            'alto'=>0,
			            'largo'=>0,
			            'ancho'=>0
					),
				'id_configuracion'=>$data['id_configuracion']
				
			);
			
			
			
			$GrabarDespacho4 =   new \stdClass;
			$GrabarDespacho4->objDespacho =  new \stdClass;
			$GrabarDespacho4->objDespacho->clave=$configNegocio->usuario_epayco;//guardar en una tabla de configuracion general
			//Obligatorios
			try{
				$cotizacion = $this->tccCotizacion($dataCotizacion, $request);
				if($data['tipo_envio'] == '1' || $data['tipo_envio'] == '2'){
					$GrabarDespacho4->objDespacho->unidadnegocio = $data['tipo_envio'];
					if($data['tipo_envio'] == '1'){
						$tipoUnidad = 'TIPO_UND_PAQ';
					} else {
						$tipoUnidad = 'TIPO_UND_DOCB';
					}
				}else {
					return 'El tipo envio no tiene un parametro valido';
				}
				$GrabarDespacho4->objDespacho->fechadespacho = $data['fecha_despacho'];
				$GrabarDespacho4->objDespacho->cuentaremitente = '1114100';//Cuenta asignada al aliado de tcc -> ojo tab de conf cliente gateway o agregador para servicio logistico
				$GrabarDespacho4->objDespacho->razonsocialremitente = $configUsuario->nombre;
				$GrabarDespacho4->objDespacho->tipoidentificacionremitente = $configUsuario->tipo_documento;
				$GrabarDespacho4->objDespacho->ciudadorigen = $configUsuario->ciudad;
				$GrabarDespacho4->objDespacho->naturalezaremitente = $configUsuario->tipo_persona;
				$GrabarDespacho4->objDespacho->identificacionremitente = $configUsuario->documento;
				$GrabarDespacho4->objDespacho->telefonoremitente = $configUsuario->telefono;
				$GrabarDespacho4->objDespacho->direccionremitente = $configUsuario->direccion;
				
				$GrabarDespacho4->objDespacho->tipoidentificaciondestinatario = $data['tipo_identificacion_destinatario'];
				$GrabarDespacho4->objDespacho->identificaciondestinatario = $data['identificacion_destinatario'];
				$GrabarDespacho4->objDespacho->razonsocialdestinatario = $data['razon_social_destinatario'];
				$GrabarDespacho4->objDespacho->direcciondestinatario = $data['direccion_destinatario'];
				$GrabarDespacho4->objDespacho->ciudaddestinatario = $data['ciudad_destinatario'];
				$GrabarDespacho4->objDespacho->unidad =  new \stdClass;
				$GrabarDespacho4->objDespacho->unidad->tipounidad = $tipoUnidad;//preguntar por este parametro TIPO_UND_DOCB
				$GrabarDespacho4->objDespacho->unidad->cantidadunidades = $data['cantidad_unidades'];
				$GrabarDespacho4->objDespacho->unidad->kilosreales = $data['kilos_reales'];
				$GrabarDespacho4->objDespacho->unidad->pesovolumen = 0;
				$GrabarDespacho4->objDespacho->unidad->valormercancia = $data['valor_mercancia'];
				$GrabarDespacho4->objDespacho->primernombredestinatario = $data['primer_nombre_destinatario'];
				$GrabarDespacho4->objDespacho->segundonombredestinatario = $data['segundo_nombre_destinatario'];
				$GrabarDespacho4->objDespacho->primerapellidodestinatario = $data['primer_apellido_destinatario'];
				$GrabarDespacho4->objDespacho->segundoapellidodestinatario = $data['segundo_apellido_destinatario'];
				$GrabarDespacho4->objDespacho->telefonodestinatario = $data['telefono_destinatario'];
				$GrabarDespacho4->objDespacho->observaciones = $data['observaciones'];
				$GrabarDespacho4->objDespacho->documentoreferencia =  new \stdClass;
				
				$GrabarDespacho4->objDespacho->generarDocumentos = true;
				$GrabarDespacho4->respuesta = '';//no definido en doc
				
				$soapResponse = $this->soap->consumeSoap('',$GrabarDespacho4,$url,'GrabarDespacho4');
				
				$remesaTCC = new TccRemesa();
				$date = new \DateTime('now');
				if($soapResponse->respuesta != "-1") {
					$remesaTCC->fecha_lote                 = $date;
					$remesaTCC->numero_remesa              = $soapResponse->remesa;
					$remesaTCC->unidad_negocio             = $GrabarDespacho4->objDespacho->unidadnegocio;
					$remesaTCC->fecha_despacho             = $GrabarDespacho4->objDespacho->fechadespacho;
					$remesaTCC->cuenta_remitente           = $GrabarDespacho4->objDespacho->cuentaremitente;
				
					$remesaTCC->razon_social_remitente     = $GrabarDespacho4->objDespacho->razonsocialremitente;
					$remesaTCC->naturaleza_remitente = $GrabarDespacho4->objDespacho->naturalezaremitente;
					$remesaTCC->tipo_identificacion_remitente = $GrabarDespacho4->objDespacho->tipoidentificacionremitente;
					$remesaTCC->telefono_remitente = $GrabarDespacho4->objDespacho->telefonoremitente;
					$remesaTCC->direccion_remitente = $GrabarDespacho4->objDespacho->direccionremitente;
					$remesaTCC->ciudad_origen                    = $GrabarDespacho4->objDespacho->ciudadorigen;
					$remesaTCC->tipo_identificacion_destinatario = $GrabarDespacho4->objDespacho->tipoidentificaciondestinatario;
					$remesaTCC->identificacion_destinatario = $GrabarDespacho4->objDespacho->identificaciondestinatario;
					$remesaTCC->primer_nombre_destinatario = $GrabarDespacho4->objDespacho->primernombredestinatario;
					$remesaTCC->segundo_nombre_destinatario = $GrabarDespacho4->objDespacho->segundonombredestinatario;
					$remesaTCC->primer_apellido_destinatario = $GrabarDespacho4->objDespacho->primerapellidodestinatario;
					$remesaTCC->segundo_apellido_destinatario = $GrabarDespacho4->objDespacho->segundoapellidodestinatario;
					$remesaTCC->razon_social_destinatario = $GrabarDespacho4->objDespacho->razonsocialdestinatario;
					$remesaTCC->direccion_destinatario = $GrabarDespacho4->objDespacho->direcciondestinatario;
					$remesaTCC->ciudad_destinatario    = $GrabarDespacho4->objDespacho->ciudaddestinatario;
					$remesaTCC->url_relacion_envio = $soapResponse->URLRelacionEnvio;
					$remesaTCC->url_rotulos        = $soapResponse->URLRotulos;
					$remesaTCC->img_relacion_envio = $soapResponse->IMGRelacionEnvio;
					$remesaTCC->img_rotulos        = $soapResponse->IMGRotulos;
					$remesaTCC->mensaje_tcc        = $soapResponse->mensaje;
					$remesaTCC->id_user            = $this->user_interface->getIdUserRestPagos( $request->get( 'api_token' ) );
					$remesaTCC->id_cotizacion = $cotizacion['id'];
					$remesaTCC->total_peso =$data['kilos_reales'];
					$remesaTCC->total_valor_mercancia = $data['valor_mercancia'];
					$remesaTCC->save();
					$remesa                                 = TccRemesa::find( $remesaTCC->id );
					$operacionEpayco                        = new OperacionesEpayco();
					$operacionEpayco->operacion             = 'remesas';
					$operacionEpayco->operador              = 'tcc';
					$operacionEpayco->valor_operador        = '';
					$operacionEpayco->valor_payco           = $cotizacion['valor'];
					$operacionEpayco->id_operacion_operador = $remesa->id;
					$operacionEpayco->id_cliente            = $idUsuario;
					
					
					if($configUsuario->recogida_automatica){
						$fechaRecogida = $date->modify('+1 day');
						$ciudadRecogida = $this->ciudad_inteface->codigoTcc($configUsuario->ciudad);
						if(!$ciudadRecogida){
							return 'No se posee actualmente servicio de recogida en esa ciudad, por favor desactive el servicio de recogida automatica en la configuracion';
						}
						if($configUsuario->segmento == 'M'){
							$horaInicial = '09:00:00';
							$horaFinal = '12:00:00';
						} else {
							$horaInicial = '14:00:00';
							$horaFinal = '17:00:00';
						}
						
						$dataRecogida = array(
							'id_configuracion'=>$data['id_configuracion'],
							'id_cliente' => '',
							'ciudad' => $configUsuario->ciudad,
							'nombre_cliente_solicitante' =>$configUsuario->nombre,
							'persona_solicitante' => $configUsuario->nombre,
							'tipo_documento_persona_solicitante'=>$configUsuario->tipo_documento,
							'identificacion_solicitante'=>$configUsuario->tipo_documento == 'CC' ? '1' :'2',
							'telefono_solicitante'=>$configUsuario->telefono,
							'telefono_remitente'=>$configUsuario->telefono,
							'ciudad_remitente' => $ciudadRecogida,
							'ciudad_solicitante' => $ciudadRecogida,
							'nombre_cliente_remitente' =>$configUsuario->nombre,
							'persona_contacto_remitente' =>$configUsuario->nombre,
							'direccion_remitente' =>$configUsuario->direccion,
							'direccion_info_adicional_remitente' =>$configUsuario->direccion,
							'tipo_documento_remitente'=>$configUsuario->tipo_documento == 'CC' ? '1' :'2',
							'identificacion_remitente'=>$configUsuario->documento,
							'fecha_servicio' => $fechaRecogida->format('Y-m-d'),
							'hora_inicial_servicio'=>$horaInicial,
							'hora_final_servicio'=>$horaFinal,
							'peso_servicio'=>$GrabarDespacho4->objDespacho->unidad->kilosreales,
							'volumen_servicio'=>0,
							'valor_mercancia_servicio'=>$GrabarDespacho4->objDespacho->unidad->valormercancia,
							'observaciones_servicio'=>$data['observaciones'],
							'viene_de_remesa'=>true
						
						);
						$operacionEpayco->recogida_automatica = true;
						$recogida = $this->tccRecogida($dataRecogida,$request);
						$operacionEpayco->recogido = $recogida->id_operacion;
						$operacionEpayco->save();
					
					}else{
						$operacionEpayco->recogida_automatica = false;
						$operacionEpayco->recogido = false;
						$operacionEpayco->save();
						$recogida = 'No tiene activo el servicio de recogida automatica';
					}
					
				$response = array(
					'guia'=>$soapResponse->remesa,
					'url_relacion_envio'=>$soapResponse->URLRelacionEnvio,
					'url_rotulos'=>$soapResponse->URLRotulos,
					'respuesta'=>$soapResponse->respuesta,
					'mensaje'=>$soapResponse->mensaje,
					'id_guia' => $operacionEpayco->id,
					'valor' => $cotizacion['valor'],
					'recogida'=>$recogida
				);
				
				return $response;
			}
				else {
					return $soapResponse;
				}
			} catch (\Exception $e){
				
				return $e;
				
			}

		}
		
		public function tccRecogida($data, Request $request){
			$idUsuario = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
			$configUsuario = DB::table('config_usuario')->select('*')->where('id_usuario_rest_pagos',$idUsuario)->where('id',$data['id_configuracion'])->first();
			$configNegocio = $this->config_negocio->getConfig($request->header( 'php-auth-user' ));
			$url = $configNegocio->url_recogida;
			
			if(isset($data['viene_de_remesa']) || isset($data['id_guia'])){
				if(isset($data['viene_de_remesa'])){
					$solicitarRecogida =   new \stdClass;
					
					$solicitarRecogida->clave=$configNegocio->usuario_epayco;//guardar en una tabla de configuracion general
					$solicitarRecogida->SolicitudRecogida =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Solicitante =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Solicitante->IDCliente = isset($data['id_cliente'])?$data['id_cliente']:'';
					$solicitarRecogida->SolicitudRecogida->Solicitante->IDSucursal = '';
					$solicitarRecogida->SolicitudRecogida->Solicitante->Cuenta ='1114100';
					$solicitarRecogida->SolicitudRecogida->Solicitante->Telefono = $data['telefono_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Solicitante->Ciudad = $data['ciudad_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Solicitante->NombreCliente = $data['nombre_cliente_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Solicitante->PersonaSolicita = $data['persona_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Solicitante->TipoDocumento = $data['tipo_documento_persona_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Solicitante->Identificacion = $data['identificacion_solicitante'];
					$solicitarRecogida->SolicitudRecogida->Remitente =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Remitente->IDCliente = '';
					$solicitarRecogida->SolicitudRecogida->Remitente->IDSucursal = '';
					$solicitarRecogida->SolicitudRecogida->Remitente->Telefono =  $data['telefono_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->Ciudad =  $data['ciudad_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->NombreCliente =  $data['nombre_cliente_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->PersonaContacto =  $data['persona_contacto_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->Direccion =  $data['direccion_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->DireccionInfoAdicional =  $data['direccion_info_adicional_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->TipoDocumento =  $data['tipo_documento_remitente'];
					$solicitarRecogida->SolicitudRecogida->Remitente->Identificacion =  $data['identificacion_remitente'];
					$solicitarRecogida->SolicitudRecogida->Servicio =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Servicio->Fecha = $data['fecha_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->Fecha = '2018-09-15';
					$solicitarRecogida->SolicitudRecogida->Servicio->TipoServicio = '';
					$solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = $data['hora_inicial_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = '09:00:00';
					$solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = $data['hora_final_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = '12:00:00';
					$solicitarRecogida->SolicitudRecogida->Servicio->Unidades = 1;
					$solicitarRecogida->SolicitudRecogida->Servicio->Peso = $data['peso_servicio'];
					$solicitarRecogida->SolicitudRecogida->Servicio->Volumen = $data['volumen_servicio'];
					$solicitarRecogida->SolicitudRecogida->Servicio->ValorMercancia = $data['valor_mercancia_servicio'];
					$solicitarRecogida->SolicitudRecogida->Servicio->Observaciones = $data['observaciones_servicio'];
					$solicitarRecogida->SolicitudRecogida->Servicio->CDPago = 1;
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
						
						
						$response = array(
							'id'=>$recogidaTCC->id_tcc,
							'fecha'=>$recogidaTCC->fecha_recogida,
							'hora_incial'=>$data['hora_inicial_servicio'],
							'hora_final'=>$data['hora_final_servicio'],
							'id_operacion'=>$recogidaTCC->id
						);
						
						return $response;
					}
					else {
						return $soapResponse;
					}
				} else if(isset($data['id_guia']) ){
					//aca logica para consultar la remesa
					$opPayco = DB::table('operaciones_epayco')->select('*')->where('id',$data['id_guia'])->first();
					if($opPayco){
						$remesa = DB::table('tcc_remesas')->select('*')->where('id',$opPayco->id_operacion_operador)->first();
					} else{
						return 'No se ha encontrado el numero de guia enviado';
					}
					
					$ciudadRecogida = $this->ciudad_inteface->codigoTcc($configUsuario->ciudad);
					
					$solicitarRecogida =   new \stdClass;
					
					$solicitarRecogida->clave=$configNegocio->usuario_epayco;//guardar en una tabla de configuracion general
					$solicitarRecogida->SolicitudRecogida =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Solicitante =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Solicitante->IDCliente = isset($data['id_cliente'])?$data['id_cliente']:'';
					$solicitarRecogida->SolicitudRecogida->Solicitante->IDSucursal = '';
					$solicitarRecogida->SolicitudRecogida->Solicitante->Cuenta ='1114100';
					$solicitarRecogida->SolicitudRecogida->Solicitante->Telefono = $configUsuario->telefono;
					$solicitarRecogida->SolicitudRecogida->Solicitante->Ciudad = $ciudadRecogida;
					$solicitarRecogida->SolicitudRecogida->Solicitante->NombreCliente = $configUsuario->nombre;
					$solicitarRecogida->SolicitudRecogida->Solicitante->PersonaSolicita = $configUsuario->nombre;
					$solicitarRecogida->SolicitudRecogida->Solicitante->TipoDocumento = $configUsuario->tipo_documento == 'CC' ? '1' :'2';
					$solicitarRecogida->SolicitudRecogida->Solicitante->Identificacion = $configUsuario->documento;
					$solicitarRecogida->SolicitudRecogida->Remitente =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Remitente->IDCliente = '';
					$solicitarRecogida->SolicitudRecogida->Remitente->IDSucursal = '';
					$solicitarRecogida->SolicitudRecogida->Remitente->Telefono =  $configUsuario->telefono;					$solicitarRecogida->SolicitudRecogida->Remitente->Ciudad =  $ciudadRecogida;
					$solicitarRecogida->SolicitudRecogida->Remitente->NombreCliente =  $configUsuario->nombre;
					$solicitarRecogida->SolicitudRecogida->Remitente->PersonaContacto =$configUsuario->nombre;
					$solicitarRecogida->SolicitudRecogida->Remitente->Direccion =  $configUsuario->direccion;
					$solicitarRecogida->SolicitudRecogida->Remitente->DireccionInfoAdicional =  $configUsuario->direccion;
					$solicitarRecogida->SolicitudRecogida->Remitente->TipoDocumento = $configUsuario->tipo_documento == 'CC' ? '1' :'2';
					$solicitarRecogida->SolicitudRecogida->Remitente->Identificacion =  $configUsuario->documento;
					$solicitarRecogida->SolicitudRecogida->Servicio =  new \stdClass;
					$solicitarRecogida->SolicitudRecogida->Servicio->Fecha = $data['fecha_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->Fecha = '2018-09-15';
					$solicitarRecogida->SolicitudRecogida->Servicio->TipoServicio = '';
					$solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = $data['hora_inicial_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->HoraInicial = '09:00:00';
					$solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = $data['hora_final_servicio'];
					//$solicitarRecogida->SolicitudRecogida->Servicio->HoraFinal = '12:00:00';
					$solicitarRecogida->SolicitudRecogida->Servicio->Unidades = 1;
					$solicitarRecogida->SolicitudRecogida->Servicio->Peso = $remesa->total_peso;
					$solicitarRecogida->SolicitudRecogida->Servicio->Volumen =0;
					$solicitarRecogida->SolicitudRecogida->Servicio->ValorMercancia = $remesa->total_valor_mercancia;
					$solicitarRecogida->SolicitudRecogida->Servicio->Observaciones = $data['observaciones_servicio'];
					$solicitarRecogida->SolicitudRecogida->Servicio->CDPago = 1;
					$solicitarRecogida->recogida = '';
					$solicitarRecogida->respuesta = '';
					$solicitarRecogida->mensaje = 'r';
					
					$soapResponse = $this->soap->consumeSoap('',$solicitarRecogida,$url,'SolicitarRecogida');
					
					$recogidaTCC = new TccRecogida();
					$date = new \DateTime('now');
					
					if($soapResponse->recogida != -1 && $opPayco->recogido == null) {
						
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
						
						$response = array(
							'id'=>$recogidaTCC->id_tcc,
							'fecha'=>$recogidaTCC->fecha_recogida,
							'hora_incial'=>$data['hora_inicial_servicio'],
							'hora_final'=>$data['hora_final_servicio'],
							'id_operacion'=>$recogidaTCC->id
						);
						
						
						
						return $response;
					}
					else {
						$recogida = DB::table('tcc_recogidas')->select('*')->where('id',$opPayco->recogido)->first();
						$response = array(
							'message'=>'No se puede generar recogida para esta guia porque ya hay una solicitud.',
							'recogida'=>$recogida
						);
						return $response;
					}
				} else {
					return 'No se puede procesar su peticion, por favor revise los parametros';
				}
			
			}else {
				return 'No se puede procesar su peticion, por favor revise los parametros';
			}
			
			
		}
		
		public function tccRecogidaCron($data, Request $request){
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
				
				return true;
			}
			else {
				return false;
			}
		}
		
		//Fin TCC Operaciones
		
		// Operaciones generales
		
		public function listaGuias($data, Request $request ) {
			
			$user = $this->user_interface->getIdUserRestPagos($request->get('api_token'));
			$remesas = DB::table('operaciones_epayco')
				->select('operaciones_epayco.*','tcc_remesas.*')
				->where('operaciones_epayco.operacion', '=','remesas')
				->where('operaciones_epayco.id_cliente','=', "{$user}")
				->join('tcc_remesas','operaciones_epayco.id_operacion_operador','=','tcc_remesas.id')
				->get();
			
			return $remesas;
			
		}
		
		//fin operaciones generales
		
		//orperaciones Configuracion
		
		public function configuracionUsuario($data, Request $request){
			$idUsuarioRest = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
			$config = DB::table('config_usuario')->select('*')->where('id_usuario_rest_pagos',$idUsuarioRest)->get();
			
			
			if($config){
				return $config;
			} else{
				return 'No se encontro configuracion';
			}
		}
		
		public function configuracionEditar( $data, Request $request ) {
			
			
			try{
				
				$idUsuarioRest = $this->user_interface->getIdUserRestPagos($request->header( 'php-auth-user' ));
				$config = ConfiguracionUsuario::find(['id_usuario_rest_pagos'=>$idUsuarioRest,'id'=>$data['id_configuracion']])->get()->first();
				if($config){
					if(isset($data['direccion']) && $data['direccion'] != '' ) {
						$config->direccion = $data['direccion'];
					}
					if(isset($data['telefono']) && $data['telefono'] != '' ){
						$config->telefono=$data['telefono'];
					}
					if(isset($data['ciudad']) && $data['ciudad'] != '' ){
						$validateCiudad = $this->ciudad_inteface->validarCiudad($data['ciudad']);
						if($validateCiudad){
							$config->ciudad=$data['ciudad'];
						} else {
							return 'El codigo ingresado para la ciudad no existe';
						}
						
					}
					if(isset($data['nombre']) && $data['nombre'] != '' ){
						$config->nombre=$data['nombre'];
					}
					if(isset($data['documento']) && $data['documento'] != '' ){
						$config->documento=$data['documento'];
					}
					if(isset($data['tipo_documento']) && $data['tipo_documento'] != '' ){
						if($data['tipo_documento'] == 'CC' || $data['tipo_documento'] == 'NIT'){
							$config->tipo_documento=$data['tipo_documento'];
						} else {
							return 'El tipo documento no es valido';
						}
						
					}
					if(isset($data['tipo_persona']) && $data['tipo_persona'] != '' ){
						if($data['tipo_persona'] == 'N' || $data['tipo_persona'] == 'J'){
							$config->tipo_documento=$data['tipo_persona'];
						} else {
							return 'El tipo persona no es valido';
						}
						
					}
					if(isset($data['recogida_automatica']) && $data['recogida_automatica'] != '' ){
						if($data['recogida_automatica'] == 'true' || $data['recogida_automatica'] == 'false'){
							$config->tipo_documento=$data['recogida_automatica'];
						} else {
							return 'El parametro dado para recogida automatica no es valido';
						}
						
					}
					
					if(isset($data['dias']) && $data['dias'] != '' &&  $data['recogida_automatica'] == "true"){
						if($data['dias'] == 'T' || strlen($data['dias'] == 13)){
							$config->dias=$data['dias'];
						}
					}
					
					if(isset($data['segmento']) && $data['segmento'] != '' &&  $data['recogida_automatica'] == "true"){
						if($data['segmento'] == 'M' || $data['segmento'] == 'T'){
							$config->segmento=$data['segmento'];
						}
					}
					
					
					
					$config->save();
					return 'Configuracion guardada satisfactoriamente';
					
					
				} else {
					$newConfig =  new ConfiguracionUsuario();
					if(isset($data['direccion']) && $data['direccion'] != '' ) {
						$newConfig->direccion = $data['direccion'];
					} else {
						return 'La direccion es un parametro obligatorio';
					}
					if(isset($data['telefono']) && $data['telefono'] != '' ){
						$newConfig->telefono=$data['telefono'];
					} else {
						return 'El telefono es un parametro obligatorio';
					}
					if(isset($data['ciudad']) && $data['ciudad'] != '' ){
						$validateCiudad = $this->ciudad_inteface->validarCiudad($data['ciudad']);
						if($validateCiudad){
							$newConfig->ciudad=$data['ciudad'];
						} else {
							return 'El codigo ingresado para la ciudad no existe';
						}
						
					} else {
						return 'La  ciudad es un parametro obligatorio';
					}
					if(isset($data['nombre']) && $data['nombre'] != '' ){
						$newConfig->nombre=$data['nombre'];
					} else {
						return 'El nombre ciudad es un parametro obligatorio';
					}
					if(isset($data['documento']) && $data['documento'] != '' ){
						$newConfig->documento=$data['documento'];
					} else {
						return 'El documento es un parametro obligatorio';
					}
					if(isset($data['tipo_documento']) && $data['tipo_documento'] != '' ){
						if($data['tipo_documento'] == 'CC' || $data['tipo_documento'] == 'NIT'){
							$newConfig->tipo_documento=$data['tipo_documento'];
						} else {
							return 'El tipo documento no es valido';
						}
						
					} else {
						return 'El tipo_documento es un parametro obligatorio';
					}
					if(isset($data['tipo_persona']) && $data['tipo_persona'] != '' ){
						if($data['tipo_persona'] == 'N' || $data['tipo_persona'] == 'J'){
							$newConfig->tipo_documento=$data['tipo_persona'];
						} else {
							return 'El tipo persona no es valido';
						}
						
					} else {
						return 'El tipo persona es un parametro obligatorio';
					}
					if(isset($data['recogida_automatica']) && $data['recogida_automatica'] != '' ){
						if($data['recogida_automatica'] == 'true' || $data['recogida_automatica'] == 'false'){
							$newConfig->tipo_documento=$data['recogida_automatica'];
						} else {
							return 'El parametro dado para recogida automatica no es valido';
						}
						
					} else {
						return 'El parametro recogida automatica es obligatorio';
					}
					
					if(isset($data['dias']) && $data['dias'] != '' &&  $data['recogida_automatica'] == "true"){
						if($data['dias'] == 'T' || strlen($data['dias'] == 13)){
							$newConfig->dias=$data['dias'];
						} else {
							return 'El parametro dado para dias no es valido';
						}
					}
					
					if(isset($data['segmento']) && $data['segmento'] != '' &&  $data['recogida_automatica'] == "true"){
						if($data['segmento'] == 'M' || $data['segmento'] == 'T'){
							$newConfig->segmento=$data['segmento'];
						} else {
							return 'El parametro dado para segmento no es valido';
						}
					}
					$newConfig->save();
					return 'Configuracion guardada satisfactoriamente';
				}
				
			} catch (\Exception $e){
				return $e;
			}
			
			
			
		}
		
		//fin operaciones Configuracion
	}
	
	
	
	// request minimos solicitados
	//{
	//	   "unidadnegocio":"2",
	//	   "fechadespacho":"2018-09-04",
	//	   "cuentaremitente":"1114100",
	//	   "razonsocialremitente":"",
	//	   "tipoidentificacionremitente":"NIT",
	//	   "primernombreremitente":"CLIENTE GENERICO DE TCC",
	//	   "segundonombreremitente":"",
	//	   "primerapellidoremitente":"",
	//	   "segundoapellidoremitente":"",
	//	   "ciudadorigen":"05001000",
	//	   "razonsocialdestinatario":"",
	//	   "tipoidentificaciondestinatario":"CC",
	//	   "primernombredestinatario":"PRUEBA DE SERVICIO WEB",
	//	   "segundonombredestinatario":"",
	//	   "primerapellidodestinatario":"",
	//	   "segundoapellidodestinatario":"",
	//	   "direcciondestinatario":"CRA 1 362N-231",
	//	   "ciudaddestinatario":"05001000",
	//	   "tipounidad":"TIPO_UND_DOCB",
	//	   "cantidadunidades":"1",
	//	   "pesovolumen":"0",
	//	   "valormercancia":"0",
	//	   "kilosreales":"2"
	//
	//}
	
	
	//request complete remesa


//"codigolote":"",
//	   "fechahoralote":"2018-04-04",
//	   "numeroremesa":"",
//	   "numeroDepacho":"",
//	   "unidadnegocio":"1",
//	   "fechadespacho":"2018-09-04",
//	   "cuentaremitente":"1114100",
//	   "sederemitente":"",
//	   "primernombreremitente":"CLIENTE GENERICO DE TCC",
//	   "segundonombreremitente":"",
//	   "primerapellidoremitente":"",
//	   "segundoapellidoremitente":"",
//	   "razonsocialremitente":"",
//	   "naturalezaremitente":"J",
//	   "tipoidentificacionremitente":"NIT",
//	   "identificacionremitente":"89090031",
//	   "telefonoremitente":"1234567",
//	   "direccionremitente":"CLL FALSA 123",
//	   "ciudadorigen":"05001000",
//	   "tipoidentificaciondestinatario":"CC",
//	   "identificaciondestinatario":"1061529633",
//	   "sededestinatario":"",
//	   "primernombredestinatario":"PRUEBA DE SERVICIO WEB",
//	   "segundonombredestinatario":"",
//	   "primerapellidodestinatario":"",
//	   "segundoapellidodestinatario":"",
//	   "razonsocialdestinatario":"",
//	   "naturalezadestinatario":"N",
//	   "direcciondestinatario":"CRA 1 362N-231",
//	   "telefonodestinatario":"6852828",
//	   "ciudaddestinatario":"05001000",
//	   "barriodestinatario":"",
//	   "totalpeso":"0",
//	   "totalpesovolumen":"0",
//	   "totalvalormercancia":"0",
//	   "formapago":"",
//	   "observaciones":"ESTO ES UNA PRUEBA Y SE DEBE ANULAR",
//	   "llevabodega":"",
//	   "recogebodega":"",
//	   "centrocostos":"",
//	   "totalvalorproducto":"",
//	   "tipounidad":"TIPO_UND_DOCB",
//	   "tipoempaque":"",
//	   "claseempaque":"CLEM_SOBRE",
//	   "dicecontener":"BOOMERANG",
//	   "cantidadunidades":"1",
//	   "kilosreales":"0",
//	   "largo":"0",
//	   "alto":"0",
//	   "ancho":"0",
//	   "pesovolumen":"0",
//	   "valormercancia":"0",
//	   "codigobarras":"",
//	   "numerobolsa":"",
//	   "referencias":"",
//	   "tipodocumento":"FA",
//	   "numerodocumento":"F12-1234569",
//	   "fechadocumento":"2016-06-1",
//	   "numeroReferenciaCliente":"",
//	   "fuente":""


//o8usw9eyem