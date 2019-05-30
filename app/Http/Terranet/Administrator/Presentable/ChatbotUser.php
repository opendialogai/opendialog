<?php

namespace App\Http\Terranet\Administrator\Presentable;

use App\Http\Terranet\Administrator\Presentable\ChatbotUserPresenter;
use OpenDialogAi\ConversationLog\ChatbotUser as OdChatbotUser;
use Terranet\Presentable\PresentableInterface;
use Terranet\Presentable\PresentableTrait;

class ChatbotUser extends OdChatbotUser implements PresentableInterface
{
    use PresentableTrait;

    protected $fillable = [
        'user_id',
        'country',
        'os',
        'created_at',
        'lastSeen',
    ];

    protected $presenter = ChatbotUserPresenter::class;
}
