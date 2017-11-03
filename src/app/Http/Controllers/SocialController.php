<?php

namespace Maynagashev\SocialConnections\app\Http\Controllers;

use Maynagashev\SocialConnections\app\Exceptions\ProviderExceptions;
use Maynagashev\SocialConnections\app\Repositories\SocialRepository;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

use App\Social;
use App\User;
use App\UserSocial;
use Validator;
use Auth;


//use App\Models\Role;
//use App\Models\Profile;
//use App\Models\Data;


class SocialController extends Controller
{

    private $deferred_redirect;

    protected $userSocial;

    protected $repo;

    protected $redirectAfterSignUp = 'complete.registration';

    public function __construct(SocialRepository $socialRepository)
    {
        $this->repo = $socialRepository;
        return parent::__construct();
    }

    protected function initUserSocial() {

        if (!$this->userSocial) {
            $user = auth()->user();
            $this->userSocial = ($user) ? UserSocial::find($user->id) : null;
        }

        return $this->userSocial;
    }

    private function check_before_connect_has_error($provider) {

        $user = $this->initUserSocial();

        // check for allowed providers
        $allowed_providers = Social::getProviders();
        if (!in_array( $provider, $allowed_providers )) {
            throw ProviderExceptions::providerNotAllowed($provider, $allowed_providers);
        }

        // don't check if guest
        if (auth()->guest()) {
            return false;
        }

        // check for existing current socials for provider
        $currentList = $user->socialsByProvider($provider);

        if ($currentList->count()>0) {
            $provider = $this->repo->substitute($provider);
            $this->deferred_redirect = redirect()->route('profile.edit', $user->name)
                ->with('status', "You already have the connection with {$provider}, 
                if you need to add another {$provider} account, disconnect current account first.")
                ->with('alert', 'warning');
            return true;
        }

        // everything ok
        return false;
    }

    public function getSocialRedirect( $provider , $checked = false )
    {

        if (!$checked) {
            if ($this->check_before_connect_has_error($provider)) {
                return $this->deferred_redirect;
            }
        }

        $provider = $this->repo->substitute($provider);

        $providerKey = Config::get('services.' . $provider);
        if (empty($providerKey)) {
            throw ProviderExceptions::providerNotFound($provider);
        }

        return Socialite::driver( $provider )->redirect();

    }

    public function getSocialHandle(Request $request, $provider )
    {

        $provider = $this->repo->substitute_back($provider);

        if ($this->check_before_connect_has_error($provider)) {
            return $this->deferred_redirect;
        }

        if ( $request->input('denied') != '' ) {
            return redirect()->to('login')
                ->with('status', 'danger')
                ->with('message', 'You did not share your profile data with our social app.');
        }

        try{
            $user = Socialite::driver( $this->repo->substitute($provider) )->user();
        }catch(\Exception $e){
            return redirect()->to('login')
                ->with('status', 'danger')
                ->with('message', 'You session expired, try again.');
        }


        // check existing connection in social_logins
        $connection = Social::where('social_id', '=', $user->id)->where('provider', '=', $provider)->first();

        // if social_login exists, just update the data and auth user defined in connection
        if ($connection) {

            $data = $this->repo->fetch_fill_data($user, $provider);
            $connection->fill($data);
            $connection->save();

            return $this->repo->auth_user_and_redirect_home($connection->user);
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

        if (!$provider || !$data) return redirect('/login');

        // if submitted new email
        $validator = Validator::make($request->all(), ['email' => 'email|required']);
        if ($validator->fails()) {
            return view('auth.social.email', compact('provider', 'data'))->withErrors($validator);
        }

        $email = $request->input('email');
        $user = UserSocial::where('email', $email)->first();

        // 1. if user already have account, show message "need auth first"
        if ($user) {
            return view('auth.social.password', compact('provider', 'data', 'email'));
        }
        // 2. else new user, create new account active (email not verified)
        else {

            //create user
            $new_user = $this->repo->create_user($data, 0, $email);

            // create connection
            $connection = $this->repo->init_new_connection($provider, $data['provider_id'], $data);
            $new_user->social()->save($connection);

            // auth
            auth()->login($new_user, true);

            $this->repo->unset_session_data();


            return redirect("/");
        }

    }

    public function addNewAccountFromPassword(Request $request){
        $provider = session()->get('provider');
        $data = session()->get('provider_data');

        if (!$provider || !$data) return redirect('/login');
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'email' => 'email|required'
        ]);
        if ($validator->fails()) {
            return view('auth.social.password', compact('provider', 'data','email'))->withErrors($validator);
        }
        $email=$request->email;
        $password=$request->password;

        if(Auth::attempt(['email' => $email, 'password' => $password])){




            $connection = $this->repo->init_new_connection($provider, $data['provider_id'], $data);

            $exist_user = UserSocial::where('email', '=', $email)->first();

            // if email already exists in users, but no connection
            if ($exist_user) {

                // make connection
                $exist_user->social()->save($connection);

                return $this->repo->auth_user_and_redirect_home($exist_user);
            }
            //redirect()->route($this->redirectAfterSignUp);
        }else{
            return view('auth.social.password', compact('provider', 'data','email'))->withErrors(['wrongPassword'=>'¬ведите правильный пароль']);
        }

    }

