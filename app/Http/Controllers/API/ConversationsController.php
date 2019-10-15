<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\Core\Conversation\Conversation as ConversationNode;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
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
    public function index()
    {
        $conversations = Conversation::where('status', '!=', ConversationNode::ARCHIVED)->paginate(50);

        foreach ($conversations as $conversation) {
            $conversation->outgoing_intents = $this->outgoingIntents($conversation);
            $conversation->opening_intent = $this->openingIntent($conversation);

            $conversation->makeVisible('id');
            $conversation->makeVisible('outgoing_intents');
            $conversation->makeVisible('opening_intent');
        }

        return new ConversationCollection($conversations);
    }

    /**
     * Display an archive listing.
     *
     * @return ConversationCollection
     */
    public function viewArchive()
    {
        $conversations = Conversation::where('status', ConversationNode::ARCHIVED)->paginate(50);

        foreach ($conversations as $conversation) {
            $conversation->outgoing_intents = $this->outgoingIntents($conversation);
            $conversation->opening_intent = $this->openingIntent($conversation);

            $conversation->makeVisible('id');
            $conversation->makeVisible('outgoing_intents');
            $conversation->makeVisible('opening_intent');
        }

        return new ConversationCollection($conversations);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Conversation::make($request->all());

        if ($error = $this->validateValue($conversation)) {
            return response($error, 400);
        }

        $conversation->save();

        $conversation->makeVisible('id');

        return new ConversationResource($conversation);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $conversation = Conversation::find($id);

        $conversation->outgoing_intents = $this->outgoingIntents($conversation);
        $conversation->opening_intent = $this->openingIntent($conversation);
        $conversation->history = $this->getHistory($conversation);

        $conversation->makeVisible('id');
        $conversation->makeVisible('outgoing_intents');
        $conversation->makeVisible('opening_intent');
        $conversation->makeVisible('history');

        return new ConversationResource($conversation);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function archive($id)
    {
        if ($conversation = Conversation::find($id)) {
            $result = $conversation->archiveConversation();

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($conversation = Conversation::find($id)) {
            if ($conversation->delete()) {
                return response()->noContent(200);
            }
        }

        return response()->noContent(404);
    }

    public function activate($id)
    {
        if ($conversation = Conversation::find($id)) {
            $ret = $conversation->activateConversation($conversation->buildConversation());

            return response()->json($ret);
        }

        return response()->json(false);
    }

    public function deactivate($id)
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
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function restore(int $id, int $versionId)
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        /** @var Activity $version */
        $version = Activity::where([
            ['subject_id', $id],
            ['id', $versionId]
        ])->first();

        // Deactivate current version if activated
        if ($conversation->status == ConversationNode::ACTIVATED) {
            $deactivateResult = $conversation->deactivateConversation();

            if (!$deactivateResult) {
                return response()->noContent(500);
            }
        }

        // Update, persist and re-validate conversation with previous model
        $conversation->model = $version->properties->first()["model"];
        $conversation->graph_uid = null;
        $conversation->save();

        // Return
        return response()->noContent(200);
    }

    /**
     * @param int $id
     * @param int $versionId
     * @return ConversationResource
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function reactivate(int $id, int $versionId)
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::find($id);

        /** @var Activity $version */
        $version = Activity::where([
            ['subject_id', $id],
            ['id', $versionId]
        ])->first();

        // Deactivate current version if activated
        if ($conversation->status == ConversationNode::ACTIVATED) {
            $deactivateResult = $conversation->deactivateConversation();

            if (!$deactivateResult) {
                return response()->noContent(500);
            }
        }

        // Update, persist, re-validate and activate conversation with previous model if activatable
        $conversation->model = $version->properties->first()["model"];
        $conversation->graph_uid = null;
        $conversation->save();

        // There's no reason for the previous version to not be valid, but just in case of any future changes we check
        if ($conversation->status == ConversationNode::ACTIVATABLE) {
            $conversation->activateConversation($conversation->buildConversation());
        }

        return response()->noContent(200);
    }

    /**
     * @param Conversation $conversation
     * @return string
     */
    private function validateValue(Conversation $conversation)
    {
        $rule = new ConversationYAML();

        if (strlen($conversation->name) > 512) {
            return [
                'field' => 'name',
                'message' => 'The maximum length for conversation name is 512.',
            ];
        }

        if (!$conversation->name) {
            return [
                'field' => 'name',
                'message' => 'Conversation name field is required.',
            ];
        }

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

        if ($yaml['id'] != $conversation->name) {
            return [
                'field' => 'name',
                'message' => 'Conversation name must be the same of model conversation id.',
            ];
        }

        return null;
    }

    /**
     * @param Conversation $conversation
     * @return array
     */
    private function outgoingIntents(Conversation $conversation)
    {
        $outgoingIntents = [];
        $yaml = Yaml::parse($conversation->model)['conversation'];

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                foreach ($intent as $tag => $value) {
                    if ($tag == 'b') {
                        foreach ($value as $key => $intent) {
                            if ($key == 'i') {
                                $outgoingIntent = OutgoingIntent::where('name', $intent)->first();
                                if ($outgoingIntent) {
                                    $outgoingIntents[] = [
                                        'id' => $outgoingIntent->id,
                                        'name' => $intent,
                                    ];
                                } else {
                                    $outgoingIntents[] = [
                                        'name' => $intent,
                                    ];
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $outgoingIntents;
    }

    /**
     * @param Conversation $conversation
     * @return string
     */
    private function openingIntent(Conversation $conversation)
    {
        $yaml = Yaml::parse($conversation->model)['conversation'];

        foreach ($yaml['scenes'] as $sceneId => $scene) {
            foreach ($scene['intents'] as $intent) {
                foreach ($intent as $tag => $value) {
                    if ($tag == 'u') {
                        foreach ($value as $key => $intent) {
                            if ($key == 'i') {
                                return $intent;
                            }
                        }
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param Conversation $conversation
     * @return mixed
     */
    public function getHistory(Conversation $conversation)
    {
        $history = Activity::where('subject_id', $conversation->id)->orderBy('id', 'desc')->get();

        return $history->filter(function ($item) {
            // Retain if it's the first activity record or if it's a record with the version has incremented
            return isset($item['properties']['old'])
                && $item['properties']['attributes']['version_number'] != $item['properties']['old']['version_number'];
        })->values()->map(function ($item) {
            return [
                'id' => $item['id'],
                'timestamp' => $item['updated_at'],
                'attributes' => $item['properties']['attributes']
            ];
        });
    }
}
