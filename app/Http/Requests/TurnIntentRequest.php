<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenDialogAi\Core\Conversation\Turn;

class TurnIntentRequest extends FormRequest
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
            'order' => ['string', Rule::in([Turn::ORDER_REQUEST, Turn::ORDER_RESPONSE])],
            'intent' => 'array'
        ];
    }
}
