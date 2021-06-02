<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use OpenDialogAi\Webchat\WebchatSetting;

class WebchatSettingValueRule implements Rule
{
    /** @var string */
    private string $message;

    /**
     * @var boolean
     */
    private bool $arrayRequest;
    /**
     * @var WebchatSetting
     */
    private $webchatSetting;

    /**
     * @param false $arrayRequest Whether the request holds an array of settings or not
     */
    public function __construct($arrayRequest = false)
    {
        $this->arrayRequest = $arrayRequest;
        $this->message = "";
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->webchatSetting = $this->getAssociatedWebchatSetting($attribute);

        switch ($this->webchatSetting->type) {
            case 'string':
                if (strlen($value) > 8192) {
                    $this->message = 'The maximum length for a string value is 8192.';
                    return false;
                }
                break;
            case 'number':
                if ($value && !is_numeric($value)) {
                    $this->message = 'This is not a valid number.';
                    return false;
                }
                break;
            case 'colour':
                if ($value && !preg_match('/#([a-f0-9]{3}){1,2}\b/i', $value)) {
                    $this->message = 'This is not a valid hex colour.';
                    return false;
                }
                break;
            case 'map':
                if ($value && json_decode($value) == null) {
                    $this->message = 'This is not a valid json value.';
                    return false;
                }
                break;
            case 'object':
                $this->message = 'Cannot update object value';
                return false;
            case 'boolean':
                if ($value != '0' && $value != '1' && $value != 'false' && $value != 'true') {
                    $this->message = 'This is not a valid boolean value.';
                    return false;
                }
                break;
            default:
                $this->message = sprintf("The webchat setting is of an unknown type: %s", $this->webchatSetting->type);
                return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return sprintf('Value for setting "%s" is not valid: %s', $this->webchatSetting->name, $this->message);
    }

    /**
     * @param string $attribute
     * @return mixed
     */
    private function getAssociatedWebchatSetting(string $attribute)
    {
        if ($this->arrayRequest) {
            $index = explode('.', $attribute)[0];
            $settingName = request()->input("{$index}.name");
            return WebchatSetting::where('name', $settingName)->first();
        } else {
            // This is bound to the route
            return request()->route()->webchat_setting;
        }
    }
}
