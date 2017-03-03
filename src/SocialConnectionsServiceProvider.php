<?php

namespace Maynagashev\SocialConnections;

use Illuminate\Support\ServiceProvider;

class SocialConnectionsServiceProvider extends ServiceProvider
{

    public $packageName = 'social-connections';

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        //$this->app->setLocale('en');

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        $this->loadTranslationsFrom(__DIR__.'/resources/lang', $this->packageName);

        $this->loadMigrationsFrom(__DIR__.'/migrations');


        // Publish assets

        $this->publishes([
            __DIR__."/config/{$this->packageName}.php" => config_path("{$this->packageName}.php")
        ], 'config');

        $this->publishes([
            __DIR__.'/app/Models/' => app_path()
        ], 'models');

        $this->publishes([
            __DIR__.'/resources/views/' => resource_path('views/vendor')
        ], 'views');

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