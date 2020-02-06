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

Auth::routes(['register' => false]);

if (env("USE_2FA")) {
    Route::get('auth/token', 'Auth\TwoFactorController@showTokenForm');
    Route::post('auth/token', 'Auth\TwoFactorController@validateTokenForm');
    Route::post('auth/two-factor', 'Auth\TwoFactorController@setupTwoFactorAuth');
}

Route::get('/', function () {
    return view('welcome');
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
     * Conversations
     */
    Route::prefix('conversations')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/add', 'AdminController@handle');
        Route::get('/{id}', 'AdminController@handle');
        Route::get('/{id}/edit', 'AdminController@handle');
    });

    /**
     * Outgoing Intents
     */
    Route::prefix('outgoing-intents')->group(function () {
        Route::get('/', 'AdminController@handle');
        Route::get('/add', 'AdminController@handle');
        Route::get('/{outgoingIntent}', 'AdminController@handle');
        Route::get('/{outgoingIntent}/edit', 'AdminController@handle');

        /**
         * Message Templates
         */
        Route::prefix('/{outgoingIntent}/message-templates')->group(function () {
            Route::get('/', 'AdminController@handle');
            Route::get('/add', 'AdminController@handle');
            Route::get('/{id}', 'AdminController@handle');
            Route::get('/{id}/edit', 'AdminController@handle');
        });
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
});

/**
 * Statistics Routes
 */
Route::prefix('stats')->middleware(['auth'])->group(function () {
    Route::get('chatbot-users', 'StatisticsController@chatbotUsers');
    Route::get('requests', 'StatisticsController@requests');
    Route::get('conversations', 'StatisticsController@conversations');
    Route::get('incoming-intents', 'StatisticsController@incomingIntents');
    Route::get('message-templates', 'StatisticsController@messageTemplates');
});
