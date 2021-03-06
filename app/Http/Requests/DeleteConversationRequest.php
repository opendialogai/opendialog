<?php

namespace App\Http\Requests;

use App\Rules\ConversationInTransition;
use Illuminate\Foundation\Http\FormRequest;

class DeleteConversationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'transition_intents' => [new ConversationInTransition()]
        ];
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareForValidation()
    {
        $this->merge(['transition_intents' => $this->route('conversation')]);
    }
}
