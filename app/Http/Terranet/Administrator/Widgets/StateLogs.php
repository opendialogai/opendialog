<?php

namespace App\Http\Terranet\Administrator\Widgets;

use App\Http\Terranet\Administrator\Modules\ConversationStateLogs;
use Terranet\Administrator\Contracts\Services\Widgetable;
use Terranet\Administrator\Services\Widgets\AbstractWidget;

/**
 * Widget StateLogs
 *
 * @package Terranet\Administrator
 */
class StateLogs extends AbstractWidget implements Widgetable
{
    protected $conversation;

    public function __construct($conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Widget contents
     *
     * @return mixed
     */
    public function render()
    {
        $stateLogs = $this->conversation->conversationStateLogs->sortByDesc('created_at');

        return view('admin.stateLogs', ['items' => $stateLogs]);
    }
}
