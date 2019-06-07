<?php

namespace App\Http\Terranet\Administrator\Modules;

use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\IndentedCode;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
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
use Terranet\Administrator\Form\Type\Select;
use Terranet\Administrator\Scaffolding;
use Terranet\Administrator\Traits\Module\AllowFormats;
use Terranet\Administrator\Traits\Module\AllowsNavigation;
use Terranet\Administrator\Traits\Module\HasFilters;
use Terranet\Administrator\Traits\Module\HasForm;
use Terranet\Administrator\Traits\Module\HasSortable;
use Terranet\Administrator\Traits\Module\ValidatesForm;

/**
 * Administrator Resource Message Template
 *
 * @package Terranet\Administrator
 */
class MessageTemplates extends Scaffolding implements Navigable, Filtrable, Editable, Validable, Sortable, Exportable
{
    use HasFilters, HasForm, HasSortable, ValidatesForm, AllowFormats, AllowsNavigation;

    /**
     * The module Eloquent model
     *
     * @var string
     */
    protected $model = 'App\Http\Terranet\Administrator\Presentable\MessageTemplate';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-comment'];
    }

    public function columns()
    {
        $columns = $this->scaffoldColumns();

        $columns->without('id');
        $columns->without('conditions');
        $columns->without('outgoing_intent_id');

        return $columns;
    }

    public function form()
    {
        $form = $this->scaffoldForm();

        $form->without(['outgoing_intent']);

        $form->update('outgoing_intent_id', function ($element) {
            $outgoingIntents = OutgoingIntent::all();

            $outgoingIntentOptions = [];
            foreach ($outgoingIntents as $outgoingintent) {
                $outgoingIntentOptions[$outgoingintent->id] = $outgoingintent->name;
            }

            $element->setInput(
                (new Select('outgoing_intent_id'))
                    ->setOptions($outgoingIntentOptions)
            );
        });

        return $form;
    }
}
