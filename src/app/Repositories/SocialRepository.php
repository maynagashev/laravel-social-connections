<?php

namespace Maynagashev\SocialConnections\app\Repositories;

class SocialRepository {


    public function fetch_fill_data($user, $provider = null)
    {

        $d = array(

            // OAuth Two Providers
            'token' => $user->token,
            'refresh_token' => ((isset($user->refreshToken)) ? $user->refreshToken : ''), // not always provided
            'expires' => ((isset($user->expiresIn)) ? $user->expiresIn : ''),

            // OAuth One Providers
            'tokenSecret' => ((isset($user->tokenSecret)) ? $user->tokenSecret : ''),

            // All Providers
            'provider_id' => $user->getId(),
            'provider_nickname' => $user->getNickname(),
            'provider_name' => $user->getName(),
            'provider_email' => $user->getEmail(),
            'provider_avatar' => $user->getAvatar(),
        );


        switch ($provider) {

            // Google
            case 'google':
            case 'youtube':

                if ($user->user['isPlusUser']) {
                    $d['provider_avatar'] = $user->avatar_original;
                    $d['provider_url'] = $user->user['url'];
                    $d['provider_nickname'] = $user->user['displayName'];
                }
                else {
                    $d['provider_avatar'] = $user->avatar_original;
                    $d['provider_url'] = '';
                    $d['provider_nickname'] = '[google_plus_disabled]';
                }
                break;

            // Facebook
            case 'facebook':
                $d['provider_url'] = $user->user['link'];

                break;

            // VK.com
            case 'vkontakte':
                $d['provider_url'] = 'https://vk.com/'.$d['provider_nickname'];

                break;

        }

        //dd($d, $user->user);

        return $d;
    }



}