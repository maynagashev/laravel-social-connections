# laravel-social-connections
Laravel package, adds social connections management, users can connect
multiple social networks to a laravel account.

Status: *in development*.


### Setup socialite providers credentials:
 
#### 1) **.env**

Because provider's credentials is environment specific and shouldn't be exposed in public, 
all credentials stored in _.env_ file.
 
_Examples:_ https://gist.github.com/maynagashev/259fce6e5a845b09dcb0a70e828966f5#file-env

 
#### 2) **config/services.php**

In config/services.php we just getting values from **.env** file with helper function **env('CONST_NAME')**.

_Examples:_ https://gist.github.com/maynagashev/259fce6e5a845b09dcb0a70e828966f5#file-services-php

