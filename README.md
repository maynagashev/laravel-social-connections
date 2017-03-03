# laravel-social-connections
Laravel package, adds social connections management, users can connect
multiple social networks to a laravel account.

Status: *in development*.

### Capabilities
- extended handler for oauth providers (with asking for email, if email address not returned from provider)
- resource controller with views for showing current connections in admin
- view component for displaying social network buttons in login/signup form


###Installation

Add new record to the **providers** list in `config/app.php`:

`Maynagashev\SocialConnections\SocialConnectionsServiceProvider::class,`

Publish package assets by running artisan command:

`php artisan vendor:publish`

Publish separately:

    php artisan vendor:publish --tag=config
    php artisan vendor:publish --tag=models
    php artisan vendor:publish --tag=views

_Routes, controllers, migrations, translations_ loaded from the package directory.


### Setup socialite providers credentials:
 
#### 1) **.env**

Because provider's credentials is environment specific and shouldn't be exposed in public, 
all credentials stored in _.env_ file.
 
_Examples:_ https://gist.github.com/maynagashev/259fce6e5a845b09dcb0a70e828966f5#file-env

 
#### 2) **config/services.php**

In config/services.php we just getting values from **.env** file with helper function **env('CONST_NAME')**.

_Examples:_ https://gist.github.com/maynagashev/259fce6e5a845b09dcb0a70e828966f5#file-services-php



### Localization:

Current locale selected by application global configuration variable **locale**, set in `config/app.php`.


### Screens from real world applications

- Social connections list in user's profile, with `$app->setLocale('en')`

![screen1](https://raw.githubusercontent.com/maynagashev/laravel-social-connections/master/screens/01.png)

- List of all supported providers and primary ones - set in config.  
![screen2](https://raw.githubusercontent.com/maynagashev/laravel-social-connections/master/screens/02.png)


###TODO:

- localization files: **ru**, **en**. 



