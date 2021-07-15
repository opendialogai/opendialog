<?php


namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use OpenDialogAi\Core\Conversation\Turn;

class TurnIntentRequest extends IntentRequest
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
        $intentRules = [];

        foreach (parent::rules() as $attribute => $rule) {
            $intentRules["intent.$attribute"] = $rule;
        }

        return $intentRules + [
            'order' => ['bail', 'required', 'string', Rule::in([Turn::ORDER_REQUEST, Turn::ORDER_RESPONSE])],
        ];
    }

    public function attributes()
    {
        $attributes = [];

        foreach (parent::rules() as $attribute => $rule) {
            $attributes["intent.$attribute"] = $attribute;
        }

        return [
            'intent.od_id' => 'name'
        ] + $attributes;
    }
}
