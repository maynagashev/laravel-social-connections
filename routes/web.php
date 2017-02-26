<?php


$controllersNamespace = 'Maynagashev\\SocialConnections\\Http\\Controllers';


// FOR ALL
Route::group(['namespace' => $controllersNamespace], function ($router) {

    // Laravel Socialite routes
    Route::get('social/redirect/{provider}', 'SocialController@getSocialRedirect')->name('social.redirect');
    Route::get('social/handle/{provider}', 'SocialController@getSocialHandle')->name('social.handle');

    // Ask for email address when connecting to providers, that has no email info.
    Route::get('social/email', 'SocialController@getEmail')->name('social.email');
    Route::post('social/email', 'SocialController@getEmail')->name('social.email.post');

});


// FOR AUTHENTICATED USERS
Route::group(['middleware' => 'auth', 'namespace' => $controllersNamespace], function ($router) {

    Route::get('social/remove/{provider}', 'SocialController@getRemove')->name('social.remove');
    Route::get('social/add/{provider}', 'SocialController@getAdd')->name('social.add');

});