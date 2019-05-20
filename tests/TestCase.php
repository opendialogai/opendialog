<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Runs migrations on the sqlite database
     */
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->artisan('webchat:setup');
    }
}
