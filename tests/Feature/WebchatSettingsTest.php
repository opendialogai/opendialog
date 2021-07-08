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

        $this->user = factory(User::class)->create();

        // Ensure we start with am empty webchat settings table
        WebchatSetting::truncate();
    }

    public function testWebchatSettingsViewEndpoint()
    {
        Artisan::call('webchat:settings');

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
        $this->app['config']->set(
            'opendialog.webchat_setting',
            [
                WebchatSetting::GENERAL => [
                    WebchatSetting::URL => [
                        WebchatSetting::DISPLAY_NAME => 'URL',
                        WebchatSetting::DISPLAY => false,
                        WebchatSetting::DESCRIPTION => 'The URL the bot is hosted at',
                        WebchatSetting::TYPE => WebchatSetting::STRING,
                    ],
                    WebchatSetting::TEAM_NAME => [
                        WebchatSetting::DISPLAY_NAME => 'Chatbot Name',
                        WebchatSetting::DESCRIPTION => 'The name displayed in the chatbot header',
                        WebchatSetting::TYPE => WebchatSetting::STRING,
                        WebchatSetting::SECTION => "General Settings",
                        WebchatSetting::SUBSECTION => 'Header',
                        WebchatSetting::SIBLING => WebchatSetting::LOGO
                    ],
                    WebchatSetting::LOGO => [
                        WebchatSetting::DISPLAY_NAME => 'Logo',
                        WebchatSetting::DESCRIPTION => 'The chatbot logo displayed in the header',
                        WebchatSetting::TYPE => WebchatSetting::STRING,
                        WebchatSetting::SECTION => "General Settings",
                        WebchatSetting::SUBSECTION => 'Header',
                        WebchatSetting::SIBLING => WebchatSetting::TEAM_NAME
                    ]
                ]
            ]
        );

        $this->artisan('webchat:settings');

        $response = $this->actingAs($this->user, 'api')
            ->json('GET', '/admin/api/webchat-setting')
            ->assertStatus(200)
            ->assertJsonFragment(
                [
                    'section' => 'General Settings',
                ]
            )
            ->assertJsonFragment(
                [
                    'subsection' => 'Header',
                ]
            )
            ->assertJsonFragment(
                [
                    'display_name' => 'Logo'
                ]
            )
            ->getContent();

        $this->assertCount(2, json_decode($response, true)[0]['children'][0]['children'][0]);
    }

    public function testWebchatSettingsUpdateEndpoint()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => WebchatSetting::STRING,
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 100,
                'type' => WebchatSetting::NUMBER,
                'name' => 'updated value'
            ])
            ->assertStatus(200);

        $updatedSetting = WebchatSetting::where('type', WebchatSetting::STRING)->first();

        $this->assertEquals($updatedSetting->value, 100);
        $this->assertNotEquals($updatedSetting->name, 'updated value');
        $this->assertNotEquals($updatedSetting->type, WebchatSetting::NUMBER);
    }

    public function testWebchatSettingsUpdateEndpointValidationNumber()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => WebchatSetting::NUMBER,
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 'pippo',
            ])
            ->assertStatus(422);

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
            'type' => WebchatSetting::BOOLEAN,
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => 'pippo',
            ])
            ->assertStatus(422);

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
            'type' => WebchatSetting::COLOUR,
            'value' => '#000000',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => '#00000',
            ])
            ->assertStatus(422);

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
            'type' => WebchatSetting::STRING,
            'value' => 'test',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(9000),
            ])
            ->assertStatus(422);

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
            'type' => WebchatSetting::OBJECT,
            'value' => '',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(10),
            ])
            ->assertStatus(422);
    }

    public function testWebchatSettingsUpdateEndpointValidationMap()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => WebchatSetting::MAP,
            'value' => json_encode([
                'key' => 'value',
            ]),
        ]);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => Str::random(10),
            ])
            ->assertStatus(422);

        $this->actingAs($this->user, 'api')
            ->json('PATCH', '/admin/api/webchat-setting/' . $setting->id, [
                'value' => json_encode($setting),
            ])
            ->assertStatus(200);
    }

    public function testWebchatSettingsStoreEndpoint()
    {
        $this->actingAs($this->user, 'api')
            ->json('POST', '/admin/api/webchat-setting', [
                'type' => WebchatSetting::STRING,
                'name' => 'new setting',
                'value' => 'test',
            ])
            ->assertStatus(405);
    }

    public function testWebchatSettingsDestroyEndpoint()
    {
        $setting = WebchatSetting::create([
            'name' => 'testSetting',
            'type' => WebchatSetting::NUMBER,
            'value' => '0',
        ]);

        $this->actingAs($this->user, 'api')
            ->json('DELETE', '/admin/api/webchat-setting/' . $setting->id)
            ->assertStatus(405);
    }

    public function testMultiUpdate()
    {
        $setting1 = WebchatSetting::create([
            'name' => 'setting 1',
            'type' => WebchatSetting::NUMBER,
            'value' => 1,
        ]);

        $setting2 = WebchatSetting::create([
            'name' => 'setting 2',
            'type' => WebchatSetting::COLOUR,
            'value' => '#fff'
        ]);

        $setting3 = WebchatSetting::create([
            'name' => 'setting 3',
            'type' => WebchatSetting::BOOLEAN,
            'value' => true
        ]);

        // All invalid
        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/webchat-setting/', [
                [
                    'name' => 'setting 1',
                    'value' => 'not a number'
                ],
                [
                    'name' => 'setting 2',
                    'value' => 'not a colour'
                ],
                [
                    'name' => 'setting 3',
                    'value' => 'not a boolean'
                ]
            ])
            ->assertStatus(422);

        // Nothing updated
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting1->value]);
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting2->value]);
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting3->value]);

        // Only 1 invalid
        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/webchat-setting/', [
                [
                    'name' => 'setting 1',
                    'value' => 1
                ],
                [
                    'name' => 'setting 2',
                    'value' => '#000'
                ],
                [
                    'name' => 'setting 3',
                    'value' => 'not a boolean'
                ]
            ])
            ->assertStatus(422);

        // Nothing updated
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting1->value]);
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting2->value]);
        $this->assertDatabaseHas('webchat_settings', ['value' => $setting3->value]);

        // all valid
        $this->actingAs($this->user, 'api')
            ->json('PUT', '/admin/api/webchat-setting/', [
                [
                    'name' => 'setting 1',
                    'value' => 1
                ],
                [
                    'name' => 'setting 2',
                    'value' => '#000'
                ],
                [
                    'name' => 'setting 3',
                    'value' => false
                ]
            ])
            ->assertStatus(200);

        // Nothing updated
        $this->assertDatabaseHas('webchat_settings', ['value' => '1']);
        $this->assertDatabaseHas('webchat_settings', ['value' => '#000']);
        $this->assertDatabaseHas('webchat_settings', ['value' => "0"]);
    }
}
