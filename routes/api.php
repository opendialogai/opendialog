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

Route::group(['prefix' => 'admin/api', 'middleware' => 'auth:api'], function () {
    Route::apiResource('conversation', 'API\ConversationsController');
    Route::apiResource('webchat-setting', 'API\WebchatSettingsController', ['except' => ['store', 'destroy']]);
    Route::apiResource('chatbot-user', 'API\ChatbotUsersController', ['except' => ['store', 'update', 'destroy']]);
    Route::apiResource('user', 'API\UsersController');
    Route::apiResource('outgoing-intents', 'API\OutgoingIntentsController');
    Route::apiResource('outgoing-intents/{id}/message-templates', 'API\MessageTemplatesController');

    Route::get('conversation/{id}/publish', 'API\ConversationsController@publish');
    Route::get('conversation/{id}/unpublish', 'API\ConversationsController@unpublish');
    Route::get('chatbot-user/{id}/messages', 'API\ChatbotUsersController@messages');
});
