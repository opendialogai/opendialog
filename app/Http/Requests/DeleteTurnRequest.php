<?php

namespace App\Http\Requests;

use App\Rules\TurnInTransition;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTurnRequest extends FormRequest
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
            'transition_intents' => [new TurnInTransition()]
        ];
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareForValidation()
    {
        $this->merge(['transition_intents' => $this->route('turn')]);
    }
}
