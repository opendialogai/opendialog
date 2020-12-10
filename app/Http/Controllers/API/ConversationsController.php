<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageTemplateCollection;
use App\ImportExportHelpers\ConversationImportExportHelper;
use Ds\Map;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\Yaml\Yaml;
use ZipStream\ZipStream;

class ConversationsController extends Controller
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
     * @return ConversationCollection
     */
    public function index(): ConversationCollection
    {
        $conversations = Conversation::withoutStatus(ConversationNode::ARCHIVED)->paginate(50);
        return new ConversationCollection($conversations);
    }


    /**
     * Display an archive listing.
     *
     * @return ConversationCollection
     */
    public function viewArchive(): ConversationCollection
    {
        $conversations = Conversation::withStatus(ConversationNode::ARCHIVED)->paginate(50);
        return new ConversationCollection($conversations);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ConversationResource
     */
    public function store(Request $request)
    {
        $yaml = Yaml::parse($request->model)['conversation'];

        /** @var Conversation $conversation */
        $conversation = Conversation::make([
            'name' => $yaml['id'],
            'model' => $request->model,
            'notes' => $request->notes,
        ]);

        if ($error = $this->validateValue($conversation->model)) {
            return response($error, 400);
        }

        $conversation->save();

        return new ConversationResource($conversation);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ConversationResource
     */
    public function show($id): ConversationResource
    {
        $conversation = Conversation::conversationWithHistory($id);
        return new ConversationResource($conversation);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $id
     * @return MessageTemplateCollection|Response
     */
    public function messageTemplates($id)
    {
        if ($conversation = Conversation::find($id)) {
            $outgoingIntentIds = collect($conversation->outgoing_intents)
                ->filter(function ($item) {
                    return isset($item['id']);
                })
                ->map(function ($item) {
                    return $item['id'];
                })
                ->unique();

            $messageTemplateCollection = new MessageTemplateCollection(
                MessageTemplate::with('outgoingIntent')
                    ->whereIn('outgoing_intent_id', $outgoingIntentIds)
                    ->paginate(50)
            );

            $messageTemplateCollection->each(function ($item) {
                $item->makeVisible('id');
                $item->makeVisible('outgoingIntent');
            });

            return $messageTemplateCollection;
        }

        return response()->noContent(404);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        /** @var Conversation $conversation */
        if ($conversation = Conversation::find($id)) {
            $conversation->fill($request->all());

            if ($error = $this->validateValue($conversation->model)) {
                return response($error, 400);
            }

            $conversation->save();

            return response()->noContent(200);
        }

        return response()->noContent(404);
    }


    /**
     * Archive the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function archive($id): Response
    {
        /** @var Conversation $conversation */
        if ($conversation = Conversation::find($id)) {
            try {
                $result = $conversation->archiveConversation();
            } catch (BindingResolutionException $e) {
                Log::error(sprintf('Error archiving conversation - %s', $e->getMessage()));
                return response('Error archiving Conversation', 500);
            }

            if ($result) {
                return response()->noContent(200);
            } else {
                return response()->noContent(404);
            }
        }

        return response()->noContent(404);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id): Response
    {
        /** @var Conversation $conversation */
        if ($conversation = Conversation::find($id)) {
            try {
                if ($conversation->delete()) {
                    return response()->noContent(200);
                }
            } catch (\Exception $e) {
                Log::error(sprintf('Error deleting conversation - %s', $e->getMessage()));
                return response('Error deleting conversation, check the logs', 500);
            }
        }

        return response()->noContent(404);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function activate($id): JsonResponse
    {
        if ($conversation = Conversation::find($id)) {
            $ret = $conversation->activateConversation();

            return response()->json($ret);
        }

        return response()->json(false);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function deactivate($id): JsonResponse
    {
        if ($conversation = Conversation::find($id)) {
            $ret = $conversation->deactivateConversation();

            return response()->json($ret);
        }

        return response()->json(false);
    }


    /**
     * @param int $id
     * @param int $versionId
     * @return ConversationResource
     * @throws BindingResolutionException
     */
    public function restore(int $id, int $versionId)
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        try {
            $this->restoreConversation($conversation, $id, $versionId);
        } catch (ConversationRestorationException $e) {
            Log::error($e->getMessage());
            return response()->noContent(500);
        }

        // Return
        return response()->noContent(200);
    }


    /**
     * @param int $id
     * @param int $versionId
     * @return Response
     * @throws BindingResolutionException
     */
    public function reactivate(int $id, int $versionId): Response
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        try {
            $this->restoreConversation($conversation, $id, $versionId);
        } catch (ConversationRestorationException $e) {
            Log::error($e->getMessage());
            return response()->noContent(500);
        }

        // There's no reason for the previous version to not be valid, but just in case of any future changes we check
        if ($conversation->status == ConversationNode::ACTIVATABLE) {
            $conversation->activateConversation();
        }

        return response()->noContent(200);
    }

    /**
     * @param int $id
     * @return string
     */
    public function export(int $id)
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        $fileName = $conversation->name . '.zip';

        $zip = new ZipStream($fileName);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $conversation->model);
        rewind($stream);
        $zip->addFileFromStream(ConversationImportExportHelper::addConversationFileExtension($conversation->name), $stream);
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
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        $file = $request->file('file');
        $activate = $request->post('activate') == 'true';

        if ($error = $this->validateValue($file->get())) {
            $error['field'] = 'import';
            $error['message'] = sprintf('Invalid file formatting (%s) in %s', $error['message'], $file->getClientOriginalName());
            return response($error, 400);
        }

        $this->importConversationFile($conversation->name, $file, $activate);

        return response()->noContent(200);
    }

    /**
     * @return Response
     */
    public function exportAll()
    {
        $fileName = 'conversations.zip';

        $zip = new ZipStream($fileName);

        $conversations = Conversation::all();

        $this->addConversationsToZip($conversations, $zip);

        $zip->finish();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function importAll(Request $request)
    {
        $activate = $request->post('activate') == 'true';

        $errorMessage = null;
        $conversationFiles = new Map();

        $i = 1;

        try {
            while (true) {
                if ($file = $request->file('file' . $i)) {
                    $conversationFileName = $file->getClientOriginalName();

                    if (!ConversationImportExportHelper::stringEndsWithFileExtension($conversationFileName)) {
                        throw new Exception(sprintf(
                            '%s is not a valid conversation file, message files must use \'%s\' extension.',
                            $conversationFileName,
                            ConversationImportExportHelper::CONVERSATION_FILE_EXTENSION
                        ));
                    }

                    if ($error = $this->validateValue($file->get())) {
                        throw new Exception(sprintf(
                            'Invalid file formatting (%s) in %s',
                            $error['message'],
                            $file->getClientOriginalName()
                        ));
                    }

                    $conversationFiles->put($conversationFileName, $file);
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

        foreach ($conversationFiles as $fileName => $file) {
            $conversationName = ConversationImportExportHelper::removeConversationFileExtension($fileName);
            $this->importConversationFile($conversationName, $file, $activate);
        }

        return response()->noContent(200);
    }

    /**
     * @param string $conversationModel
     * @return array
     */
    public function validateValue(string $conversationModel): ?array
    {
        $rule = new ConversationYAML();

        if (!$conversationModel) {
            return [
                'field' => 'model',
                'message' => 'Conversation model is required.',
            ];
        }

        if (!$rule->passes(null, $conversationModel)) {
            return [
                'field' => 'model',
                'message' => $rule->message() . '.',
            ];
        }

        $yaml = Yaml::parse($conversationModel)['conversation'];

        if (strlen($yaml['id']) > 512) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for conversation id is 512.',
            ];
        }

        return null;
    }


    /**
     * @param Conversation $conversation
     * @param int          $id
     * @param int          $versionId
     * @throws ConversationRestorationException
     * @throws BindingResolutionException
     */
    private function restoreConversation(Conversation $conversation, int $id, int $versionId): void
    {
        /** @var Activity $version */
        $version = Activity::where([
            ['subject_id', $id],
            ['id', $versionId],
        ])->first();

        if (is_null($version)) {
            throw new ConversationRestorationException("Could not find a previous version for restoration.");
        }

        // Deactivate current version if activated
        if ($conversation->status == ConversationNode::ACTIVATED) {
            $deactivateResult = $conversation->deactivateConversation();

            if (!$deactivateResult) {
                throw new ConversationRestorationException(
                    "Tried to deactivate the current version during a restoration but failed."
                );
            }
        }

        // Update, persist and re-validate conversation with previous model
        $conversation->model = $version->properties->first()["model"];
        $conversation->graph_uid = null;
        $conversation->save();
    }

    /**
     * @param string $conversationName
     * @param UploadedFile|null $file
     * @param bool $activate
     */
    public function importConversationFile(string $conversationName, ?UploadedFile $file, bool $activate): void
    {
        ConversationImportExportHelper::importConversationFromString($conversationName, $file->get(), $activate);
    }

    /**
     * @param Collection|Conversation[] $conversations
     * @param ZipStream $zip
     * @param bool $withDirectory
     */
    public function addConversationsToZip(Collection $conversations, ZipStream $zip, $withDirectory = false): void
    {
        foreach ($conversations as $conversation) {
            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $conversation->model);
            rewind($stream);

            $conversationFileName = ConversationImportExportHelper::addConversationFileExtension($conversation->name);

            if ($withDirectory) {
                $conversationFileName = ConversationImportExportHelper::getConversationPath($conversationFileName);
            }

            $zip->addFileFromStream($conversationFileName, $stream);
            fclose($stream);
        }
    }
}
