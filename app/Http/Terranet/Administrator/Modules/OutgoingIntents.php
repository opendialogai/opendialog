<?php

namespace App\Http\Terranet\Administrator\Modules;

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
}
