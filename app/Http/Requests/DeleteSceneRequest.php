<?php

namespace App\Http\Requests;

use App\Rules\SceneInTransition;
use Illuminate\Foundation\Http\FormRequest;

class DeleteSceneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'scene' => [new SceneInTransition()]
        ];
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareForValidation()
    {
        $this->merge(['scene' => $this->route('scene')]);
    }
}
