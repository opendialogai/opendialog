<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIntentCollection;
use App\Http\Resources\OutgoingIntentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
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

        $intent = "<intent>" . $outgoingIntent->name . "</intent>\n";

        foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
            $output = $intent;
            if ($messageTemplate->conditions) {
                $output .= "<conditions>\n" . $messageTemplate->conditions . "\n</conditions>\n";
            }
            $output .= $messageTemplate->message_markup;

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $output);
            rewind($stream);
            $zip->addFileFromStream($messageTemplate->name . '.message', $stream);
            fclose($stream);
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $outgoingIntent->name);
        rewind($stream);
        $zip->addFileFromStream($outgoingIntent->name, $stream);
        fclose($stream);

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

        $file = $request->file('file');

        $filename = base_path("resources/messages/$outgoingIntent->name");
        File::delete($filename);
        File::put($filename, $file->get());

        Artisan::call(
            'messages:update',
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
            $intent = "<intent>" . $outgoingIntent->name . "</intent>\n";

            foreach ($outgoingIntent->messageTemplates as $messageTemplate) {
                $output = $intent;
                if ($messageTemplate->conditions) {
                    $output .= "<conditions>\n" . $messageTemplate->conditions . "\n</conditions>\n";
                }
                $output .= $messageTemplate->message_markup;

                $stream = fopen('php://memory', 'r+');
                fwrite($stream, $output);
                rewind($stream);
                $zip->addFileFromStream($messageTemplate->name . '.message', $stream);
                fclose($stream);
            }

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $outgoingIntent->name);
            rewind($stream);
            $zip->addFileFromStream($outgoingIntent->name, $stream);
            fclose($stream);
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
                $outgoingIntentName = $file->getClientOriginalName();
                $filename = base_path("resources/messages/$outgoingIntentName");
                File::delete($filename);
                File::put($filename, $file->get());

                Artisan::call(
                    'messages:update',
                    [
                        'conversation' => $outgoingIntentName,
                        '--yes' => true
                    ]
                );

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
}
