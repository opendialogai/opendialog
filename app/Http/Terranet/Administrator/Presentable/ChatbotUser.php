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
        'created_at',
        'lastSeen',
        'first_name',
        'last_name',
        'ip_address',
        'country',
        'browser_language',
        'os',
        'browser',
        'timezone',
        'platform',
    ];

    protected $presenter = ChatbotUserPresenter::class;
}
