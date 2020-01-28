<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'web'], function () {
    Route::get('/onboarding', 'OnboardingController@handle');
    Route::get('/onboarding/{id}', 'OnboardingController@handle');
});
