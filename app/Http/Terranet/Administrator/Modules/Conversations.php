<?php

namespace App\Http\Terranet\Administrator\Modules;

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
use App\Http\Terranet\Administrator\Widgets\RevisionViewer;
use App\Http\Terranet\Administrator\Widgets\StateLogs;

/**
 * Administrator Resource Conversation
 *
 * @package Terranet\Administrator
 */
class Conversations extends Scaffolding implements Navigable, Filtrable, Editable, Validable, Sortable, Exportable
{
    use HasFilters, HasForm, HasSortable, ValidatesForm, AllowFormats, AllowsNavigation;

    /**
     * The module Eloquent model
     *
     * @var string
     */
    protected $model = 'App\Http\Terranet\Administrator\Presentable\Conversation';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-comments'];
    }

    public function columns()
    {
        $columns = $this->scaffoldColumns();

        $columns->update('yaml_validation_status', function ($element) {
                $element->setTitle('Yaml');
        });

        $columns->update('yaml_schema_validation_status', function ($element) {
                $element->setTitle('Schema');
        });

        $columns->update('scenes_validation_status', function ($element) {
                $element->setTitle('Scenes');
        });

        $columns->update('model_validation_status', function ($element) {
                $element->setTitle('Model');
        });

        $columns->without(['model', 'notes']);

        return $columns;
    }

    public function form()
    {
        $form = $this->scaffoldForm();

        // Hide columns.
        $form->without(['id', 'outgoing_intents']);
        $form->update('status', function ($element) {
            $element->setInput(
                (new Hidden('status'))->setValue('invalid')
            );
        });
        $form->update('yaml_validation_status', function ($element) {
            $element->setInput(
                (new Hidden('yaml_validation_status'))->setValue('invalid')
            );
        });
        $form->update('yaml_schema_validation_status', function ($element) {
            $element->setInput(
                (new Hidden('yaml_schema_validation_status'))->setValue('invalid')
            );
        });
        $form->update('scenes_validation_status', function ($element) {
            $element->setInput(
                (new Hidden('scenes_validation_status'))->setValue('invalid')
            );
        });
        $form->update('model_validation_status', function ($element) {
            $element->setInput(
                (new Hidden('model_validation_status'))->setValue('invalid')
            );
        });

        return $form;
    }

    public function widgets()
    {
        $conversation = app('scaffold.model');

        # Add widgets.
        return $this->scaffoldWidgets()
            ->push(new RevisionViewer($conversation))
            ->push(new StateLogs($conversation));
    }
}
