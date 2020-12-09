<?php

namespace Tests\Feature;

use App\Console\Commands\Specification\BaseSpecificationCommand;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;

/**
 * Class ImportExportMessagesTest
 * @package Tests\Feature
 * @group SpecificationTests
 */
class ImportExportMessagesTest extends BaseSpecificationTest
{
    public function testImportMessages()
    {
        $messageData = $this->disk->get(BaseSpecificationCommand::getMessagePath('Did not understand.message.xml'));
        $messageXml = new \SimpleXMLElement($messageData);
        $intentName = (string) $messageXml->intent;
        $messageTemplateName = (string) $messageXml->name;
        $markup = $messageXml->markup->message->asXML();

        $this->assertDatabaseMissing('outgoing_intents', ['name' => $intentName]);
        $this->assertDatabaseMissing('message_templates', ['name' => $messageTemplateName]);

        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('outgoing_intents', ['name' => $intentName]);
        $this->assertDatabaseHas('message_templates', ['name' => $messageTemplateName]);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where(['name' => $messageTemplateName])->first();
        $this->assertEquals($markup, $messageTemplate->message_markup);
    }

    public function testImportSingleMessage()
    {
        $messageData = $this->disk->get(BaseSpecificationCommand::getMessagePath('Did not understand.message.xml'));
        $messageXml = new \SimpleXMLElement($messageData);
        $intentName = (string) $messageXml->intent;
        $messageTemplateName = (string) $messageXml->name;
        $markup = $messageXml->markup->message->asXML();

        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Welcome']);

        $this->assertDatabaseMissing('outgoing_intents', ['name' => $intentName]);
        $this->assertDatabaseMissing('message_templates', ['name' => $messageTemplateName]);

        Artisan::call(
            'messages:import',
            [
                'message' => 'Did not understand',
                '--yes' => true
            ]
        );

        // We didn't import welcome message so it still shouldn't be there
        $this->assertDatabaseMissing('outgoing_intents', ['name' => 'intent.opendialog.WelcomeResponse']);
        $this->assertDatabaseMissing('message_templates', ['name' => 'Welcome']);

        $this->assertDatabaseHas('outgoing_intents', ['name' => $intentName]);
        $this->assertDatabaseHas('message_templates', ['name' => $messageTemplateName]);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where(['name' => $messageTemplateName])->first();
        $this->assertEquals($markup, $messageTemplate->message_markup);
    }

    public function testImportSingleMessageContaingAttribute()
    {
        $messageTemplateName = 'Attribute';
        $messageTemplateFileName = BaseSpecificationCommand::addMessageFileExtension($messageTemplateName);
        $messageTemplateFilePath = BaseSpecificationCommand::getMessagePath($messageTemplateFileName);
        $messageData = $this->disk->get($messageTemplateFilePath);
        $messageXml = new \SimpleXMLElement($messageData);
        $markup = $messageXml->markup->message->asXML();

        $this->assertDatabaseMissing('message_templates', ['name' => $messageTemplateName]);

        Artisan::call(
            'messages:import',
            [
                'message' => $messageTemplateName,
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('message_templates', ['name' => $messageTemplateName]);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::where(['name' => $messageTemplateName])->first();
        $this->assertEquals($markup, $messageTemplate->message_markup);
    }

    public function testExportMessages()
    {
        $welcomeMessageFileName = BaseSpecificationCommand::addMessageFileExtension('Welcome');
        $welcomeMessageFilePath = BaseSpecificationCommand::getMessagePath($welcomeMessageFileName);
        $welcomeMessageData = $this->disk->get($welcomeMessageFilePath);
        $welcomeMessageXml = new \SimpleXMLElement($welcomeMessageData);
        $welcomeMessageTemplateName = (string) $welcomeMessageXml->name;

        $noMatchMessageFileName = BaseSpecificationCommand::addMessageFileExtension('Did not understand');
        $noMatchMessageFilePath = BaseSpecificationCommand::getMessagePath($noMatchMessageFileName);
        $noMatchMessageData = $this->disk->get($noMatchMessageFilePath);
        $noMatchMessageXml = new \SimpleXMLElement($noMatchMessageData);
        $noMatchMessageTemplateName = (string) $noMatchMessageXml->name;

        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('message_templates', ['name' => $welcomeMessageTemplateName]);
        $this->assertDatabaseHas('message_templates', ['name' => $noMatchMessageTemplateName]);

        $welcomeMessageTemplate = MessageTemplate::where('name', $welcomeMessageTemplateName)->first();
        $welcomeNewMessageTemplate = $welcomeMessageTemplate->name . "Export";
        $welcomeMessageTemplate->name = $welcomeNewMessageTemplate;
        $welcomeNewMarkup = '<text-message>WelcomeExport';
        $welcomeMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $welcomeNewMarkup,
            $welcomeMessageTemplate->message_markup
        );
        $welcomeMessageTemplate->save();

        $noMatchMessageTemplate = MessageTemplate::where('name', $noMatchMessageTemplateName)->first();
        $noMatchNewMarkup = '<text-message>NoMatchExport';
        $noMatchMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $noMatchNewMarkup,
            $noMatchMessageTemplate->message_markup
        );
        $noMatchMessageTemplate->save();

        Artisan::call(
            'messages:export',
            [
                '--yes' => true
            ]
        );

        $welcomeNewMessageFilePath = BaseSpecificationCommand::addMessageFileExtension(BaseSpecificationCommand::getMessagePath($welcomeNewMessageTemplate));

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists($welcomeNewMessageFilePath);
        $this->disk->assertExists($welcomeMessageFilePath);

        $this->disk->assertExists($noMatchMessageFilePath);

        $welcomeMessage = $this->disk->get($welcomeNewMessageFilePath);
        $noMatchMessage = $this->disk->get($noMatchMessageFilePath);

        $this->assertStringContainsString($welcomeNewMarkup, $welcomeMessage);
        $this->assertStringContainsString($noMatchNewMarkup, $noMatchMessage);
    }

