<?php

namespace Maynagashev\SocialConnections\app\Http\Controllers;

use Maynagashev\SocialConnections\app\Exceptions\ProviderNotAllowed;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

use App\Social;
use App\User;
//use App\Models\Role;
//use App\Models\Profile;
//use App\Models\Data;
use Validator;


class SocialController extends Controller
{

    private $deferred_redirect;

    private function substitute($provider)
    {
        $s = Social::$substitutions;
        foreach($s as $k => $v) {
            $provider = ($provider == $k) ? $v : $provider;
        }
        return $provider;
    }

    private function substitute_back($provider)
    {
        $s = Social::$substitutions;
        foreach($s as $k => $v) {
            $provider = ($provider == $v) ? $k : $provider;
        }
        return $provider;
    }

    private function fail_checks_before_connect($provider) {

        // check for allowed providers
        $allowed_providers = Social::getProviders();
        if (!in_array( $provider, $allowed_providers )) {
            throw ProviderNotAllowed::providerNotInArray($provider, $allowed_providers);
        }

        // don't check if guest
        if (auth()->guest()) {
            return false;
        }

        // check for existing current socials for provider
        $user = auth()->user();
        $currentList = $user->socialsByProvider($provider);

        if ($currentList->count()>0) {
            $provider = $this->substitute($provider);
            $this->deferred_redirect = redirect()->route('profile.edit', $user->name)
                ->with('status', "You already have the connection with {$provider}, 
                if you need to add another {$provider} account, disconnect current account first.")
                ->with('alert', 'warning');
            return true;
        }

        // everything ok
        return false;
    }

    public function getAdd(Request $request, $provider)
    {

        $user = auth()->user();

        if (!$user) {
            return $this->redirect_back("You must be authenticated to use this module.", 'warning');
        }

        if ($this->fail_checks_before_connect($provider)) {
            return $this->deferred_redirect;
        }

        // session vars and messages for $this->redirect_home() method invoked at the end
        session()->put('redirect', "/profile/{$user->name}/edit");
        session()->put('redirect_status', 'Connection to '.$this->substitute($provider).' established.' );

        // execute process of making connection, with checked = true status
        return $this->getSocialRedirect( $provider, true);
    }

    public function getSocialRedirect( $provider , $checked = false )
    {

        if (!$checked) {
            if ($this->fail_checks_before_connect($provider)) {
                return $this->deferred_redirect;
            }
        }

        $provider = $this->substitute($provider);

        $providerKey = Config::get('services.' . $provider);
        if (empty($providerKey)) {
            dd('error','No such provider');
        }

        return Socialite::driver( $provider )->redirect();

    }

    public function getSocialHandle(Request $request, $provider )
    {

        $provider = $this->substitute_back($provider);

        if ($this->fail_checks_before_connect($provider)) {
            return $this->deferred_redirect;
        }

        if ( $request->input('denied') != '' ) {
            return redirect()->to('login')
                ->with('status', 'danger')
                ->with('message', 'You did not share your profile data with our social app.');
        }

        $user = Socialite::driver( $this->substitute($provider) )->user();

        // check existing connection in social_logins
        $connection = Social::where('social_id', '=', $user->id)->where('provider', '=', $provider)->first();

        // if social_login exists, just update the data and auth user defined in connection
        if ($connection) {

            $data = $this->fetch_fill_data($user, $provider);
            $connection->fill($data);
            $connection->save();

            return $this->auth_user_and_redirect_home($connection->user);
        }
        //if not exist then execute process of creating new connection
        else {



            // if authenticated add connection to that user
            if (auth()->check()) {
                return $this->add_new_connection_to_current_user($user, $provider);
            }
            // else proccess guest sign up
            else {
                if ($user->email) {
                    return $this->make_connection_with_email($user, $provider);
                } else {
                    return $this->make_connection_without_email($user, $provider);
                }
            }
        }

    }

    /**
     * Ask for email (twitter, instagram connections)
     * @param Request $request
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEmail(Request $request)
    {

        $provider = session()->get('provider');
        $data = session()->get('provider_data');

        if (!$provider || !$data) die("Session expired.");

        // if submitted new email
        if ($request->has('email')) {

            $validator = Validator::make($request->all(), ['email' => 'email|required']);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            // 1. if user already have account, show message "need auth first"
            if ($user) {
                return view('auth.social.password', compact('provider', 'data', 'email'));
            }

            // 2. else new user, create new account active (email not verified)
            else {

                //create user
                $new_user = $this->create_user($data, 0, $email);

                // create connection
                $connection = $this->init_new_connection($provider, $data['provider_id'], $data);
                $new_user->social()->save($connection);

                // auth
                auth()->login($new_user, true);

                $this->unset_session_data();

                return redirect()->route('profile.edit', $new_user->name);
            }
        }
        // else show form
        else {
            return view('auth.social.email', compact('provider', 'data'));
        }
    }

    public function getRemove(Request $request, $provider)
    {
        // at least one connection must remain to login

        $user = auth()->user();
        if ($user && $user->hasSocial($provider)) {

            if ($user->social->count()>0) {

                $c = $user->social()->where('provider', $provider)->first();
                $c->delete();

                return $this->redirect_back('Connection to '.$this->substitute($provider).' removed.');

            }
            else {
                return $this->redirect_back("At least one channel must remain so you can log in the next time.", 'warning');
            }
        }
        else {
            return $this->redirect_back('Connection already removed.');
        }
    }

// HELPERS


    private function unset_session_data()
    {
        session()->forget('provider');
        session()->forget('provider_data');
    }

    private function init_new_connection($provider, $social_id, $data) {


        $connection = new Social();
        $connection->social_id = $social_id;
        $connection->provider = $provider;
        $connection->fill($data);

        return $connection;
    }

    /**
     * 1. Make new connection - email provided
     *
     * @param $user
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function make_connection_with_email($user, $provider)
    {

        $data = $this->fetch_fill_data($user, $provider);

        $connection = $this->init_new_connection($provider, $user->id, $data);

        $exist_user = User::where('email', '=', $data['provider_email'])->first();

        // if email already exists in users, but no connection
        if ($exist_user) {

            // make connection
            $exist_user->social()->save($connection);

            return $this->auth_user_and_redirect_home($exist_user);
        }
        // email not exists in users
        else {

            //create user
            $new_user = $this->create_user($data, 1);

            // create connection
            $new_user->social()->save($connection);

            // auth
            auth()->login($new_user, true);

            return redirect()->route('profile.edit', $new_user->name);
        }
    }


    private function make_connection_without_email($user, $provider)
    {

        // If logged in, just create connection to current user
        // (we already checked that we have no records with combination [provider + social_id])
        if (auth()->check()) {
            return $this->add_new_connection_to_current_user($user, $provider);
        }
        // if not authorized, save provider data and ask for email
        else {
            $data = $this->fetch_fill_data($user, $provider);

            session()->put('provider', $provider);
            session()->put('provider_data', $data);

            return view('auth.social.email', compact('data', 'provider'));
        }
    }
    private function auth_user_and_redirect_home(User $user) {

        // before login, check if previously not set influencer type
        if (!$user->is_influencer) {
            $user->is_influencer = 1;
            $user->save();
        }

        auth()->login($user, true);

        return $this->redirect_home();
    }


    private function redirect_home()
    {
        if ( auth()->user()->hasRole('user') || auth()->user()->hasRole('administrator')) {

            $redirect = session()->pull('redirect');
            $redirect_status = session()->pull('redirect_status');

            if ($redirect && $redirect_status) {
                return redirect($redirect)->with('status', $redirect_status);
            }
            else {
                return redirect('/home#');
            }

        }

        return \App::abort(500);  // user role not fetched
    }

    private function redirect_back($text, $alert_class='success')
    {
      //dump('redirect back', $text, $alert_class);
        return redirect()->back()->with('status', $text)->with('alert', $alert_class);
    }

    /**
     * @param $data  (fetched data from $this->fetch_fill_data)
     * @param int $email_verified
     * @param string $email
     * @return User
     */
    private function create_user($data, $email_verified= 1, $email = null) {

        $name = $data['provider_name'];
        $name = (User::where('name', $name)->count() > 0) ? $name.'_'.uniqid() : $name;  // if that name exists - add uniqid

        $new_user = new User;
        $new_user->active = 1;
        $new_user->is_influencer = 1;
        $new_user->email_verified = $email_verified;
        $new_user->name = $name;
        $new_user->email = ($data['provider_email']) ? $data['provider_email'] : $email;
        $new_user->password = bcrypt(str_random(16));
        $new_user->activation_code = str_random(60) . $data['provider_email'];


        $name = explode(' ', $data['provider_name']);
        if (count($name) >= 1) $new_user->first_name = $name[0];
        if (count($name) >= 2) $new_user->last_name = $name[1];


        $new_user->signup_sm_ip_address	= request()->ip();

        // create user
        $new_user->save();

        // create role
        $role = Role::whereName('user')->first();
        $new_user->assignRole($role);

        // create profile
        $profile = new Profile;
        $new_user->profile()->save($profile);


        return $new_user;
    }

    private function add_new_connection_to_current_user($user, $provider)
    {
        $data = $this->fetch_fill_data($user, $provider);

        $connection = $this->init_new_connection($provider, $user->id, $data);

        auth()->user()->social()->save($connection);

        return $this->redirect_home();
    }

    private function fetch_fill_data($user, $provider = null)
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

        if ($provider=='google' || $provider=='youtube') {
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
        }
        if ($provider=='facebook') {
            $d['provider_url'] = $user->user['link'];
        }

        // dd($d, $user->user);

        return $d;
    }


}