<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationCollection;
use App\Http\Resources\ConversationResource;
use Illuminate\Http\Request;
use OpenDialogAi\ConversationBuilder\Conversation;
use OpenDialogAi\ConversationEngine\Rules\ConversationYAML;
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
        return new ConversationCollection(Conversation::paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Conversation::create($request->all());

        if ($error = $this->validateValue($conversation)) {
            return response($error, 400);
        }

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
        return new ConversationResource(Conversation::find($id));
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
        }

        return response()->noContent(200);
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
        }

        return response()->noContent(200);
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

    private function validateValue(Conversation $conversation)
    {
        $rule = new ConversationYAML();

        if (strlen($conversation->name) > 512) {
            return 'The maximum length for conversation name is 512.';
        }

        if (!$rule->passes(null, $conversation->model)) {
            return $rule->message() . '.';
        }

        $yaml = Yaml::parse($conversation->model)['conversation'];

        if ($yaml['id'] != $conversation->name) {
            return 'Conversation name must be the same of model conversation id.';
        }

        return null;
    }
}