    public function getAdd(Request $request, $provider)
    {
        $user = $this->initUserSocial();

        if (!$user) {
            abort(403, 'Forbidden. Authenticated users only.');
        }

        if ($this->check_before_connect_has_error($provider)) {
            return $this->deferred_redirect;
        }

        // session vars and messages for $this->redirect_home() method invoked at the end
        session()->put('redirect', $user->editConnectionsUrl);
        session()->put('redirect_status', trans('social-connections::messages.connection-established', ['provider' => $this->repo->substitute($provider)]));

        // execute process of making connection, with checked = true status
        return $this->getSocialRedirect( $provider, true);
    }

    public function getRemove(Request $request, $provider)
    {
        // at least one connection must remain to login

        $user = $this->initUserSocial();

        if ($user && $user->hasProvider($provider)) {

            if ($user->social->count()>0) {

                $user->social()->where('provider', $provider)->first()->delete();

                return $this->repo->redirect_back(trans('social-connections::messages.connection-removed', ['provider' => $this->repo->substitute($provider)]));

            }
            else {
                return $this->repo->redirect_back("At least one channel must remain so you can log in the next time.", 'warning');
            }
        }
        else {
            return $this->repo->redirect_back('Connection already removed.');
        }
    }

// Sub Actions


    /**
     * 1. Make new connection - email provided
     *
     * @param $user
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function make_connection_with_email($user, $provider)
    {

        $data = $this->repo->fetch_fill_data($user, $provider);

        $connection = $this->repo->init_new_connection($provider, $user->id, $data);

        $exist_user = UserSocial::where('email', '=', $data['provider_email'])->first();

        // if email already exists in users, but no connection
        if ($exist_user) {

            // make connection
            $exist_user->social()->save($connection);

            return $this->repo->auth_user_and_redirect_home($exist_user);
        }
        // email not exists in users
        else {

            //create user
            $new_user = $this->repo->create_user($data, 1);

            // create connection
            $new_user->social()->save($connection);

            // auth
            auth()->login($new_user, true);
            //return redirect()->route($this->redirectAfterSignUp);

            return redirect("/");
        }
    }

    private function make_connection_without_email($user, $provider){

        // If logged in, just create connection to current user
        // (we already checked that we have no records with combination [provider + social_id])
        if (auth()->check()) {
            return $this->add_new_connection_to_current_user($user, $provider);
        }
        // if not authorized, save provider data and ask for email
        else {
            $data = $this->repo->fetch_fill_data($user, $provider);

            session()->put('provider', $provider);
            session()->put('provider_data', $data);

            return view('auth.social.email', compact('data', 'provider'));
        }
    }



    private function add_new_connection_to_current_user($user, $provider)
    {
        $data = $this->repo->fetch_fill_data($user, $provider);

        $connection = $this->repo->init_new_connection($provider, $user->id, $data);

        $this->userSocial->social()->save($connection);

        return $this->repo->redirect_home();
    }

}