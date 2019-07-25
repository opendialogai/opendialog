<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $conversations = Conversation::paginate(50);

        foreach ($conversations as $conversation) {
            $conversation->outgoing_intents = $this->outgoingIntents($conversation);
            $conversation->opening_intent = $this->openingIntent($conversation);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($conversation = Conversation::find($id)) {
            $conversation->delete();

            return response()->noContent(200);
        }

        return response()->noContent(404);
    }

    public function publish($id)
    {
        if ($conversation = Conversation::find($id)) {
            $ret = $conversation->publishConversation($conversation->buildConversation());

            return response()->json($ret);
        }

        return response()->json(false);
    }

    public function unpublish($id)
    {
        if ($conversation = Conversation::find($id)) {
            $ret = $conversation->unPublishConversation();

            return response()->json($ret);
        }

        return response()->json(false);
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
}
