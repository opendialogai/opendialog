<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenDialogAi\Core\Conversation\ConversationObject;

class TurnRequest extends FormRequest
{
    use ConversationObjectRequestTrait;

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
        /** @var ConversationObject $parent */
        $parent = $this->route('scene');

        $currentUid = $this->get('id');

        return $this->odIdRule($parent, $currentUid) + [
            'id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'interpreter' => 'nullable|string',
            'behaviors' => 'array',
            'conditions' => 'array',
            'valid_origins' => 'array',
            'intents' => 'array'
        ];
    }
}
