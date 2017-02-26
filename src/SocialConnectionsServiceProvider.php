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
    public function boot(): void
    {

        $this->setupRoutes($this->app->router);
        dump("my provider boot");

    }

    /**
     * Register the application services.
     */
    public function register(): void
    {

    }


    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $controllersNamespace = 'Maynagashev\\SocialConnections\\Http\\Controllers';

        // guests and other
        $router->group(['namespace' => $controllersNamespace], function($router)
        {
            // Laravel Socialite routes
            Route::get('social/redirect/{provider}', 'SocialController@getSocialRedirect')->name('social.redirect');
            Route::get('social/handle/{provider}', 'SocialController@getSocialHandle')->name('social.handle');

            // Ask for email address when connecting to providers, that has no email info.
            Route::get('social/email', 'SocialController@getEmail')->name('social.email');
            Route::post('social/email', 'SocialController@getEmail')->name('social.email.post');

        });

        // authenticated only
        Route::group(['middleware' => 'auth', 'namespace' => $controllersNamespace], function ($router)
        {
            Route::get('social/remove/{provider}',  'SocialController@getRemove')->name('social.remove');
            Route::get('social/add/{provider}',  'SocialController@getAdd')->name('social.add');

        });
    }

}