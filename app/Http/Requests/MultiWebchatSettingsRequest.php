<?php

namespace App\Http\Requests;

use App\Rules\WebchatSettingValueRule;
use Illuminate\Foundation\Http\FormRequest;

class MultiWebchatSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*.name' => ['required', 'exists:webchat_settings,name'],
            '*.value' => ['present', new WebchatSettingValueRule(true)]
        ];
    }
}
