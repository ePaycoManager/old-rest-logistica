<?php

use Illuminate\Database\Seeder;

class ConfNegocioTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run() {
		
		DB::table( 'config_negocio' )->insert([
			'usuario_epayco'=>'CLIENTETCC608W3A61CJ',
			'url_cotizar'=>'http://clientes.tcc.com.co/preservicios/liquidacionacuerdos.asmx?wsdl',
			'url_guia'=>'http://clientes.tcc.com.co/preservicios/wsdespachos.asmx?wsdl',
			'url_recogida'=>'http://clientes.tcc.com.co/preservicios/wsrecogidas.asmx?wsdl',
			'porcentaje_recogida'=>'15',
			'porcentaje_guia'=>'15',
			'porcentaje_cotizar'=>'15',
			'operador'=>'tcc',
			'activo'=>true
		]);
	}
}
