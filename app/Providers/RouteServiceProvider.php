<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use OpenDialogAi\Core\Conversation\Exceptions\ConversationObjectNotFoundException;
use OpenDialogAi\Core\Conversation\Facades\ConversationDataClient;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // Sets up custom route binding for user in routes files
        Route::bind('scenario', function ($value) {
            try {
                return ConversationDataClient::getScenarioByUid($value, false);
            } catch (ConversationObjectNotFoundException $exception) {
                throw new ModelNotFoundException(sprintf('Scenario with ID %s not found', $value));
            }
        });

        Route::bind('conversation', function ($value) {
            try {
                return ConversationDataClient::getConversationByUid($value, false);
            } catch (ConversationObjectNotFoundException $exception) {
                throw new ModelNotFoundException(sprintf('Conversation with ID %s not found', $value));
            }
        });

        Route::bind('scene', function ($value) {
            try {
                return ConversationDataClient::getSceneByUid($value, false);
            } catch (ConversationObjectNotFoundException $exception) {
                throw new ModelNotFoundException(sprintf('Scene with ID %s not found', $value));
            }
        });

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapCustomRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "custom" routes for the application.
     *
     * @return void
     */
    protected function mapCustomRoutes()
    {
        Route::middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/custom.php'));
    }
}
