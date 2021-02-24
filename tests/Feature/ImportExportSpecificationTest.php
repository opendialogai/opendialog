<?php

namespace Tests\Feature;

use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ConversationEngine\ConversationStore\DGraphConversationQueryFactory;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;
use OpenDialogAi\ResponseEngine\MessageTemplate;

/**
 * Class ImportExportSpecificationTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportSpecificationTest extends BaseSpecificationTest
{
    public function testImportSpecification()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Did not understand']);

        $this->assertEmpty(
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getConversationTemplateIds())
                ->getData()
        );

        Artisan::call(
            'specification:import',
            [
                '--yes' => true,
                '--activate' => true,
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);

        $this->assertCount(
            2,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getConversationTemplateIds())
                ->getData()
        );

        $this->assertCount(
            2,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getAllOpeningIntents())
                ->getData()
        );

        Artisan::call(
            'specification:import',
            [
                '--yes' => true,
                '--activate' => true,
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);

        // The previously imported conversation should still be there, just deactivated
        $this->assertCount(
            4,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getConversationTemplateIds())
                ->getData()
        );

        $this->assertCount(
            2,
            resolve(DGraphClient::class)
                ->query(DGraphConversationQueryFactory::getAllOpeningIntents())
                ->getData()
        );
    }

    public function testExportSpecification()
    {
        $this->assertDatabaseMissing('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Did not understand']);

        Artisan::call(
            'specification:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('conversations', ['name' => 'no_match_conversation']);
        $this->assertDatabaseHas('outgoing_intents', ['name' => 'intent.core.NoMatchResponse']);
        $this->assertDatabaseHas('message_templates', ['name' => 'Did not understand']);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where('name', 'Did not understand')->first();
        $markup = "<message><empty-message></empty-message></message>";
        $messageTemplate->message_markup = $markup;
        $messageTemplate->save();

        Artisan::call(
            'specification:export',
            [
                '--yes' => true
            ]
        );

        $messageFileName = MessageImportExportHelper::addMessageFileExtension($messageTemplate->name);
        $filename = MessageImportExportHelper::getMessagePath($messageFileName);
        $message = $this->disk->get($filename);
        $this->assertStringContainsString($markup, $message);
    }
}
