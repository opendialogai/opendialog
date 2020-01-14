<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/api/user', function (Request $request) {
    return $request->user();
});

Route::namespace('API')->middleware(['auth:api'])->prefix('admin/api')->group(function () {
    Route::apiResource('conversation', 'ConversationsController');
    Route::apiResource('webchat-setting', 'WebchatSettingsController', ['except' => ['store', 'destroy']]);
    Route::apiResource('chatbot-user', 'ChatbotUsersController', ['except' => ['store', 'update', 'destroy']]);
    Route::apiResource('user', 'UsersController');

    Route::apiResource('outgoing-intents', 'OutgoingIntentsController');
    Route::apiResource('outgoing-intents/{id}/message-templates', 'MessageTemplatesController');

    Route::apiResource('global-context', 'GlobalContextsController');

    Route::get('conversation-archive', 'ConversationsController@viewArchive');
    Route::prefix('conversation/{id}')->group(function () {
        Route::get('/activate', 'ConversationsController@activate');
        Route::get('/deactivate', 'ConversationsController@deactivate');
        Route::get('/archive', 'ConversationsController@archive');

        Route::get('/restore/{versionId}', 'ConversationsController@restore');
        Route::get('/reactivate/{versionId}', 'ConversationsController@reactivate');
    });

    Route::get('chatbot-user/{id}/messages', 'ChatbotUsersController@messages');

    Route::get('requests', 'RequestsController@index');
    Route::get('requests/{id}', 'RequestsController@show');

    Route::get('warnings', 'WarningsController@index');
    Route::get('warnings/{id}', 'WarningsController@show');
});
