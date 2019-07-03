<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Webchat\WebchatSetting;
use Tests\TestCase;

class WebchatSettingsTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('webchat:settings');

        $this->user = factory(User::class)->create();
    }

    public function testWebchatSettingsViewEndpoint()
    {
        $setting = WebchatSetting::first();

        $this->get('/admin/api/webchat-setting/' . $setting->id)
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->json('GET', '/admin/api/webchat-setting/' . $setting->id)
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'name' => $setting->name,
                    'type' => $setting->type,
                ]
            );
    }

    public function testWebchatSettingsViewAllEndpoint()
    {
        $settings = WebchatSetting::all();

        $this->get('/admin/api/webchat-setting')
            ->assertStatus(302);

        $this->actingAs($this->user)
            ->json('GET', '/admin/api/webchat-setting')
            ->assertStatus(200)
            ->assertJsonCount(count($settings))
            ->assertJsonFragment(
                [
                    'name' => WebchatSetting::URL,
                    'type' => WebchatSetting::STRING,
                ]
            )->assertJsonFragment(
                [
                    'name' => WebchatSetting::TEAM_NAME,
                    'type' => WebchatSetting::STRING,
                ]
            )->assertJsonFragment(
                [
                    'name' => WebchatSetting::HEADER_TEXT,
                    'type' => WebchatSetting::COLOUR,
                ]
            )->assertJsonFragment(
                [
                    'name' => WebchatSetting::START_MINIMIZED,
                    'type' => WebchatSetting::BOOLEAN,
                ]
            );
    }

    public function testWebchatSettingsUpdateEndpoint()
    {
        $setting = WebchatSetting::first();

        $this->actingAs($this->user)
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, ['value' => 'updated value'])
            ->assertStatus(200);

        $updatedSetting = WebchatSetting::first();

        $this->assertEquals($updatedSetting->value, 'updated value');
    }
}
