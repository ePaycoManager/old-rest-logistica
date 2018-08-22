<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
	    $this->app->bind('App\Interfaces\OperacionesInterface','App\Repositories\OperacionesRepository');
        $this->app->bind('App\Interfaces\CiudadInterface','App\Repositories\CiudadRepository');
	    $this->app->bind('App\Interfaces\SoapInterface','App\Services\SoapConsumeService');
	    $this->app->bind('App\Interfaces\UserInterface','App\Repositories\UserRepository');
	    
	    
    }
}
