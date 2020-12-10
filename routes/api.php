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

    Route::apiResource('outgoing-intent', 'OutgoingIntentsController');
    Route::get('outgoing-intent/{id}/export', 'OutgoingIntentsController@export');
    Route::post('outgoing-intent/{id}/import', 'OutgoingIntentsController@import');
    Route::apiResource('outgoing-intent/{id}/message-templates', 'MessageTemplatesController');

    Route::apiResource('global-context', 'GlobalContextsController');

    Route::get('conversation-archive', 'ConversationsController@viewArchive');
    Route::prefix('conversation/{id}')->group(function () {
        Route::get('/activate', 'ConversationsController@activate');
        Route::get('/deactivate', 'ConversationsController@deactivate');
        Route::get('/archive', 'ConversationsController@archive');
        Route::get('/message-templates', 'ConversationsController@messageTemplates');

        Route::get('/export', 'ConversationsController@export');
        Route::post('/import', 'ConversationsController@import');

        Route::get('/restore/{versionId}', 'ConversationsController@restore');
        Route::get('/reactivate/{versionId}', 'ConversationsController@reactivate');
    });

    Route::get('chatbot-user/{id}/messages', 'ChatbotUsersController@messages');

    Route::get('requests', 'RequestsController@index');
    Route::get('requests/{id}', 'RequestsController@show');

    Route::get('warnings', 'WarningsController@index');
    Route::get('warnings/{id}', 'WarningsController@show');

    Route::get('conversations/export', 'ConversationsController@exportAll');
    Route::post('conversations/import', 'ConversationsController@importAll');

    Route::get('outgoing-intents/export', 'OutgoingIntentsController@exportAll');
    Route::post('outgoing-intents/import', 'OutgoingIntentsController@importAll');

    Route::post('specification-import', 'SpecificationController@import');
    Route::get('specification-export', 'SpecificationController@export');
});
