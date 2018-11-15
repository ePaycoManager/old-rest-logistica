<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
//login
$router->post('/login',  ['uses' => 'LoginController@login']);
$router->get('/api/ciudades/agrupado','CiudadController@ciudadesAgrupado');
//resto de rutas
$router->group(['prefix' => 'api','middleware' => 'auth'], function () use ($router) {
    $router->post('/{operador}/{operacion}',  ['uses' => 'IndexController@index']);
	$router->get('/ciudades','CiudadController@index');
	$router->get('/departamentos','CiudadController@departamentos');


});
