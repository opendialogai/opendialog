<?php

namespace Tests\Feature;

use App\ImportExportHelpers\MessageImportExportHelper;
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
        $messageData = $this->disk->get(MessageImportExportHelper::getMessagePath('Did not understand.message.xml'));
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
        $messageData = $this->disk->get(MessageImportExportHelper::getMessagePath('Did not understand.message.xml'));
        $messageXml = new \SimpleXMLElement($messageData);
        $intentName = (string) $messageXml->intent;
        $messageTemplateName = (string) $messageXml->name;
        $conditions = (string) $messageXml->conditions;
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
        $this->assertEquals($conditions, $messageTemplate->conditions);
    }

    public function testImportSingleMessageContaingAttribute()
    {
        $messageTemplateName = 'Attribute';
        $messageTemplateFileName = MessageImportExportHelper::addMessageFileExtension($messageTemplateName);
        $messageTemplateFilePath = MessageImportExportHelper::getMessagePath($messageTemplateFileName);
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
        $welcomeMessageFileName = MessageImportExportHelper::addMessageFileExtension('Welcome');
        $welcomeMessageFilePath = MessageImportExportHelper::getMessagePath($welcomeMessageFileName);
        $welcomeMessageData = $this->disk->get($welcomeMessageFilePath);
        $welcomeMessageXml = new \SimpleXMLElement($welcomeMessageData);
        $welcomeMessageTemplateName = (string) $welcomeMessageXml->name;

        $noMatchMessageFileName = MessageImportExportHelper::addMessageFileExtension('Did not understand');
        $noMatchMessageFilePath = MessageImportExportHelper::getMessagePath($noMatchMessageFileName);
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

        $welcomeNewMessageFilePath = MessageImportExportHelper::addMessageFileExtension(MessageImportExportHelper::getMessagePath($welcomeNewMessageTemplate));

        // This command is not destructive so the original should remain as well
        $this->disk->assertExists($welcomeNewMessageFilePath);
        $this->disk->assertExists($welcomeMessageFilePath);

        $this->disk->assertExists($noMatchMessageFilePath);

        $welcomeMessage = $this->disk->get($welcomeNewMessageFilePath);
        $noMatchMessage = $this->disk->get($noMatchMessageFilePath);

        $this->assertStringContainsString($welcomeNewMarkup, $welcomeMessage);
        $this->assertStringContainsString("<intent>$welcomeMessageXml->intent</intent>", $welcomeMessage);
        $this->assertStringContainsString("<name>$welcomeNewMessageTemplate</name>", $welcomeMessage);

        $this->assertStringContainsString($noMatchNewMarkup, $noMatchMessage);
        $this->assertStringContainsString("<intent>$noMatchMessageXml->intent</intent>", $noMatchMessage);
        $this->assertStringContainsString("<name>$noMatchMessageTemplateName</name>", $noMatchMessage);
    }

    public function testExportSingleMessage()
    {
        $welcomeMessageFileName = MessageImportExportHelper::addMessageFileExtension('Welcome');
        $welcomeMessageData = $this->disk->get(MessageImportExportHelper::getMessagePath($welcomeMessageFileName));
        $welcomeMessageXml = new \SimpleXMLElement($welcomeMessageData);
        $welcomeMessageTemplateName = (string) $welcomeMessageXml->name;

        $noMatchMessageFileName = MessageImportExportHelper::addMessageFileExtension('Did not understand');
        $noMatchMessageData = $this->disk->get(MessageImportExportHelper::getMessagePath($noMatchMessageFileName));
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
        /** @var MessageTemplate $welcomeMessageTemplate */
        $welcomeMessageTemplate = MessageTemplate::where('name', $welcomeMessageTemplateName)->first();
        $welcomeNewMarkup = '<text-message>WelcomeExport';
        $welcomeOriginalMarkup = $welcomeMessageTemplate->message_markup;
        $welcomeMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $welcomeNewMarkup,
            $welcomeMessageTemplate->message_markup
        );
        $welcomeMessageTemplate->conditions = <<<EOT
conditions:
  - condition:
      operation: test_operation
      attributes:
        attribute: session.test_attribute
      parameters:
        value: true
EOT;
        $welcomeMessageTemplate->save();

        /** @var MessageTemplate $noMatchMessageTemplate */
        $noMatchMessageTemplate = MessageTemplate::where('name', $noMatchMessageTemplateName)->first();
        $noMatchNewMarkup = '<text-message>NoMatchExport';
        $noMatchMessageTemplate->message_markup = str_replace(
            '<text-message>',
            $noMatchNewMarkup,
            $noMatchMessageTemplate->message_markup
        );
        $noMatchNewConditions = <<<EOT
conditions:
  - condition:
      operation: test_operation
      attributes:
        attribute: session.test_attribute
      parameters:
        value: true
EOT;
        $noMatchMessageTemplate->conditions = $noMatchNewConditions;

        $noMatchMessageTemplate->save();

        Artisan::call(
            'messages:export',
            [
                'message' => $noMatchMessageTemplateName,
                '--yes' => true
            ]
        );

        $welcomeFileName = MessageImportExportHelper::getMessagePath($welcomeMessageFileName);
        $welcomeMessage = $this->disk->get($welcomeFileName);

        $noMatchFileName = MessageImportExportHelper::getMessagePath($noMatchMessageFileName);
        $noMatchMessage = $this->disk->get($noMatchFileName);

        // We didn't export the welcome message change so it should be as it was prior the the database change
        $this->assertStringContainsString($welcomeOriginalMarkup, $welcomeMessage);
        $this->assertStringContainsString("<intent>$welcomeMessageXml->intent</intent>", $welcomeMessage);
        $this->assertStringContainsString("<name>$welcomeMessageTemplateName</name>", $welcomeMessage);
        $this->assertStringNotContainsString("<conditions", $welcomeMessage);

        $this->assertStringContainsString($noMatchNewMarkup, $noMatchMessage);
        $this->assertStringContainsString("<intent>$noMatchMessageXml->intent</intent>", $noMatchMessage);
        $this->assertStringContainsString("<name>$noMatchMessageTemplateName</name>", $noMatchMessage);
        $this->assertStringNotContainsString("<conditions></conditions>", $noMatchMessage);
        $this->assertStringContainsString($noMatchNewConditions, $noMatchMessage);
    }

    public function testExportSingleMessageContainingAttribute()
    {
        $messageTemplateName = 'AttributeMessage';
        $messageTemplateFileName = MessageImportExportHelper::addMessageFileExtension($messageTemplateName);
        $messageTemplateFilePath = MessageImportExportHelper::getMessagePath($messageTemplateFileName);

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

        $filename = MessageImportExportHelper::getMessagePath("Did not understand.message.xml");

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
