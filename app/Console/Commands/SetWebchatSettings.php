<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Webchat\WebchatSetting;

class SetWebchatSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webchat:setup {settings?} {--url=} {--non-interactive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the webchat_settings table with the correct settings for the opendialog project. It calls
    the webchat:settings command from the OpenDialogAi-Webchat package to create the correct rows in the table, then
    populates the settings required for the opendialog project. An optional list of settings to be updated can be passed in to 
    update only those settings';

    public function handle()
    {
        $settingsInput = explode(',', $this->argument('settings'));
        $customSettings = !empty($settingsInput);

        if (!$this->option('non-interactive')) {
            if (!$customSettings && !$this->confirm('This will overwrite all current settings - are you sure you want to run?')) {
                $this->info('OK, not running');
                return 1;
            } elseif ($customSettings && !$this->confirm(
                sprintf('This will update all of these settings - are you sure: %s', implode(',', $settingsInput))
            )
            ) {
                $this->info('OK, not running');
                return 1;
            }
        }

        // First, run the OpenDialogAI-Webchat settings command to get our database in order
        $this->info('Setting up webchat settings table...');
        Artisan::call('webchat:settings');
        $this->info('...done');

        $odUrl = $this->option('url') ? $this->option('url') : env('APP_URL');

        $commentsUrl = 'http://example.com';
        $token = 'ApiTokenValue';

        $settings = [
            WebchatSetting::URL => "$odUrl/web-chat",
            WebchatSetting::OPEN => true,
            WebchatSetting::TEAM_NAME => "",
            WebchatSetting::LOGO => "$odUrl/images/homepage-logo.svg",
            WebchatSetting::MESSAGE_DELAY => '500',
            WebchatSetting::COLOURS => 'colours',
            WebchatSetting::HEADER_BACKGROUND => '#1b2956',
            WebchatSetting::HEADER_TEXT => '#ffffff',
            WebchatSetting::LAUNCHER_BACKGROUND => '#1b2956',
            WebchatSetting::MESSAGE_LIST_BACKGROUND => '#1b2956',
            WebchatSetting::SENT_MESSAGE_BACKGROUND => '#7fdad1',
            WebchatSetting::SENT_MESSAGE_TEXT => '#1b2956',
            WebchatSetting::RECEIVED_MESSAGE_BACKGROUND => '#ffffff',
            WebchatSetting::RECEIVED_MESSAGE_TEXT => '#1b2956',
            WebchatSetting::USER_INPUT_BACKGROUND => '#ffffff',
            WebchatSetting::USER_INPUT_TEXT => '#1b212a',
            WebchatSetting::ICON_BACKGROUND => '0000ff',
            WebchatSetting::ICON_HOVER_BACKGROUND => 'ffffff',
            WebchatSetting::BUTTON_BACKGROUND => '#7fdad1',
            WebchatSetting::BUTTON_HOVER_BACKGROUND => '#7fdad1',
            WebchatSetting::BUTTON_TEXT => '#1b2956',
            WebchatSetting::EXTERNAL_BUTTON_BACKGROUND => '#7fdad1',
            WebchatSetting::EXTERNAL_BUTTON_HOVER_BACKGROUND => '#7fdad1',
            WebchatSetting::EXTERNAL_BUTTON_TEXT => '#1b2956',
            WebchatSetting::CHATBOT_AVATAR_PATH => "$odUrl/vendor/webchat/images/avatar.svg",
            WebchatSetting::CHATBOT_NAME => 'OpenDialog',
            WebchatSetting::USE_HUMAN_AVATAR => false,
            WebchatSetting::USE_HUMAN_NAME => false,
            WebchatSetting::USE_BOT_AVATAR => true,
            WebchatSetting::USE_BOT_NAME => false,
            WebchatSetting::CHATBOT_FULLPAGE_CSS_PATH => "",
            WebchatSetting::CHATBOT_CSS_PATH => "",
            WebchatSetting::PAGE_CSS_PATH => "",
            WebchatSetting::SHOW_TEXT_INPUT_WITH_EXTERNAL_BUTTONS => false,
            WebchatSetting::FORM_RESPONSE_TEXT => null,
            WebchatSetting::COMMENTS => 'comments',
            WebchatSetting::COMMENTS_ENABLED => false,
            WebchatSetting::COMMENTS_NAME => 'Comments',
            WebchatSetting::COMMENTS_ENABLED_PATH_PATTERN => '^\\/home\\/posts',
            WebchatSetting::COMMENTS_ENTITY_NAME => 'comments',
            WebchatSetting::COMMENTS_CREATED_FIELDNAME => 'created-at',
            WebchatSetting::COMMENTS_TEXT_FIELDNAME => 'comment',
            WebchatSetting::COMMENTS_AUTHOR_ENTITY_NAME => 'users',
            WebchatSetting::COMMENTS_AUTHOR_RELATIONSHIP_NAME => 'author',
            WebchatSetting::COMMENTS_AUTHOR_ID_FIELDNAME => 'id',
            WebchatSetting::COMMENTS_AUTHOR_NAME_FIELDNAME => 'name',
            WebchatSetting::COMMENTS_SECTION_ENTITY_NAME => 'posts',
            WebchatSetting::COMMENTS_SECTION_RELATIONSHIP_NAME => 'post',
            WebchatSetting::COMMENTS_SECTION_ID_FIELDNAME => 'id',
            WebchatSetting::COMMENTS_SECTION_NAME_FIELDNAME => 'name',
            WebchatSetting::COMMENTS_SECTION_FILTER_PATH_PATTERN => 'home\\/posts\\/(\\d*)\\/?',
            WebchatSetting::COMMENTS_SECTION_FILTER_QUERY => 'post',
            WebchatSetting::COMMENTS_SECTION_PATH_PATTERN => 'home\\/posts\\/\\d*$',
            WebchatSetting::COMMENTS_ENDPOINT => "$commentsUrl/json-api/v1",
            WebchatSetting::COMMENTS_AUTH_TOKEN => "Bearer $token",
            WebchatSetting::DISABLE_CLOSE_CHAT => false,
            WebchatSetting::WEBCHAT_HISTORY => 'webchatHistory',
            WebchatSetting::SHOW_HISTORY => true,
            WebchatSetting::NUMBER_OF_MESSAGES => 10,
            WebchatSetting::COLLECT_USER_IP => true,
            WebchatSetting::SHOW_RESTART_BUTTON => false,
            WebchatSetting::SHOW_DOWNLOAD_BUTON => true,
            WebchatSetting::SHOW_END_CHAT_BUTON => false,
            WebchatSetting::HIDE_DATETIME_MESSAGE => true,
            WebchatSetting::RESTART_BUTTON_CALLBACK => 'WELCOME',
            WebchatSetting::MESSAGE_ANIMATION => false,
            WebchatSetting::HIDE_TYPING_INDICATOR_ON_INTERNAL_MESSAGES => false,
            WebchatSetting::HIDE_MESSAGE_TIME => true,

            WebchatSetting::NEW_USER_START_MINIMIZED => false,
            WebchatSetting::RETURNING_USER_START_MINIMIZED => false,
            WebchatSetting::ONGOING_USER_START_MINIMIZED => false,
            WebchatSetting::NEW_USER_OPEN_CALLBACK => 'WELCOME',
            WebchatSetting::RETURNING_USER_OPEN_CALLBACK => 'WELCOME',
            WebchatSetting::ONGOING_USER_OPEN_CALLBACK => '',

            WebchatSetting::VALID_PATH => '["*"]',
        ];

        foreach ($settings as $name => $value) {
            if (!$customSettings || in_array($name, $settingsInput)) {
                $this->updateSetting($name, $value);
                if (($key = array_search($name, $settingsInput)) !== false) {
                    unset($settingsInput[$key]);
                }
            }
        }

        if (!empty($settingsInput)) {
            $this->info(sprintf('Settings not updated for %s - name(s) not valid', implode(',', $settingsInput)));
        }
    }

    private function updateSetting($settingName, $value)
    {
        $this->info("Setting $settingName to $value");
        /** @var WebchatSetting $setting */
        $setting = WebchatSetting::where('name', $settingName)
            ->first();

        $setting->value = $value;

        $setting->save();
    }
}
