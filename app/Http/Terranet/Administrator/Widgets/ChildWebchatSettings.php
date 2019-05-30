<?php

namespace App\Http\Terranet\Administrator\Widgets;

use Terranet\Administrator\Contracts\Services\Widgetable;
use Terranet\Administrator\Services\Widgets\AbstractWidget;

/**
 * Widget ChildWebchatSettings
 *
 * @package Terranet\Administrator
 */
class ChildWebchatSettings extends AbstractWidget implements Widgetable
{
    protected $webchatSetting;

    public function __construct($webchatSetting)
    {
        $this->webchatSetting = $webchatSetting;
    }

    /**
     * Widget contents
     *
     * @return mixed
     */
    public function render()
    {
        $childWebchatSettings = $this->webchatSetting->children;

        return view('admin.childWebchatSettings', ['children' => $childWebchatSettings]);
    }
}
