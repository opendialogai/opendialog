<?php

namespace App\Http\Terranet\Administrator\Presentable;

use Terranet\Presentable\Presenter;

class WebchatSettingPresenter extends Presenter
{
    public function title()
    {
        return link_to_route('scaffold.view', $this->presentable->name, [
            'module' => 'webchatsettings',
            'id' => $this->presentable
        ]);
    }

    public function value()
    {
        $value = $this->presentable->value;
        switch ($this->presentable->type) {
            case 'boolean':
                if ($value) {
                    return '<span class="fa fa-check-circle" style="color: green; font-size: 18px;" />';
                } else {
                    return '<span class="fa fa-times-circle" style="color: red; font-size: 18px;" />';
                }
                break;
            case 'number':
            case 'string':
                return $value;
                break;
            case 'object':
                return '<span class="fa fa-list-ul" style="color: darkblue; font-size: 18px;" />';
                break;
            default:
                break;
        }
    }
}
