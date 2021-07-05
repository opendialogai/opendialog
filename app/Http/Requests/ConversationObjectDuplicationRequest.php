<?php

namespace App\Http\Requests;

use App\Rules\OdId;
use Illuminate\Foundation\Http\FormRequest;
use OpenDialogAi\Core\Conversation\ConversationObject;

class ConversationObjectDuplicationRequest extends FormRequest
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
        return $this->odIdRule() + [
            'name' => ['bail', 'string', 'filled']
        ];
    }

    /**
     * Finds and sets a unique OD ID and name for the given object, ensuring that it is unique with respect to
     * it's parent scope
     *
     * @param ConversationObject $object
     * @param ConversationObject|null $parent
     * @return ConversationObject
     */
    public function setUniqueOdId(
        ConversationObject $object,
        ?ConversationObject $parent = null
    ): ConversationObject {
        $odId = $this->get('od_id', sprintf("%s_copy", $object->getOdId()));
        $originalOdId = $odId;

        $i = 1;
        while (!OdId::isOdIdUniqueWithinParentScope($odId, $parent)) {
            $i++;
            $odId = sprintf("%s_%d", $originalOdId, $i);
        }

        if ($i > 1) {
            $name = $this->get('name', sprintf("%s copy %d", $object->getName(), $i));
        } else {
            $name = $this->get('name', sprintf("%s copy", $object->getName()));
        }

        $object->setOdId($odId);
        $object->setName($name);

        return $object;
    }
}
