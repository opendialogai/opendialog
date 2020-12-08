<?php

namespace Tests\Feature;

use App\Console\Commands\Specification\BaseSpecificationCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\AbstractAdapter;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use Tests\TestCase;

/**
 * Class ImportExportMessagesTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportMessagesTest extends TestCase
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    private $disk;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call(
            'schema:init',
            [
                '--yes' => true
            ]
        );

        $this->disk = Storage::fake('specifications');

        /** @var AbstractAdapter $diskAdapter */
        $diskAdapter = $this->disk->getAdapter();
        File::copyDirectory(resource_path('specifications'), $diskAdapter->getPathPrefix());
    }

    public function testImportMessages()
    {
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
    }

    public function testExportMessages()
    {
        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);

        $messageTemplate = MessageTemplate::where('name', 'Did not understand')->first();
        $messageTemplate->message_markup = str_replace('<text-message>', '<text-message>Export', $messageTemplate->message_markup);
        $messageTemplate->save();

        Artisan::call(
            'messages:export',
            [
                '--yes' => true
            ]
        );

        $messageFileName = "$messageTemplate->name.message";
        $filename = BaseSpecificationCommand::getMessagePath($messageFileName);
        $message = $this->disk->get($filename);
        $this->assertStringContainsString('<text-message>Export', $message);
    }

    public function testUpdateMessages()
    {
        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);

        $filename = BaseSpecificationCommand::getMessagePath("Did not understand.message");

        $message = $this->disk->get($filename);
        $message = str_replace('<text-message>', '<text-message>Export', $message);
        $this->disk->put($filename, $message);

        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $messageTemplate = MessageTemplate::where('name', 'Did not understand')->first();
        $this->assertStringContainsString('<text-message>Export', $messageTemplate->message_markup);
    }
}
