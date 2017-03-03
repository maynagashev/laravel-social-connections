<?php

namespace App;

class UserSocial extends User {

    protected $table = 'users';

    public $editConnectionsUrl = 'u/edit/connections.html';

    public function getProvidersAttribute()
    {
        return $this->social->keyBy('provider');
    }


    public function social()
    {
        return $this->hasMany(Social::class, 'user_id');
    }

    public function hasProvider($provider = null)
    {
        if (!$provider) {
            return ($this->social->count()>0);
        }
        else {
            foreach ($this->social as $d) {
                if ($d->provider == $provider) return true;
            }
            return false;
        }        
    }

    public function socialsByProvider($provider)
    {
        $ret = collect([]);
        foreach ($this->social as $d) {
            if ($d->provider == $provider) {
                $ret->push($d);
            }
        }

        return $ret;
    }

    public function getProvider($provider)
    {
        return $this->social->keyBy('provider')->get($provider);
    }
}