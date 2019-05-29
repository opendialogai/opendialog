<?php

namespace App\Http\Terranet\Administrator\Widgets;

use App\Http\Terranet\Administrator\Modules\OutgoingIntent;
use Terranet\Administrator\Contracts\Services\Widgetable;
use Terranet\Administrator\Services\Widgets\AbstractWidget;

/**
 * Widget MessageTemplates
 *
 * @package Terranet\Administrator
 */
class MessageTemplates extends AbstractWidget implements Widgetable
{
    protected $outgoingIntent;

    public function __construct($outgoingIntent)
    {
        $this->outgoingIntent = $outgoingIntent;
    }

    /**
     * Widget contents
     *
     * @return mixed
     */
    public function render()
    {
        $messageTemplates = $this->outgoingIntent->messageTemplates;

				return view('admin.messageTemplates', ['items' => $messageTemplates]);
    }
}
