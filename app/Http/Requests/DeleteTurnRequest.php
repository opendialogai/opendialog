<?php

namespace App\Http\Requests;

use App\Rules\TurnInTransition;
use Illuminate\Foundation\Http\FormRequest;

class DeleteTurnRequest extends FormRequest
{
    use DeleteObjectRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->prepareRules(TurnInTransition::class);
    }

    /**
     * Fetches the conversation ID from the route param
     */
    protected function prepareForValidation()
    {
        $this->prepareValidation('turn');
    }
}
