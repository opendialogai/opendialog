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


use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;

Fortify::loginView(function () {
    return view('auth.login');
});
Fortify::twoFactorChallengeView(function () {
    return view('auth.two-factor-challenge');
});

Fortify::requestPasswordResetLinkView(function () {
    return view('auth.passwords.email');
});

Fortify::resetPasswordView(function ($request) {
    return view('auth.passwords.reset', ['request' => $request]);
});

Route::get('/', function () {
    return redirect('/admin');
});

/**
 * Admin Routes
 */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/', 'AdminController@handle');

    Route::get('/demo', 'AdminController@handle')->name('webchat-demo');

    /**
     * Webchat Settings
     */
    Route::prefix('webchat-setting')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
    });

    /**
     * Chatbot Users
     */
    Route::prefix('chatbot-users')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
        Route::get('/{id}/conversation-log', 'AdminController@handle');
    });

    /**
     * Users
     */
    Route::prefix('users')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/add', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
        Route::get('/{id}/edit', 'AdminController@handle');
    });

    /**
     * Dynamic Attributes
     */
    Route::prefix('dynamic-attributes')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/add', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
        Route::get('/{id}/edit', 'AdminController@handle');
    });

    /**
     * Requests
     */
    Route::prefix('requests')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
    });

    /**
     * Global Contexts
     */
    Route::prefix('global-contexts')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/add', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
        Route::get('/{id}/edit', 'AdminController@handle');
    });

    /**
     * Warnings
     */
    Route::prefix('warnings')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
    });

    Route::get('conversation-builder/{anything}', 'AdminController@handle')->where('anything', '.*');
    Route::get('interpreters', 'AdminController@handle');
    Route::get('interpreters/{anything}', 'AdminController@handle')->where('anything', '.*');
    Route::get('actions', 'AdminController@handle');
    Route::get('actions/{anything}', 'AdminController@handle')->where('anything', '.*');
    Route::get('message-editor', 'AdminController@handle');
});

/**
 * Statistics Routes
 */
Route::prefix('stats')->middleware(['auth'])->group(function () {
    Route::get('chatbot-users', 'StatisticsController@chatbotUsers');
    Route::get('requests', 'StatisticsController@requests');
    Route::get('scenarios', 'StatisticsController@scenarios');
    Route::get('conversations', 'StatisticsController@conversations');
    Route::get('message-templates', 'StatisticsController@messageTemplates');
});

/**
 * Reflection Routes
 */
Route::prefix('reflection')->middleware(['auth'])->group(function () {
    Route::get('all', 'ReflectionController');
});

Route::get('status', 'StatusController@handle');

Route::group(['middleware' => 'auth'], function () {
    Route::get('admin/logout', 'Auth\LoginController@logout');
    Route::get('logout', 'Auth\LoginController@logout');
});
