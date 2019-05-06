<?php

namespace App\MessageBuilder;

use Laravel\Nova\Fields\Field;
use SimpleXMLElement;

class MessageBuilder extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'message-builder';

    /**
     * Resolve the field's value for display.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return void
     */
    public function resolveForDisplay($resource, $attribute = null): void
    {
        parent::resolveForDisplay($resource, $attribute);

        $message = new SimpleXMLElement($this->value);

        $messages = [];

        foreach ($message->children() as $item) {
            // Handle attribute messages.
            if ($item->getName() === 'attribute-message') {
                if (!empty((string) $item)) {
                    // Create a text message for string attributes.
                    $markup = "<text-message>{$item}</text-message>";
                    $message = new SimpleXMLElement($markup);
                    $messages[] = $this->parseMessage($message);
                }
            } else {
                // Convert the markup to the appropriate type of message.
                $messages[] = $this->parseMessage($item);
            }
        }

        $this->value = $messages;
    }

    /**
     * Parse XML markup and convert to a message array.
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function parseMessage(SimpleXMLElement $item): array
    {
        switch ($item->getName()) {
            case 'text-message':
                $data = (string)$item;
                break;

            case 'button-message':
                $buttons = [];
                foreach ($item->button as $button) {
                    $buttons[] = [
                        'text' => (string)$button->text,
                    ];
                }

                $data = [
                    'text' => (string)$item->text,
                    'buttons' => $buttons,
                ];
                break;

            case 'image-message':
                $data = [
                    'src' => (string)$item->src,
                    'link' => (string)$item->link,
                ];
                break;
        }

        return [
            'type' => $item->getName(),
            'data' => $data,
        ];
    }
}
