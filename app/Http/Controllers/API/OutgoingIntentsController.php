<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIntentCollection;
use App\Http\Resources\OutgoingIntentResource;
use App\ImportExportHelpers\Generator\IntentFileGenerator;
use App\ImportExportHelpers\Generator\MessageFileGenerator;
use App\ImportExportHelpers\IntentImportExportHelper;
use App\ImportExportHelpers\MessageImportExportHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
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

        $i = 1;
        while (true) {
            if ($file = $request->file('file' . $i)) {
                $messageFileName = $file->getClientOriginalName();
                $this->importMessageFile($messageFileName, $file);
                $i++;
            } else {
                break;
            }
        }

        Artisan::call(
            'messages:import',
            [
                'outgoingIntent' => $outgoingIntent->name,
                '--yes' => true
            ]
        );

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

        foreach ($outgoingIntents as $outgoingIntent) {
            $intentFile = (string) (new IntentFileGenerator($outgoingIntent->name));

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

        $zip->finish();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function importAll(Request $request)
    {
        $i = 1;
        while (true) {
            if ($file = $request->file('file' . $i)) {
                $fileName = $file->getClientOriginalName();

                if (MessageImportExportHelper::stringEndsWithFileExtension($fileName)) {
                    $this->importMessageFile($fileName, $file);

                    $messageName = MessageImportExportHelper::removeMessageFileExtension($fileName);

                    Artisan::call(
                        'messages:import',
                        [
                            '--yes' => true,
                            'message' => $messageName
                        ]
                    );
                } elseif (IntentImportExportHelper::stringEndsWithFileExtension($fileName)) {
                    $this->importIntentFile($fileName, $file);

                    $intentName = IntentImportExportHelper::removeIntentFileExtension($fileName);

                    Artisan::call(
                        'intents:import',
                        [
                            '--yes' => true,
                            'outgoingIntent' => $intentName
                        ]
                    );
                }

                $i++;
            } else {
                break;
            }
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
     * @param \Illuminate\Http\UploadedFile|null $file
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function importMessageFile(string $fileName, ?\Illuminate\Http\UploadedFile $file): void
    {
        $messageFileName = MessageImportExportHelper::getMessagePath($fileName);
        MessageImportExportHelper::getDisk()->delete($messageFileName);
        MessageImportExportHelper::getDisk()->put($messageFileName, $file->get());
    }

    /**
     * @param string $fileName
     * @param \Illuminate\Http\UploadedFile|null $file
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function importIntentFile(string $fileName, ?\Illuminate\Http\UploadedFile $file): void
    {
        $intentFileName = IntentImportExportHelper::getIntentPath($fileName);
        IntentImportExportHelper::getDisk()->delete($intentFileName);
        IntentImportExportHelper::getDisk()->put($intentFileName, $file->get());
    }
}
