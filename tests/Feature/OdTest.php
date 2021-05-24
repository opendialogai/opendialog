<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\AttributeEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\Contracts\ContextService;
use OpenDialogAi\Core\InterpreterEngine\Service\ConfiguredInterpreterServiceInterface;
use OpenDialogAi\Core\InterpreterEngine\Service\InterpreterServiceInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterComponentServiceInterface;
use OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface;
use OpenDialogAi\SensorEngine\Service\SensorService;
use OpenDialogAi\Webchat\Console\Commands\WebchatSettings;
use OpenDialogAi\Webchat\WebchatSetting;
use Tests\TestCase;

class OdTest extends TestCase
{
    public function setup(): void
    {
        parent::setUp();
        Artisan::call('configurations:create');

        $this->app->forgetInstance(ConfiguredInterpreterServiceInterface::class);
        $this->app->forgetInstance(InterpreterComponentServiceInterface::class);

        $this->webchatSetup();
    }

    /**
     * Verify that the demo endpoint is present.
     *
     * @return void
     */
    public function testDemoEndpoint()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/admin/demo');

        $response->assertStatus(200);
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
        ], false);
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
            WebchatSetting::GENERAL => []
        ]);
    }

    /**
     * Verify that the OD-Core service providers are available.
     *
     * @return void
     */
    public function testOdCoreServiceProviders()
    {
        $actionEngine = resolve(ActionEngineInterface::class);
        $this->assertInstanceOf(ActionEngineInterface::class, $actionEngine);

        $contextService = resolve(ContextService::class);
        $this->assertInstanceOf(ContextService::class, $contextService);

        $attributeResolver = resolve(AttributeResolver::class);
        $this->assertInstanceOf(AttributeResolver::class, $attributeResolver);

        $interpreterService = resolve(InterpreterServiceInterface::class);
        $this->assertInstanceOf(InterpreterServiceInterface::class, $interpreterService);

        $responseEngineService = resolve(ResponseEngineServiceInterface::class);
        $this->assertInstanceOf(ResponseEngineServiceInterface::class, $responseEngineService);

        $sensorService = resolve(SensorService::class);
        $this->assertInstanceOf(SensorService::class, $sensorService);
    }

    /**
     * Verify that the OD-Webchat service provider is available.
     *
     * @return void
     */
    public function testOdWebchatServiceProvider()
    {
        $webChatSettings = app('OpenDialogAi\Webchat\Console\Commands\WebchatSettings');
        $this->assertInstanceOf(WebchatSettings::class, $webChatSettings);
    }
}
