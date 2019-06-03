<?php

namespace App\Http\Terranet\Administrator\Presentable;

use App\Http\Terranet\Administrator\Presentable\ConversationPresenter;
use OpenDialogAi\ResponseEngine\MessageTemplate as OdMessageTemplate;
use Terranet\Presentable\PresentableInterface;
use Terranet\Presentable\PresentableTrait;

class MessageTemplate extends OdMessageTemplate implements PresentableInterface
{
    use PresentableTrait;

    protected $fillable = ['outgoingIntent', 'name', 'conditions', 'message_markup'];

    protected $presenter = MessageTemplatePresenter::class;
}
