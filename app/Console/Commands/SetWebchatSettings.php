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
     * @return mixed
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
            'url' => "$odUrl/web-chat",
            'hideOpenCloseIcons' => true,
            'open' => true,
            'teamName' => 'OpenDialog Webchat',
            'messageDelay' => '1000',
            'colours' => 'colours',
            'headerBackground' => '#0000FF',
            'chatbotAvatarPath' => "$odUrl/images/logo.svg",
            'chatbotName' => 'OpenDialog',
            'useHumanAvatar' => true,
            'useBotAvatar' => true,
            'comments' => 'comments',
            'commentsEnabled' => false,
            'commentsName' => 'Comments',
            'commentsEnabledPathPattern' => '^\\/home\\/posts',
            'commentsEntityName' => 'comments',
            'commentsCreatedFieldName' => 'created-at',
            'commentsTextFieldName' => 'comment',
            'commentsAuthorEntityName' => 'users',
            'commentsAuthorRelationshipName' => 'author',
            'commentsAuthorIdFieldName' => 'id',
            'commentsAuthorNameFieldName' => 'name',
            'commentsSectionEntityName' => 'posts',
            'commentsSectionRelationshipName' => 'post',
            'commentsSectionIdFieldName' => 'id',
            'commentsSectionNameFieldName' => 'name',
            'commentsSectionFilterPathPattern' => 'home\\/posts\\/(\\d*)\\/?',
            'commentsSectionFilterQuery' => 'post',
            'commentsSectionPathPattern' => 'home\\/posts\\/\\d*$',
            'commentsEndpoint' => "$commentsUrl/json-api/v1",
            'commentsAuthToken' => "Bearer $token",
            'disableCloseChat' => false,
            'webchatHistory' => 'webchatHistory',
            'showHistory' => true,
            'numberOfMessages' => 10,
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
