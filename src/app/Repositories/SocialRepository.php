<?php

namespace Maynagashev\SocialConnections\app\Repositories;

use App\Social;
use App\UserSocial;
use App\User;

use Illuminate\Support\Facades\Hash;


class SocialRepository {


    /**
     * @param $data  (fetched data from $this->fetch_fill_data)
     * @param int $email_verified
     * @param string $email
     * @return User
     */
    public function create_user($data, $email_verified= 1, $email = null) {

        $name = $data['provider_name'];
        $name = (UserSocial::where('name', $name)->count() > 0) ? $name.'_'.uniqid() : $name;  // if that name exists - add uniqid

        $new_user = new UserSocial;
        $new_user->name = $name;
        $new_user->email = ($data['provider_email']) ? $data['provider_email'] : $email;
        $new_user->password =  Hash::make(str_random(12));
//        $new_user->active = 1;
//        $new_user->email_verified = $email_verified;
//        $new_user->activation_code = str_random(60) . $data['provider_email'];

        $name = explode(' ', $data['provider_name']);
        if (count($name) >= 1) $new_user->first_name = $name[0];
        if (count($name) >= 2) $new_user->last_name = $name[1];

        // create user
        $new_user->save();

        return $new_user;
    }

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

    public function init_new_connection($provider, $social_id, $data) {

        $connection = new Social();
        $connection->social_id = $social_id;
        $connection->provider = $provider;
        $connection->fill($data);

        return $connection;
    }
    
    
    
    
    
    // REDIRECTS
    
    public function auth_user_and_redirect_home(User $user) {

        auth()->login($user, true);

        return $this->redirect_home();
    }
    
    public function redirect_home()
    {
        if ( auth()->user()) {

            $redirect = session()->pull('redirect');
            $redirect_status = session()->pull('redirect_status');

            if ($redirect && $redirect_status) {
                return redirect($redirect)->with('status', $redirect_status);
            }
            else {
                return redirect('/');
            }
        }

        return abort(403, 'Redirect home only for authenticated users');
    }

    public function redirect_back($text, $alert_class='success')
    {
        //dump('redirect back', $text, $alert_class);
        return redirect()->back()->with('status', $text)->with('alert', $alert_class);
    }    
    

    // Helpers
    public function unset_session_data()
    {
        session()->forget('provider');
        session()->forget('provider_data');
    }

    public function substitute($provider)
    {
        $s = Social::$substitutions;
        foreach($s as $k => $v) {
            $provider = ($provider == $k) ? $v : $provider;
        }
        return $provider;
    }

    public function substitute_back($provider)
    {
        $s = Social::$substitutions;
        foreach($s as $k => $v) {
            $provider = ($provider == $v) ? $k : $provider;
        }
        return $provider;
    }


}