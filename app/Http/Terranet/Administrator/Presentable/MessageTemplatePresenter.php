<?php

namespace App\Http\Terranet\Administrator\Presentable;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use SimpleXMLElement;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Symfony\Component\Yaml\Yaml;
use Terranet\Presentable\Presenter;

class MessageTemplatePresenter extends Presenter
{
    public function title()
    {
        return link_to_route('scaffold.view', $this->presentable->name, [
            'module' => 'messagetemplates',
            'id' => $this->presentable
        ]);
    }

    public function conditions()
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['yaml']));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['yaml']));

        $commonMarkConverter = new CommonMarkConverter([], $environment);

        return $commonMarkConverter->convertToHtml("```\n" . $this->presentable->conditions . "\n```");
    }

    public function messageMarkup()
    {
        $messages = $this->resolveForDisplay($this->presentable->message_markup);
        return view('admin.messageViewer', ['messages' => $messages])->render();
    }

    public function outgoingIntent()
    {
        return '<div><a href="/' . config('administrator.prefix', 'cms') . '/outgoing_intents/' . $this->presentable->outgoingIntent->id . '">' . $this->presentable->outgoingIntent->name . '</a></div>';
    }

    /**
     * Resolve the field's value for display.
     *
     * @param  string  $value
     * @param  string|null  $attribute
     * @return array
     */
    private function resolveForDisplay($value)
    {
        $message = new SimpleXMLElement($value);

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

        return $messages;
    }

    /**
     * Parse XML markup and convert to a message array.
     *
     * @param SimpleXMLElement $item
     * @return array
     */
    private function parseMessage(SimpleXMLElement $item)
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

            case 'rich-message':
                $buttons = [];
                foreach ($item->button as $button) {
                    $buttons[] = [
                        'text' => (string)$button->text,
                    ];
                }

                $data = [
                    'title' => (string)$item->title,
                    'subtitle' => (string)$item->subtitle,
                    'text' => (string)$item->text,
                    'buttons' => $buttons,
                    'image' => [
                        'src' => (string)$item->image->src,
                        'url' => (string)$item->image->url,
                    ],
                ];
                break;

            case 'list-message':
                $viewType = ($item['view-type']) ? (string)$item['view-type'] : 'horizontal';

                $items = [];
                foreach ($item->item as $i) {
                    $items[] = $this->parseMessage($i->children()[0]);
                }

                $data = [
                    'view_type' => $viewType,
                    'items' => $items,
                ];
                break;
        }

        return [
            'type' => $item->getName(),
            'data' => $data,
        ];
    }
}
