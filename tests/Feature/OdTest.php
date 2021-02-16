<?php

namespace Tests\Feature;

use App\User;
use OpenDialogAi\ActionEngine\Service\ActionEngineInterface;
use OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver;
use OpenDialogAi\ContextEngine\ContextManager\ContextService;
use OpenDialogAi\ContextEngine\Contexts\User\UserService;
use OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface;
use OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface;
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
            WebchatSetting::GENERAL => [
                WebchatSetting::TEAM_NAME => 'OpenDialog',
                WebchatSetting::USE_HUMAN_AVATAR => false,
                WebchatSetting::USE_BOT_AVATAR => true,
            ]
        ]);
    }

    /**
     * Verify that the OD-Core service providers are available.
     *
     * @return void
     */
    public function testOdCoreServiceProviders()
    {
        $actionEngine = app('OpenDialogAi\ActionEngine\Service\ActionEngineInterface');
        $this->assertInstanceOf(ActionEngineInterface::class, $actionEngine);

        $contextService = app('OpenDialogAi\ContextEngine\ContextManager\ContextService');
        $this->assertInstanceOf(ContextService::class, $contextService);
        $attributeResolver = app('OpenDialogAi\ContextEngine\AttributeResolver\AttributeResolver');
        $this->assertInstanceOf(AttributeResolver::class, $attributeResolver);
        $userService = app('OpenDialogAi\ContextEngine\Contexts\User\UserService');
        $this->assertInstanceOf(UserService::class, $userService);

        $conversationStore = app('OpenDialogAi\ConversationEngine\ConversationStore\ConversationStoreInterface');
        $this->assertInstanceOf(ConversationStoreInterface::class, $conversationStore);

        $interpreterService = app('OpenDialogAi\InterpreterEngine\Service\InterpreterServiceInterface');
        $this->assertInstanceOf(InterpreterServiceInterface::class, $interpreterService);

        $responseEngineService = app('OpenDialogAi\ResponseEngine\Service\ResponseEngineServiceInterface');
        $this->assertInstanceOf(ResponseEngineServiceInterface::class, $responseEngineService);

        $sensorService = app('OpenDialogAi\SensorEngine\Service\SensorService');
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
