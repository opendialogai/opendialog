<?php

namespace App\Http\Terranet\Administrator\Modules;

use Terranet\Administrator\Columns\Element;
use Terranet\Administrator\Contracts\Module\Editable;
use Terranet\Administrator\Contracts\Module\Exportable;
use Terranet\Administrator\Contracts\Module\Filtrable;
use Terranet\Administrator\Contracts\Module\Navigable;
use Terranet\Administrator\Contracts\Module\Sortable;
use Terranet\Administrator\Contracts\Module\Validable;
use Terranet\Administrator\Filters\Scope;
use Terranet\Administrator\Form\Type\Boolean;
use Terranet\Administrator\Form\Type\Hidden;
use Terranet\Administrator\Form\Type\Number;
use Terranet\Administrator\Scaffolding;
use Terranet\Administrator\Traits\Module\AllowFormats;
use Terranet\Administrator\Traits\Module\AllowsNavigation;
use Terranet\Administrator\Traits\Module\HasFilters;
use Terranet\Administrator\Traits\Module\HasForm;
use Terranet\Administrator\Traits\Module\HasSortable;
use Terranet\Administrator\Traits\Module\ValidatesForm;
use App\Http\Terranet\Administrator\Widgets\ChildWebchatSettings;

/**
 * Administrator Resource ChatbotUser
 *
 * @package Terranet\Administrator
 */
class WebchatSettings extends Scaffolding implements Navigable, Filtrable, Editable, Validable, Sortable, Exportable
{
    use HasFilters, HasForm, HasSortable, ValidatesForm, AllowFormats, AllowsNavigation;

    /**
     * The module Eloquent model
     *
     * @var string
     */
    protected $model = 'App\Http\Terranet\Administrator\Presentable\WebchatSetting';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-pencil'];
    }

    public function columns()
    {
        return $this->scaffoldColumns()->without(['id']);
    }

    public function form()
    {
        $form = $this->scaffoldForm();

        $form->insert('displayName', 0, function ($element) {
            $element->setAttributes(['disabled' => true]);
            $element->setTitle('Name');
            $element->setValue(app('scaffold.model')->name);
        });

        $form->update('name', function ($element) {
            $element->setInput(
                new Hidden('name')
            );
        });

        // Use appropriate widget.
        $form->update('value', function ($element) {
          switch (app('scaffold.model')->type) {
              case 'boolean':
                  $element->setInput(
                      new Boolean('value')
                  );
                  break;
              case 'number':
                  $element->setInput(
                      new Number('value')
                  );
                  break;
              case 'object':
                  $element->setInput(
                      new Hidden('value')
                  );
                  break;
              default:
                  break;

          }
        });

        return $form;
    }

    public function widgets()
    {
        $webchatSetting = app('scaffold.model');

        # Add widgets.
        return $this->scaffoldWidgets()
            ->push(new ChildWebchatSettings($webchatSetting));
    }
}
