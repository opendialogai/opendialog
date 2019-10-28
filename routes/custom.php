<?php

Route::namespace('API')->middleware(['auth:api'])->prefix('admin/api')->group(function () {
    Route::get('conversations-list', 'ConversationsController@adminList');
    Route::get('webchat-settings-categories', 'WebchatSettingsController@getCategories');
});
