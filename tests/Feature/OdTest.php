<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OdTest extends TestCase
{
    /**
     * Verify that the demo endpoint is present.
     *
     * @return void
     */
    public function testDemoEndpoint()
    {
        $response = $this->get('/demo');

        $response->assertStatus(200);
        $response->assertSeeTextInOrder([
            'Send Trigger message',
            'Set custom user attribute',
            'window.openDialogSettings',
        ]);
    }

    /**
     * Verify that the webchat endpoint is present.
     *
     * @return void
     */
    public function testWebchatEndpoint()
    {
        $response = $this->get('/web-chat');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '<opendialog-chat>',
            'vendor/webchat/js/app.js',
        ]);
    }

    /**
     * Verify that the webchat settings endpoint is present.
     *
     * @return void
     */
    public function testWebchatSettingsEndpoint()
    {
        $response = $this->get('/webchat-config');

        $response->assertStatus(200);
        $response->assertJson([
        ]);
    }
}
