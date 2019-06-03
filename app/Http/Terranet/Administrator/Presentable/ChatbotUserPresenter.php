<?php

namespace App\Http\Terranet\Administrator\Presentable;

use Terranet\Presentable\Presenter;

class ChatbotUserPresenter extends Presenter
{
    public function title()
    {
        return link_to_route('scaffold.view', $this->presentable->name, [
            'module' => 'chatbotusers',
            'id' => $this->presentable
        ]);
    }

    public function lastSeen()
    {
        if ($this->presentable->messages->count()) {
            return $this->presentable->messages->first()->created_at->format('Y-m-d H:i:s');
        }
    }
}
