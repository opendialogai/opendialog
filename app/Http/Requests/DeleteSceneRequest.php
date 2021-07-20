<?php

namespace App\Http\Requests;

use App\Rules\SceneInTransition;
use Illuminate\Foundation\Http\FormRequest;

class DeleteSceneRequest extends FormRequest
{
    use DeleteObjectRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->prepareRules(SceneInTransition::class);
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareForValidation()
    {
        $this->prepareValidation('scene');
    }
}
