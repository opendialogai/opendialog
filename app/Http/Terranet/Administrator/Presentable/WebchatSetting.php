<?php

namespace App\Http\Terranet\Administrator\Presentable;

use App\Http\Terranet\Administrator\Presentable\WebchatSettingPresenter;
use OpenDialogAi\Webchat\WebchatSetting as OdWebchatSetting;
use Terranet\Presentable\PresentableInterface;
use Terranet\Presentable\PresentableTrait;

class WebchatSetting extends OdWebchatSetting implements PresentableInterface
{
    use PresentableTrait;

    protected $fillable = [
        'name',
        'type',
        'value',
    ];

    protected $presenter = WebchatSettingPresenter::class;
}
