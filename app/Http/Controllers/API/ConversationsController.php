<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\Yaml\Yaml;

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

        $conversation = Conversation::make([
            'name' => $yaml['id'],
            'model' => $request->model,
            'notes' => $request->notes,
        ]);

        if ($error = $this->validateValue($conversation)) {
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
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $id): Response
    {
        if ($conversation = Conversation::find($id)) {
            $conversation->fill($request->all());

            if ($error = $this->validateValue($conversation)) {
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
     * @param Conversation $conversation
     * @return array
     */
    private function validateValue(Conversation $conversation): ?array
    {
        $rule = new ConversationYAML();

        if (!$conversation->model) {
            return [
                'field' => 'model',
                'message' => 'Conversation model field is required.',
            ];
        }

        if (!$rule->passes(null, $conversation->model)) {
            return [
                'field' => 'model',
                'message' => $rule->message() . '.',
            ];
        }

        $yaml = Yaml::parse($conversation->model)['conversation'];

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
}
