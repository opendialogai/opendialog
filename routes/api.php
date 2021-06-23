<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::namespace('API')
    ->middleware(['auth:api'])
    ->prefix('admin/api')
    ->group(function () {
        Route::apiResource('conversation', 'ConversationsController');
        Route::apiResource(
            'webchat-setting',
            'WebchatSettingsController',
            ['except' => ['store', 'destroy']]
        );
        Route::put('/webchat-setting', 'WebchatSettingsController@multiUpdate');
        Route::apiResource(
            'chatbot-user',
            'ChatbotUsersController',
            ['except' => ['store', 'update', 'destroy']]
        );
        Route::apiResource('user', 'UsersController');

        Route::apiResource('global-context', 'GlobalContextsController');

        Route::apiResource('component-configuration', 'ComponentConfigurationController');
        Route::post('component-configurations/test', 'ComponentConfigurationController@test');
        Route::post('component-configurations/query', 'ComponentConfigurationController@query');

        Route::get('dynamic-attributes/download', 'DynamicAttributesController@download');
        Route::post('dynamic-attributes/upload', 'DynamicAttributesController@upload');
        Route::apiResource('dynamic-attribute', 'DynamicAttributesController');

        Route::get(
            'conversation-archive',
            'ConversationsController@viewArchive'
        );
        Route::prefix('conversation/{id}')->group(function () {
            Route::get('/activate', 'ConversationsController@activate');
            Route::get('/deactivate', 'ConversationsController@deactivate');
            Route::get('/archive', 'ConversationsController@archive');
            Route::get(
                '/message-templates',
                'ConversationsController@messageTemplates'
            );

            Route::get('/export', 'ConversationsController@export');
            Route::post('/import', 'ConversationsController@import');

            Route::get(
                '/restore/{versionId}',
                'ConversationsController@restore'
            );
            Route::get(
                '/reactivate/{versionId}',
                'ConversationsController@reactivate'
            );
        });

        Route::get(
            'chatbot-user/{id}/messages',
            'ChatbotUsersController@messages'
        );

        Route::get('requests', 'RequestsController@index');
        Route::get('requests/{id}', 'RequestsController@show');

        Route::get('warnings', 'WarningsController@index');
        Route::get('warnings/{id}', 'WarningsController@show');

        Route::get('conversations/export', 'ConversationsController@exportAll');
        Route::post(
            'conversations/import',
            'ConversationsController@importAll'
        );

        Route::prefix('conversation-builder')->group(function () {
            Route::apiResource('scenarios', 'ScenariosController');
            Route::post('scenarios/{scenario}/duplicate', 'ScenariosController@duplicate');

            Route::get('scenarios/{scenario}/conversations', 'ScenariosController@showConversationsByScenario');
            Route::post('scenarios/{scenario}/conversations', 'ScenariosController@storeConversationsAgainstScenario');

            Route::get('conversations/{conversation}', 'ConversationsController@show');
            Route::patch('conversations/{conversation}', 'ConversationsController@update');
            Route::delete('conversations/{conversation}', 'ConversationsController@destroy');

            Route::get('conversations/{conversation}/scenes', 'ConversationsController@showScenesByConversation');
            Route::post('conversations/{conversation}/scenes', 'ConversationsController@storeSceneAgainstConversation');

            Route::get('scenes/{scene}', 'ScenesController@show');
            Route::patch('scenes/{scene}', 'ScenesController@update');
            Route::delete('scenes/{scene}', 'ScenesController@destroy');

            Route::get('scenes/{scene}/turns', 'ScenesController@showTurnsByScene');
            Route::post('scenes/{scene}/turns', 'ScenesController@storeTurnAgainstScene');

            Route::get('turns/{turn}', 'TurnsController@show');
            Route::patch('turns/{turn}', 'TurnsController@update');
            Route::delete('turns/{turn}', 'TurnsController@destroy');

            Route::get('turns/{turn}/intents', 'TurnsController@showTurnIntentsByTurn');
            Route::post('turns/{turn}/intents', 'TurnsController@storeTurnIntentAgainstTurn');

            Route::get('turns/{turn}/turn-intents/{intent}', 'TurnsController@getTurnIntentByTurnAndIntent');
            Route::patch('turns/{turn}/turn-intents/{intent}', 'TurnsController@updateTurnIntent');
            Route::delete('turns/{turn}/turn-intents/{intent}', 'TurnsController@destroyTurnIntent');

            Route::get('intents/{intent}', 'IntentsController@show');
            Route::patch('intents/{intent}', 'IntentsController@update');
            Route::delete('intents/{intent}', 'IntentsController@destroy');

            Route::get('ui-state/focused/scenario/{scenario}', 'UIStateController@showFocusedScenario');
            Route::get('ui-state/focused/conversation/{conversation}', 'UIStateController@showFocusedConversation');
            Route::get('ui-state/focused/scene/{scene}', 'UIStateController@showFocusedScene');
            Route::get('ui-state/focused/turn/{turn}', 'UIStateController@showFocusedTurn');
            Route::get('ui-state/focused/intent/{intent}', 'UIStateController@showFocusedIntent');

            Route::get('ui-state/scenarios/{scenario}/tree', 'UIStateController@showConversationTree');

            Route::patch('ui-state/turns/{turn}/intents/{type}', 'UIStateController@massUpdateIntents')
                ->where('type', '(request)|(response)');

            Route::post('/intents/{intent}/message-templates', 'MessageTemplateController@store');

            Route::get('/intents/{intent}/message-templates/{messageTemplate}', 'MessageTemplateController@show');
            Route::get('/message-templates/{messageTemplate}', 'MessageTemplateController@show');

            Route::delete('/message-templates/{messageTemplate}', 'MessageTemplateController@destroy');
            Route::delete('/intents/{intent}/message-templates/{messageTemplate}', 'MessageTemplateController@destroy');

            Route::patch('/intents/{intent}/message-templates/{messageTemplate}', 'MessageTemplateController@update');
        });
        Route::get('{userId}/context', 'UserContextController@getUserContext');
        Route::post('{userId}/context', 'UserContextController@addToUserContext');

        Route::post('conversation-simulation', 'ConversationSimulationController@simulate');
    });
