<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use OpenDialogAi\Webchat\WebchatSetting;

class SetWebchatSettingsTest extends TestCase
{
    public function testCommand()
    {
        // Confirm that settings are not set before the command is run
        $this->assertNull(WebchatSetting::first());

        // Run the command
        Artisan::call('webchat:setup');

        // Ensure that settings are set after the command is run
        $this->assertNotNull(WebchatSetting::first());

        // Get specific settings
        $chatbotName = WebchatSetting::where('name', WebchatSetting::CHATBOT_NAME)->first();
        $url = WebchatSetting::where('name', WebchatSetting::URL)->first();
        $useBotAvatar = WebchatSetting::where('name', WebchatSetting::USE_BOT_AVATAR)->first();
        $useHumanAvatar = WebchatSetting::where('name', WebchatSetting::USE_HUMAN_AVATAR)->first();

        // Ensure they aren't null
        $this->assertNotNull($chatbotName);
        $this->assertNotNull($url);
        $this->assertNotNull($useBotAvatar);
        $this->assertNotNull($useHumanAvatar);

        // Ensure they are of the expected type
        $this->assertEquals(WebchatSetting::STRING, $chatbotName->type);
        $this->assertEquals(WebchatSetting::STRING, $url->type);
        $this->assertEquals(WebchatSetting::BOOLEAN, $useBotAvatar->type);
        $this->assertEquals(WebchatSetting::BOOLEAN, $useHumanAvatar->type);
    }
}
