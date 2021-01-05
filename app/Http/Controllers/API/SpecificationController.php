<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\ImportExportHelpers\ConversationImportExportHelper;
use App\ImportExportHelpers\IntentImportExportHelper;
use App\ImportExportHelpers\MessageImportExportHelper;
use Ds\Map;
use Exception;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use ZipStream\ZipStream;

class SpecificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function import(Request $request)
    {
        $errorMessage = null;
        $messageFiles = new Map();
        $intentFiles = new Map();
        $conversationFiles = new Map();

        $i = 1;

        try {
            while (true) {
                if ($file = $request->file('file' . $i)) {
                    $fileName = $file->getClientOriginalName();

                    if (MessageImportExportHelper::stringEndsWithFileExtension($fileName)) {
                        resolve(OutgoingIntentsController::class)->validateMessageFile($file, $fileName);
                        $messageFiles->put($fileName, $file);
                    } elseif (IntentImportExportHelper::stringEndsWithFileExtension($fileName)) {
                        resolve(OutgoingIntentsController::class)->validateIntentFile($file, $fileName);
                        $intentFiles->put($fileName, $file);
                    } elseif (ConversationImportExportHelper::stringEndsWithFileExtension($fileName)) {
                        $error = resolve(ConversationsController::class)->validateValue($file->get());

                        if (is_null($error)) {
                            $conversationFiles->put($fileName, $file);
                        } else {
                            throw new Exception(sprintf(
                                'Invalid file formatting (%s) in %s',
                                $error['message'],
                                $fileName
                            ));
                        }
                    } else {
                        throw new Exception(sprintf(
                            '%s is not a valid message, intent or conversation file,'
                            . ' message files must use \'%s\' extension,'
                            . ' intent files must use \'%s\' extension'
                            . ' & converastion files must use \'%s\' extension.',
                            $fileName,
                            MessageImportExportHelper::MESSAGE_FILE_EXTENSION,
                            IntentImportExportHelper::INTENT_FILE_EXTENSION,
                            ConversationImportExportHelper::CONVERSATION_FILE_EXTENSION
                        ));
                    }

                    $i++;
                } else {
                    break;
                }
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (!is_null($errorMessage)) {
            return response([
                'field' => 'import',
                'message' => $errorMessage
            ], 400);
        }

        MessageImportExportHelper::deleteExistingMessages();
        IntentImportExportHelper::deleteExistingIntents();
        ConversationImportExportHelper::deleteExistingConversations();

        foreach ($messageFiles as $fileName => $file) {
            resolve(OutgoingIntentsController::class)->importMessageFile($fileName, $file);
        }

        foreach ($intentFiles as $fileName => $file) {
            resolve(OutgoingIntentsController::class)->importIntentFile($fileName, $file);
        }

        foreach ($conversationFiles as $fileName => $file) {
            $conversationName = ConversationImportExportHelper::removeConversationFileExtension($fileName);
            resolve(ConversationsController::class)->importConversationFile($conversationName, $file, true);
        }

        return response()->noContent(200);
    }

    public function export()
    {
        $fileName = 'specification.zip';

        $zip = new ZipStream($fileName);

        $outgoingIntents = OutgoingIntent::all();
        resolve(OutgoingIntentsController::class)->addOutgoingIntentsAndMessageTemplatesToZip($outgoingIntents, $zip);

        $conversations = Conversation::all();
        resolve(ConversationsController::class)->addConversationsToZip($conversations, $zip, true);

        $zip->finish();
    }
}
