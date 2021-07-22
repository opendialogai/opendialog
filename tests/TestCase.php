<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Conversation\BehaviorsCollection;
use OpenDialogAi\Core\Conversation\ConditionCollection;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\Transition;
use OpenDialogAi\Core\Conversation\Turn;
use OpenDialogAi\Core\Conversation\VirtualIntent;

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
        $this->markTestSkipped();

//        if (!$this->dgraphInitialised) {
//            /** @var DGraphClient $client */
//            $client = $this->app->make(DGraphClient::class);
//            $client->dropSchema();
//            $client->initSchema();
//            $this->dgraphInitialised = true;
//        }
    }

    protected function webchatSetup(): void
    {
        $this->artisan('webchat:setup', ['--non-interactive' => true]);
    }

    /**
     * @param Turn $turn
     * @param $uid
     * @param $odId
     * @param $speaker
     * @return Intent
     */
    protected function createIntent(Turn $turn, $uid, $odId, $speaker): Intent
    {
        $intent = new Intent($turn);
        $intent->setUid($uid);
        $intent->setOdId($odId);
        $intent->setName('Welcome intent 1');
        $intent->setDescription('A welcome intent 1');
        $intent->setCreatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $intent->setUpdatedAt(Carbon::parse('2021-02-24T09:30:00+0000'));
        $intent->setInterpreter('interpreter.core.nlp');
        $intent->setConditions(new ConditionCollection());
        $intent->setBehaviors(new BehaviorsCollection());
        $intent->setSpeaker($speaker);
        $intent->setConfidence(1.0);
        $intent->setListensFor(['intent_a', 'intent_b']);
        $intent->setTransition(new Transition(null, null, null));
        $intent->setVirtualIntent(VirtualIntent::createEmpty());
        $intent->setSampleUtterance('Hello!');

        return $intent;
    }
}
