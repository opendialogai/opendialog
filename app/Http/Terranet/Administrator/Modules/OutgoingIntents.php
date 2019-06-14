<?php

namespace App\Http\Terranet\Administrator\Modules;

use App\Http\Terranet\Administrator\Presentable\MessageTemplatePresenter;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Symfony\Component\Yaml\Yaml;
use Terranet\Administrator\Columns\Element;
use Terranet\Administrator\Contracts\Module\Editable;
use Terranet\Administrator\Contracts\Module\Exportable;
use Terranet\Administrator\Contracts\Module\Filtrable;
use Terranet\Administrator\Contracts\Module\Navigable;
use Terranet\Administrator\Contracts\Module\Sortable;
use Terranet\Administrator\Contracts\Module\Validable;
use Terranet\Administrator\Form\Type\Hidden;
use Terranet\Administrator\Scaffolding;
use Terranet\Administrator\Traits\Module\AllowFormats;
use Terranet\Administrator\Traits\Module\AllowsNavigation;
use Terranet\Administrator\Traits\Module\HasFilters;
use Terranet\Administrator\Traits\Module\HasForm;
use Terranet\Administrator\Traits\Module\HasSortable;
use Terranet\Administrator\Traits\Module\ValidatesForm;
use App\Http\Terranet\Administrator\Widgets\MessageTemplates;

/**
 * Administrator Resource Outgoing Intent
 *
 * @package Terranet\Administrator
 */
class OutgoingIntents extends Scaffolding implements Navigable, Filtrable, Editable, Validable, Sortable, Exportable
{
    use HasFilters, HasForm, HasSortable, ValidatesForm, AllowFormats, AllowsNavigation;

    /**
     * The module Eloquent model
     *
     * @var string
     */
    protected $model = 'OpenDialogAi\ResponseEngine\OutgoingIntent';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-cog'];
    }

    public function columns()
    {
        $columns = $this->scaffoldColumns();

        $columns->push('messageTemplates', function (Element $element) {
            $element->setTitle('Related Message Templates');

            $element->display(function () {
                $messages = [];

                foreach ($this->messageTemplates as $messageTemplate) {
                    $m = MessageTemplatePresenter::resolveForDisplay($messageTemplate->message_markup);
                    $messages = array_merge($messages, $m);
                }

                return view('admin.messageViewer', ['messages' => $messages])->render();
            });
        });

        return $columns;
    }

    public function form()
    {
        $form = $this->scaffoldForm();

        return $form;
    }

    public function widgets()
    {
        $outgoingIntent = app('scaffold.model');

        # Add widgets.
        return $this->scaffoldWidgets()
            ->push(new MessageTemplates($outgoingIntent));
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
