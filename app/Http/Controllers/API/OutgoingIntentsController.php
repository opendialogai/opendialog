<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIntentCollection;
use App\Http\Resources\OutgoingIntentResource;
use App\ImportExportHelpers\Generator\IntentFileGenerator;
use App\ImportExportHelpers\Generator\InvalidFileFormatException;
use App\ImportExportHelpers\Generator\MessageFileGenerator;
use App\ImportExportHelpers\IntentImportExportHelper;
use App\ImportExportHelpers\MessageImportExportHelper;
use Ds\Map;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use ZipStream\ZipStream;

class OutgoingIntentsController extends Controller
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


    /**
     * Display a listing of the resource.
     *
     * @return OutgoingIntentCollection
     */
    public function index(): OutgoingIntentCollection
    {
        /** @var OutgoingIntent $outgoingIntents */
        $outgoingIntents = OutgoingIntent::paginate(50);

        foreach ($outgoingIntents as $outgoingIntent) {
            $outgoingIntent->makeVisible('id');
        }

        return new OutgoingIntentCollection($outgoingIntents);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return OutgoingIntentResource
     */
    public function store(Request $request): OutgoingIntentResource
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::make($request->all());

        if ($error = $this->validateValue($outgoingIntent)) {
            return response($error, 400);
        }

        $outgoingIntent->save();

        $outgoingIntent->makeVisible('id');

        return new OutgoingIntentResource($outgoingIntent);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return OutgoingIntentResource
     */
    public function show($id): OutgoingIntentResource
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::find($id);

        $outgoingIntent->makeVisible('id');

        return new OutgoingIntentResource($outgoingIntent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int    $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        /** @var OutgoingIntent $outgoingIntent */
        if ($outgoingIntent = OutgoingIntent::find($id)) {
            $outgoingIntent->fill($request->all());

            if ($error = $this->validateValue($outgoingIntent)) {
                return response($error, 400);
            }

            $outgoingIntent->save();
        }

        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id): Response
    {
        if ($outgoingIntent = OutgoingIntent::find($id)) {
            $outgoingIntent->delete();
        }

        return response()->noContent(200);
    }

    /**
     * @param int $id
     * @return string
     */
    public function export(int $id)
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::find($id);

        $fileName = $outgoingIntent->name . '.zip';

        $zip = new ZipStream($fileName);

        /** @var MessageTemplate $messageTemplate */
        foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
            $messageFile = new MessageFileGenerator(
                $outgoingIntent->name,
                $messageTemplate->name,
                $messageTemplate->message_markup
            );

            if ($messageTemplate->conditions) {
                $messageFile->setConditions($messageTemplate->conditions);
            }

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $messageFile);
            rewind($stream);
            $zip->addFileFromStream(MessageImportExportHelper::addMessageFileExtension($messageTemplate->name), $stream);
            fclose($stream);
        }

        $zip->finish();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function import(Request $request, int $id)
    {
        /** @var OutgoingIntent $outgoingIntent */
        $outgoingIntent = OutgoingIntent::find($id);

        $errorMessage = null;
        $files = new Map();

        $i = 1;
        try {
            while (true) {
                if ($file = $request->file('file' . $i)) {
                    $messageFileName = $file->getClientOriginalName();

                    if (!MessageImportExportHelper::stringEndsWithFileExtension($messageFileName)) {
                        throw new Exception(sprintf(
                            '%s is not a valid message file, message files must use \'%s\' extension.',
                            $messageFileName,
                            MessageImportExportHelper::MESSAGE_FILE_EXTENSION
                        ));
                    }

                    $this->validateMessageFile($file, $messageFileName, $outgoingIntent);
                    $files->put($messageFileName, $file);
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

        foreach ($files as $fileName => $file) {
            $this->importMessageFile($fileName, $file);
        }

        return response()->noContent(200);
    }

    /**
     * @return Response
     */
    public function exportAll()
    {
        $fileName = 'outgoing-intents.zip';

        $zip = new ZipStream($fileName);

        $outgoingIntents = OutgoingIntent::all();

        $this->addOutgoingIntentsAndMessageTemplatesToZip($outgoingIntents, $zip);

        $zip->finish();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function importAll(Request $request)
    {
        $errorMessage = null;
        $messageFiles = new Map();
        $intentFiles = new Map();

        $i = 1;

        try {
            while (true) {
                if ($file = $request->file('file' . $i)) {
                    $fileName = $file->getClientOriginalName();

                    if (MessageImportExportHelper::stringEndsWithFileExtension($fileName)) {
                        $this->validateMessageFile($file, $fileName);
                        $messageFiles->put($fileName, $file);
                    } elseif (IntentImportExportHelper::stringEndsWithFileExtension($fileName)) {
                        $this->validateIntentFile($file, $fileName);
                        $intentFiles->put($fileName, $file);
                    } else {
                        throw new Exception(sprintf(
                            '%s is not a valid message or intent file, message files must use \'%s\' extension'
                            . ' & intent files must use \'%s\' extension.',
                            $fileName,
                            MessageImportExportHelper::MESSAGE_FILE_EXTENSION,
                            IntentImportExportHelper::INTENT_FILE_EXTENSION
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

        foreach ($messageFiles as $fileName => $file) {
            $this->importMessageFile($fileName, $file);
        }

        foreach ($intentFiles as $fileName => $file) {
            $this->importIntentFile($fileName, $file);
        }

        return response()->noContent(200);
    }

    /**
     * @param OutgoingIntent $outgoingIntent
     * @return array|null
     */
    private function validateValue(OutgoingIntent $outgoingIntent): ?array
    {
        if (strlen($outgoingIntent->name) > 255) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for outgoing intent name is 255.',
            ];
        }

        if (!$outgoingIntent->name) {
            return [
                'field' => 'name',
                'message' => 'Outgoing intent name field is required.',
            ];
        }

        return null;
    }

    /**
     * @param string $fileName
     * @param UploadedFile|null $file
     */
    public function importMessageFile(string $fileName, ?UploadedFile $file): void
    {
        MessageImportExportHelper::importMessageFileFromString($fileName, $file->get());
    }

    /**
     * @param string $fileName
     * @param UploadedFile|null $file
     */
    public function importIntentFile(string $fileName, ?UploadedFile $file): void
    {
        IntentImportExportHelper::importIntentFileFromString($fileName, $file->get());
    }

    /**
     * @param UploadedFile|null $file
     * @param string $messageFileName
     * @param ?OutgoingIntent $outgoingIntent
     * @throws Exception
     */
    public function validateMessageFile(?UploadedFile $file, string $messageFileName, ?OutgoingIntent $outgoingIntent = null)
    {
        try {
            $fileGenerator = MessageFileGenerator::fromString($file->get());
        } catch (InvalidFileFormatException $e) {
            throw new Exception(sprintf('Invalid file formatting (%s) in %s', $e->getMessage(), $messageFileName));
        } catch (Exception $e) {
            throw new Exception(sprintf('Invalid file (%s) in %s', $e->getMessage(), $messageFileName));
        }

        if (!is_null($outgoingIntent) && $fileGenerator->getIntent() != $outgoingIntent->name) {
            throw new Exception(sprintf(
                'File references a different intent: found \'%s\' but expected \'%s\' in %s',
                $fileGenerator->getIntent(),
                $outgoingIntent->name,
                $messageFileName
            ));
        }
    }

    /**
     * @param UploadedFile|null $file
     * @param string $intentFileName
     * @throws Exception
     */
    public function validateIntentFile(?UploadedFile $file, string $intentFileName)
    {
        try {
            $fileGenerator = IntentFileGenerator::fromString($file->get());
        } catch (InvalidFileFormatException $e) {
            throw new Exception(sprintf('Invalid file formatting (%s) in %s', $e->getMessage(), $intentFileName));
        } catch (Exception $e) {
            throw new Exception(sprintf('Invalid file (%s) in %s', $e->getMessage(), $intentFileName));
        }
    }

    /**
     * @param Collection|OutgoingIntent[] $outgoingIntents
     * @param ZipStream $zip
     */
    public function addOutgoingIntentsAndMessageTemplatesToZip(Collection $outgoingIntents, ZipStream $zip): void
    {
        foreach ($outgoingIntents as $outgoingIntent) {
            $intentFile = (string)(new IntentFileGenerator($outgoingIntent->name));

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $intentFile);
            rewind($stream);

            $intentFileName = IntentImportExportHelper::addIntentFileExtension($outgoingIntent->name);
            $intentFilePath = IntentImportExportHelper::getIntentPath($intentFileName);

            $zip->addFileFromStream($intentFilePath, $stream);
            fclose($stream);

            /** @var MessageTemplate $messageTemplate */
            foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
                $messageFile = new MessageFileGenerator(
                    $outgoingIntent->name,
                    $messageTemplate->name,
                    $messageTemplate->message_markup
                );

                if ($messageTemplate->conditions) {
                    $messageFile->setConditions($messageTemplate->conditions);
                }

                $stream = fopen('php://memory', 'r+');
                fwrite($stream, $messageFile);
                rewind($stream);

                $messageFileName = MessageImportExportHelper::addMessageFileExtension($messageTemplate->name);
                $messageFilePath = MessageImportExportHelper::getMessagePath($messageFileName);
                $zip->addFileFromStream($messageFilePath, $stream);
                fclose($stream);
            }
        }
    }
}
