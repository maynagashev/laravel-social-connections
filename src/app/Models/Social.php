<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $table = 'social_logins';

    public static $providers = [];

    public static $substitutions = [
     
    ];

    protected $fillable = [
        'user_id',
        'provider',
        'social_id',
        'token',
        'token_secret',
        'refresh_token',
        'expires',
        'provider_id',
        'provider_nickname',
        'provider_name',
        'provider_email',
        'provider_avatar',
        'provider_url',
        'provider_special'
    ];

    public static function getProviders()
    {

        $providers = config('social-connections.providers') !== null ?
            config('social-connections.providers') : [];

        self::$providers = $providers; // for compatibility

        return $providers;
    }
    
    public function getProvider($provider) {
        
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}