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
        $auditUrl = env('OPENDIALOG_AUDIT_URL');
        $token = env('OPENDIALOG_AUDIT_TOKEN');

        $settings = [
            'url' => "$odUrl/web-chat",
            'hideOpenCloseIcons' => true,
            'open' => true,
            'teamName' => 'Lisa',
            'messageDelay' => '1000',
            'colours' => 'colours',
            'headerBackground' => '#98002e',
            'chatbotAvatarPath' => "$odUrl/images/lisa.png",
            'useAvatars' => true,
            'comments' => 'comments',
            'commentsEnabled' => true,
            'commentsName' => 'Comments',
            'commentsEnabledPathPattern' => '^\\/home\\/audits|^\\/home\\/fsas',
            'commentsEntityName' => 'comments',
            'commentsCreatedFieldName' => 'created-at',
            'commentsTextFieldName' => 'comment',
            'commentsAuthorEntityName' => 'users',
            'commentsAuthorRelationshipName' => 'author',
            'commentsAuthorIdFieldName' => 'id',
            'commentsAuthorNameFieldName' => 'name',
            'commentsSectionEntityName' => 'fsas',
            'commentsSectionRelationshipName' => 'fsa',
            'commentsSectionIdFieldName' => 'id',
            'commentsSectionNameFieldName' => 'name',
            'commentsSectionFilterPathPattern' => 'home\\/audits\\/(\\d*)\\/?',
            'commentsSectionFilterQuery' => 'audit',
            'commentsSectionPathPattern' => 'home\\/audits\\/\\d*\\/fsas\\/(\\d*)$',
            'commentsAxiosConfig' => "{\"baseURL\": \"$auditUrl/json-api/v1\",\"headers\": {\"Authorization\": " .
                "\"Bearer $token\",\"Content-Type\": \"application/vnd.api+json\"}}",
            'disableCloseChat' => false,
            'webchatHistory' => 'webchatHistory',
            'showHistory' => true,
            'numberOfMessages' => 10,
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