    public function testExportSingleMessage()
    {
        $welcomeMessageFileName = BaseSpecificationCommand::addMessageFileExtension('Welcome');
        $welcomeMessageData = $this->disk->get(BaseSpecificationCommand::getMessagePath($welcomeMessageFileName));
        $welcomeMessageXml = new \SimpleXMLElement($welcomeMessageData);
        $welcomeMessageTemplateName = (string) $welcomeMessageXml->name;

        $noMatchMessageFileName = BaseSpecificationCommand::addMessageFileExtension('Did not understand');
        $noMatchMessageData = $this->disk->get(BaseSpecificationCommand::getMessagePath($noMatchMessageFileName));
        $noMatchMessageXml = new \SimpleXMLElement($noMatchMessageData);
        $noMatchMessageTemplateName = (string) $noMatchMessageXml->name;

        Artisan::call(
            'messages:import',
            [
                '--yes' => true
            ]
        );

        $this->assertDatabaseHas('message_templates', ['name' => $welcomeMessageTemplateName]);
        $this->assertDatabaseHas('message_templates', ['name' => $noMatchMessageTemplateName]);

        // Make a change to the welcome message, even though we won't export it
        $welcomeMessageTemplate = MessageTemplate::where('name', $welcomeMessageTemplateName)->first();
        $welcomeNewMarkup = '<text-message>WelcomeExport';
        $welcomeOriginalMarkup = $welcomeMessageTemplate->message_markup;
        $welcomeMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $welcomeNewMarkup,
            $welcomeMessageTemplate->message_markup
        );
        $welcomeMessageTemplate->save();

        $noMatchMessageTemplate = MessageTemplate::where('name', $noMatchMessageTemplateName)->first();
        $noMatchNewMarkup = '<text-message>NoMatchExport';
        $noMatchMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $noMatchNewMarkup,
            $noMatchMessageTemplate->message_markup
        );
        $noMatchMessageTemplate->save();

        Artisan::call(
            'messages:export',
            [
                'message' => $noMatchMessageTemplateName,
                '--yes' => true
            ]
        );

        $welcomeFileName = BaseSpecificationCommand::getMessagePath($welcomeMessageFileName);
        $welcomeMessage = $this->disk->get($welcomeFileName);

        $noMatchFileName = BaseSpecificationCommand::getMessagePath($noMatchMessageFileName);
        $noMatchMessage = $this->disk->get($noMatchFileName);

        // We didn't export the welcome message change so it should be as it was prior the the database change
        $this->assertStringContainsString($welcomeOriginalMarkup, $welcomeMessage);
        $this->assertStringContainsString($noMatchNewMarkup, $noMatchMessage);
    }

    public function testExportSingleMessageContainingAttribute()
    {
        $messageTemplateName = 'AttributeMessage';
        $messageTemplateFileName = BaseSpecificationCommand::addMessageFileExtension($messageTemplateName);
        $messageTemplateFilePath = BaseSpecificationCommand::getMessagePath($messageTemplateFileName);

        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::create(['name' => 'intent.app.attributeResponse']);

        /** @var MessageTemplate $messageTemplate */
        $messageTemplate = MessageTemplate::make();
        $messageTemplate->name = $messageTemplateName;
        $markup = "<message><text-message>Testing...</text-message>{session.my_attribute}</message>";
        $messageTemplate->message_markup = $markup;
        $messageTemplate->outgoing_intent_id = $outgoingIntent->id;
        $messageTemplate->save();

        Artisan::call(
            'messages:export',
            [
                'message' => $messageTemplateName,
                '--yes' => true
            ]
        );

        $message = $this->disk->get($messageTemplateFilePath);
        $this->assertStringContainsString($markup, $message);
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

        $filename = BaseSpecificationCommand::getMessagePath("Did not understand.message.xml");

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
