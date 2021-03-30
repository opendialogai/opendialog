<?php


namespace App\Http\Requests;

use App\Rules\Status;
use Illuminate\Foundation\Http\FormRequest;

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
            "speaker" => "required|string",
            "intent_is_request" => "required|boolean",
        ];
    }
}
