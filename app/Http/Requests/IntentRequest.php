<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenDialogAi\Core\Conversation\Intent;

class IntentRequest extends FormRequest
{
    protected string $prefix = '';

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
        $prefix = $this->prefix;

        return [
            "${prefix}id" => 'string',
            "${prefix}od_id" => ['bail', 'required', 'string', 'filled'],
            "${prefix}name" => 'string',
            "${prefix}description" => 'string',
            "${prefix}interpreter" => 'nullable|string',
            "${prefix}confidence" => ['bail', 'required', 'numeric', 'between:0,1'],
            "${prefix}sample_utterance" => ['bail', 'required', 'string'],
            "${prefix}speaker" => ['bail', 'required', 'string', Rule::in([Intent::USER, Intent::APP])],
            "${prefix}behaviors" => 'array',
            "${prefix}conditions" => 'array',
            "${prefix}transitions" => 'array',
            "${prefix}expected_attributes" => 'array',
            "${prefix}virtual_intent" => ['nullable'],
            "${prefix}virtual_intent.speaker" => [
                'bail',
                "required_with:${prefix}virtual_intent",
                'string',
                Rule::in([Intent::USER]),
                Rule::notIn([$this->json("${prefix}speaker")])
            ],
            "${prefix}virtual_intent.intent_id" => [
                'bail',
                "required_with:${prefix}virtual_intent",
                'string',
                'filled'
            ],
            "${prefix}actions" => 'array',
        ];
    }

    public function attributes()
    {
        $attributes = [];

        foreach ($this->rules() as $attribute => $rules) {
            if ($this->prefix !== '' && substr($attribute, 0, strlen($this->prefix)) === $this->prefix) {
                $attributes[$attribute] = substr_replace($attribute, '', 0, strlen($this->prefix));
            }
        }

        return $attributes;
    }

    public function messages()
    {
        $prefix = $this->prefix;

        return [
            "${prefix}virtual_intent.speaker.in" => "The virtual intent speaker can only be " . Intent::USER . ".",
            "${prefix}virtual_intent.speaker.not_in" =>
                "The virtual intent speaker must not be the same as the related intent's speaker."
        ];
    }
}
