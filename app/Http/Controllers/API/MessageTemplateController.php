<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Facades\Serializer;
use App\Http\Requests\MessageTemplateRequest;
use App\Http\Resources\MessageTemplateResource;
use OpenDialogAi\Core\Conversation\DataClients\MessageTemplateDataClient;
use OpenDialogAi\Core\Conversation\Intent;
use OpenDialogAi\Core\Conversation\MessageTemplate;

class MessageTemplateController extends Controller
{
    /**
     * @var MessageTemplateDataClient
     */
    private $messageTemplateDataClient;

    public function __construct()
    {
        $this->middleware('auth');
        $this->messageTemplateDataClient = resolve(MessageTemplateDataClient::class);
    }

    public function store(Intent $intent, MessageTemplateRequest $request)
    {
        /** @var MessageTemplate $newMessageTemplate */
        $newMessageTemplate = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        $newMessageTemplate->setIntent($intent);

        $messageTemplate = $this->messageTemplateDataClient->addMessageTemplateToIntent($newMessageTemplate);

        return new MessageTemplateResource($messageTemplate);
    }

    public function show(?Intent $intent, MessageTemplate $messageTemplate)
    {
        return new MessageTemplateResource($messageTemplate);
    }

    public function destroy(?Intent $intent, MessageTemplate $messageTemplate)
    {
        if ($this->messageTemplateDataClient->deleteMessageTemplate($messageTemplate->getUid())) {
            return response()->noContent(200);
        } else {
            return response('Error deleting message template, check the logs', 500);
        }
    }

    public function update(Intent $intent, MessageTemplateRequest $request): MessageTemplateResource
    {
        $update = Serializer::deserialize($request->getContent(), MessageTemplate::class, 'json');
        return new MessageTemplateResource($this->messageTemplateDataClient->updateMessageTemplate($update));
    }
}
