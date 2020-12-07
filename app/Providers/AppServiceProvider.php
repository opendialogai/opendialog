<?php

namespace App\Providers;

use App\Bot\DialogFlow\DialogflowClient;
use App\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(DialogflowClient::class, function () {
            return new DialogflowClient([
                'language_code' => 'en-GB'
            ]);
        });

        User::observe(UserObserver::class);

        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
    }
}
