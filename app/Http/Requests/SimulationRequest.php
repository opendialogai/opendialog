<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenDialogAi\ConversationEngine\Util\ConversationalState;
use OpenDialogAi\Core\Conversation\Intent;

class SimulationRequest extends FormRequest
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
            "scenario" => "string|nullable",
            "conversation" => "string|nullable",
            "scene" => "string|nullable",
            "turn" => "string|nullable",
            "intent" => "string|nullable",
            "speaker" => ['required', 'string', Rule::in(Intent::VALID_SPEAKERS)],
            "turn_status" => ['required', 'string', Rule::in(ConversationalState::VALID_TURN_STATUSES)],
        ];
    }
}
