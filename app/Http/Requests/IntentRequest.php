<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenDialogAi\Core\Conversation\Intent;

class IntentRequest extends FormRequest
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
            'id' => 'string',
            'od_id' => ['bail', 'required', 'string', 'filled'],
            'name' => 'string',
            'description' => 'string',
            'interpreter' => 'nullable|string',
            'confidence' => ['bail', 'required', 'numeric', 'between:0,1'],
            'sample_utterance' => ['bail', 'required', 'string'],
            'speaker' => ['bail', 'required', 'string', Rule::in([Intent::USER, Intent::APP])],
            'behaviors' => 'array',
            'conditions' => 'array',
            'transitions' => 'array',
            'expected_attributes' => 'array',
            'virtual_intents' => 'array',
            'actions' => 'array',
        ];
    }
}
