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
    protected $signature = 'webchat:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the webchat_settings table with the correct settings for the opendialog project. It calls
    the webchat:settings command from the OpenDialogAi-Webchat package to create the correct rows in the table, then
    populates the settings required for the opendialog project. If auditAppUrl, openDialogUrl and authToken are not included in
    the call, the existing settings from the database will be used if they exist.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // First, run the OpenDialogAI-Webchat settings command to get our database in order
        $this->info('Setting up webchat settings table...');
        Artisan::call('webchat:settings');
        $this->info('...done');

        $odUrl = env('APP_URL');
        $commentsUrl = 'http://example.com';
        $token = 'ApiTokenValue';

        $settings = [
            WebchatSetting::URL => "$odUrl/web-chat",
            WebchatSetting::HIDE_OPEN_CLOSE_ICONS => true,
            WebchatSetting::OPEN => true,
            WebchatSetting::TEAM_NAME => 'OpenDialog Webchat',
            WebchatSetting::MESSAGE_DELAY => '1000',
            WebchatSetting::COLOURS => 'colours',
            WebchatSetting::HEADER_BACKGROUND => '#0000FF',
            WebchatSetting::CHATBOT_AVATAR_PATH => "$odUrl/images/logo.svg",
            WebchatSetting::CHATBOT_NAME => 'OpenDialog',
            WebchatSetting::USE_HUMAN_AVATAR => true,
            WebchatSetting::USE_BOT_AVATAR => true,
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
        ];

        foreach ($settings as $name => $value) {
            $this->updateSetting($name, $value);
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
