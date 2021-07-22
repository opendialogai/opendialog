<?php


namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use OpenDialogAi\Core\Conversation\Turn;

class TurnIntentRequest extends IntentRequest
{
    protected string $prefix = 'intent.';

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
        return parent::rules() + [
            'order' => ['bail', 'required', 'string', Rule::in([Turn::ORDER_REQUEST, Turn::ORDER_RESPONSE])],
        ];
    }

    public function attributes()
    {
        return [
            'intent.od_id' => 'name'
        ] + parent::attributes();
    }
}
