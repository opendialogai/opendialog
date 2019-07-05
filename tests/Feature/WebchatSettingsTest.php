<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
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

        $this->actingAs($this->user, 'api')
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

        $this->actingAs($this->user, 'api')
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

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 'updated value',
                'type' => 'updated value',
                'name' => 'updated value'
            ])
            ->assertStatus(200);

        $updatedSetting = WebchatSetting::first();

        $this->assertEquals($updatedSetting->value, 'updated value');
        $this->assertNotEquals($updatedSetting->name, 'updated value');
        $this->assertNotEquals($updatedSetting->type, 'updated value');
    }

    public function testWebchatSettingsUpdateEndpointValidationNumber()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'number',
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 'pippo',
            ])
            ->assertStatus(400);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 10,
            ])
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '10',
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsUpdateEndpointValidationBoolean()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'boolean',
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 'pippo',
            ])
            ->assertStatus(400);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '1',
            ])
            ->assertStatus(200);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => true,
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsUpdateEndpointValidationColour()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'colour',
            'value' => '#000000',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '#00000',
            ])
            ->assertStatus(400);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '#fff',
            ])
            ->assertStatus(200);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '#ffffff',
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsUpdateEndpointValidationString()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'string',
            'value' => 'test',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(9000),
            ])
            ->assertStatus(400);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(900),
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsUpdateEndpointValidationObject()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'object',
            'value' => '',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(10),
            ])
            ->assertStatus(400);
    }

    public function testWebchatSettingsUpdateEndpointValidationMap()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => 'map',
            'value' => json_encode([
                'key' => 'value',
            ]),
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(10),
            ])
            ->assertStatus(400);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => json_encode($setting),
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsStoreEndpoint()
    {
        $setting = WebchatSetting::first();

        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/webchat-setting', [
                'type' => 'string',
                'name' => 'new setting',
                'value' => 'test',
            ])
            ->assertStatus(405);
    }

    public function testWebchatSettingsDestroyEndpoint()
    {
        $setting = WebchatSetting::first();

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/webchat-setting/' . $setting->id)
            ->assertStatus(405);
    }
}
