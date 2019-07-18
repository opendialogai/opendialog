<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageTemplateCollection;
use App\Http\Resources\MessageTemplateResource;
use Illuminate\Http\Request;
use OpenDialogAi\ResponseEngine\MessageTemplate;
use OpenDialogAi\ResponseEngine\Rules\MessageConditions;
use OpenDialogAi\ResponseEngine\Rules\MessageXML;

class MessageTemplatesController extends Controller
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
    public function index($outgoingIntentId)
    {
        return new MessageTemplateCollection(MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->paginate(50));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $messageTemplate = MessageTemplate::make($request->all());

        if ($error = $this->validateValue($messageTemplate)) {
            return response($error, 400);
        }

        $messageTemplate->save();

        return new MessageTemplateResource($messageTemplate);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($outgoingIntentId, $id)
    {
        return new MessageTemplateResource(MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $outgoingIntentId, $id)
    {
        if ($messageTemplate = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id)) {
            $messageTemplate->fill($request->all());

            if ($error = $this->validateValue($messageTemplate)) {
                return response($error, 400);
            }

            $messageTemplate->save();
        }

        return response()->noContent(200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($outgoingIntentId, $id)
    {
        if ($messageTemplate = MessageTemplate::where('outgoing_intent_id', $outgoingIntentId)->find($id)) {
            $messageTemplate->delete();
        }

        return response()->noContent(200);
    }

    /**
     * @param MessageTemplate $messageTemplate
     * @return string
     */
    private function validateValue(MessageTemplate $messageTemplate)
    {
        $ruleXML = new MessageXML();
        $ruleConditions = new MessageConditions();

        if (strlen($messageTemplate->name) > 255) {
            return 'The maximum length for message template name is 255.';
        }

        if (MessageTemplate::where('name', $messageTemplate->name)->where('id', '<>', $messageTemplate->id)->count()) {
            return 'Message template name is already in use.';
        }

        if (!$ruleConditions->passes(null, $messageTemplate->conditions)) {
            return $ruleConditions->message();
        }

        if (!$ruleXML->passes(null, $messageTemplate->message_markup)) {
            return $ruleXML->message() . '.';
        }

        return null;
    }
}