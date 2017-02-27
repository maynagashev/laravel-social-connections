<?php

namespace Maynagashev\SocialConnections\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{

    protected $table = 'social_logins';

    public static $providers = [
        'twitter',
        'facebook',
        'instagram',
        'youtube'
    ];

    public static $substitutions = [
        'youtube' => 'google'
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

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}