<?php

namespace Maynagashev\SocialConnections;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;


class SocialConnectionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');


        $this->publishes([
            __DIR__.'/config/social-connections.php' => config_path('social-connections.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/app/Models/' => app_path()
        ], 'models');

        $this->publishes([
            __DIR__.'/resources/views/' => resource_path('views/vendor')
        ], 'views');


        $this->loadMigrationsFrom(__DIR__.'/migrations');

        //dump(__CLASS__."->boot()");

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/social-connections.php', 'social-connections'
        );
    }

}