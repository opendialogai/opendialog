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

Route::get('auth/token', 'Auth\TwoFactorController@showTokenForm');
Route::post('auth/token', 'Auth\TwoFactorController@validateTokenForm');
Route::post('auth/two-factor', 'Auth\TwoFactorController@setupTwoFactorAuth');

Route::group(['middleware' => 'auth'], function () {
    Route::get('od-admin', 'AdminController@handle');
    Route::get('od-admin/webchat-setting', 'AdminController@handle');

    Route::get('admin/api/conversation', 'Admin\ConversationController@viewAll');
    Route::get('admin/api/conversation/{id}', 'Admin\ConversationController@view');
    Route::patch('admin/api/conversation/{id}', 'Admin\ConversationController@update');
    Route::delete('admin/api/conversation/{id}', 'Admin\ConversationController@delete');

    Route::get('admin/api/webchat-setting', 'Admin\WebchatSettingsController@viewAll');
    Route::get('admin/api/webchat-setting/{id}', 'Admin\WebchatSettingsController@view');
    Route::patch('admin/api/webchat-setting/{id}', 'Admin\WebchatSettingsController@update');
});
