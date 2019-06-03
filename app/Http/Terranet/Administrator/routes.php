<?php

$pattern = '[a-z0-9\_\-]+';

Route::group([
    'prefix' => config('administrator.prefix', 'cms'),
    'namespace' => 'Terranet\Administrator\Controllers',
    'middleware' => ['web'],
], function () use ($pattern) {
    /*
    |-------------------------------------------------------
    | Authentication
    |-------------------------------------------------------
    */
    Route::get('login', [
        'as' => 'scaffold.login',
        'uses' => 'AuthController@getLogin',
    ]);
    Route::post('login', 'AuthController@postLogin');
    Route::get('logout', [
        'as' => 'scaffold.logout',
        'uses' => 'AuthController@getLogout',
    ]);

    /*
    |-------------------------------------------------------
    | Main Scaffolding routes
    |-------------------------------------------------------
    */
    Route::group([], function () use ($pattern) {
        /*
        |-------------------------------------------------------
        | Custom routes
        |-------------------------------------------------------
        |
        | Controllers that shouldn't be handled by Scaffolding controller
        | goes here.
        |
        */
        //        Route::controllers([
        //            'test' => 'App\Http\Controllers\Admin\TestController'
        //        ]);

        /*
        |-------------------------------------------------------
        | Scaffolding routes
        |-------------------------------------------------------
        */
        // Dashboard
        Route::get('/', [
            'as' => 'scaffold.dashboard',
            'uses' => 'DashboardController@index',
        ]);

        Route::resource('translations', 'TranslationsController', [
            'only' => ['index', 'store'],
            'as' => 'scaffold',
        ]);

        Route::group(['prefix' => 'media'], function () {
            Route::get('/', [
                'as' => 'scaffold.media',
                'uses' => 'MediaController@index',
            ]);

            Route::get('popup', [
                'as' => 'scaffold.media.popup',
                'uses' => 'MediaController@popup',
            ]);

            Route::post('/', [
                'as' => 'scaffold.media.mkdir',
                'uses' => 'MediaController@mkdir',
            ]);

            Route::post('rename', [
                'as' => 'scaffold.media.rename',
                'uses' => 'MediaController@rename',
            ]);

            Route::post('move', [
                'as' => 'scaffold.media.move',
                'uses' => 'MediaController@move',
            ]);

            Route::post('remove', [
                'as' => 'scaffold.media.remove',
                'uses' => 'MediaController@removeSelected',
            ]);

            Route::post('upload', [
                'as' => 'scaffold.media.upload',
                'uses' => 'MediaController@upload',
            ]);
        });

        // Index
        Route::get('{module}', [
            'as' => 'scaffold.index',
            'uses' => 'ScaffoldController@index',
        ])->where('module', $pattern);

        // Create new Item
        Route::get('{module}/create', [
            'as' => 'scaffold.create',
            'uses' => 'ScaffoldController@create',
        ])->where('module', $pattern);

        // Save new item
        Route::post('{module}/create', 'ScaffoldController@store')->where('module', $pattern);

        // View Item
        Route::get('{module}/{id}', [
            'as' => 'scaffold.view',
            'uses' => 'ScaffoldController@view',
        ])->where('module', $pattern);

        // Edit Item
        Route::get('{module}/{id?}/edit', [
            'as' => 'scaffold.edit',
            'uses' => 'ScaffoldController@edit',
        ])->where('module', $pattern);

        // Save Item
        Route::post('{module}/{id?}/edit', [
            'as' => 'scaffold.update',
            'uses' => 'ScaffoldController@update',
        ])->where('module', $pattern);

        // Delete Item
        Route::get('{module}/{id}/delete', [
            'as' => 'scaffold.delete',
            'uses' => 'ScaffoldController@delete',
        ])->where('module', $pattern);

        // Delete attachment
        Route::get('{module}/{id}/delete/attachment/{attachment}', [
            'as' => 'scaffold.delete_attachment',
            'uses' => 'ScaffoldController@deleteAttachment',
        ])->where('module', $pattern);

        // Custom method
        Route::get('{module}/{id}/{action}', [
            'as' => 'scaffold.action',
            'uses' => 'ScaffoldController@action',
        ])->where('module', $pattern);

        // Custom batch method
        Route::post('{module}/batch-action', [
            'as' => 'scaffold.batch',
            'uses' => 'BatchController@batch',
        ])->where('module', $pattern);

        // Export collection url
        Route::get('{module}.{format}', [
            'as' => 'scaffold.export',
            'uses' => 'BatchController@export',
        ])->where('module', $pattern);
    });
});
