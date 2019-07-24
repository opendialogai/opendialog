<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

if (env("APP_DEBUG")) {
        Route::get('/demo', function () {
        return view('demo');
    });
}

Auth::routes(['register' => false]);

if (env("USE_2FA")) {
    Route::get('auth/token', 'Auth\TwoFactorController@showTokenForm');
    Route::post('auth/token', 'Auth\TwoFactorController@validateTokenForm');
    Route::post('auth/two-factor', 'Auth\TwoFactorController@setupTwoFactorAuth');
}

Route::group(['middleware' => 'auth'], function () {
    Route::get('admin', 'AdminController@handle');
    Route::get('admin/webchat-setting', 'AdminController@handle');
    Route::get('admin/webchat-setting/{id}', 'AdminController@handle');
    Route::get('admin/conversations', 'AdminController@handle');
    Route::get('admin/conversations/{id}', 'AdminController@handle');
    Route::get('admin/conversations/{id}/edit', 'AdminController@handle');
    Route::get('admin/conversations/add', 'AdminController@handle');
    Route::get('admin/outgoing-intents', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}/edit', 'AdminController@handle');
    Route::get('admin/outgoing-intents/add', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}/message-templates', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}/message-templates/{id}', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}/message-templates/{id}/edit', 'AdminController@handle');
    Route::get('admin/outgoing-intents/{outgoingIntent}/message-templates/add', 'AdminController@handle');
    Route::get('admin/chatbot-users', 'AdminController@handle');
    Route::get('admin/chatbot-users/{id}', 'AdminController@handle');
    Route::get('admin/chatbot-users/{id}/conversation-log', 'AdminController@handle');
    Route::get('admin/users', 'AdminController@handle');
    Route::get('admin/users/{id}', 'AdminController@handle');
    Route::get('admin/users/{id}/edit', 'AdminController@handle');
    Route::get('admin/users/add', 'AdminController@handle');

    Route::get('stats/users', 'StatisticsController@users');
    Route::get('stats/cost', 'StatisticsController@cost');
});
