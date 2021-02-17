<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var bool Whether DGraph has been initialised or not
     */
    private $dgraphInitialised = false;

    /**
     * Runs migrations on the sqlite database
     */
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        if ($overwriteDgraphUrl = getenv("OVERWRITE_DGRAPH_URL")) {
            $this->app['config']->set('opendialog.core.DGRAPH_URL', $overwriteDgraphUrl);
        }
        if ($overwriteDgraphPort = getenv("OVERWRITE_DGRAPH_PORT")) {
            $this->app['config']->set('opendialog.core.DGRAPH_PORT', $overwriteDgraphPort);
        }

        if ($overwriteDgraphAuthToken = getenv("OVERWRITE_DGRAPH_AUTH_TOKEN")) {
            $this->app['config']->set('opendialog.core.DGRAPH_AUTH_TOKEN', $overwriteDgraphAuthToken);
        }
    }

    protected function initDDgraph(): void
    {
        if (!$this->dgraphInitialised) {
            /** @var DGraphClient $client */
            $client = $this->app->make(DGraphClient::class);
            $client->dropSchema();
            $client->initSchema();
            $this->dgraphInitialised = true;
        }
    }

    protected function webchatSetup(): void
    {
        $this->artisan('webchat:setup');
    }
}
