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
 * Administrator Resource ChatbotUser
 *
 * @package Terranet\Administrator
 */
class ChatbotUsers extends Scaffolding implements Navigable, Filtrable, Editable, Validable, Sortable, Exportable
{
    use HasFilters, HasForm, HasSortable, ValidatesForm, AllowFormats, AllowsNavigation;

    /**
     * The module Eloquent model
     *
     * @var string
     */
    protected $model = 'App\Http\Terranet\Administrator\Presentable\ChatbotUser';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-user-o'];
    }

    public function columns()
    {
        $columns = $this->scaffoldColumns();

        $columns->without([
            'email',
            'first_name',
            'last_name',
            'ip_address',
            'browser_language',
            'browser',
            'timezone',
            'platform'
        ]);

        $columns->update('user_id', function(Element $element) {
            $element->setTitle('User Id');
        });

        $columns->update('os', function(Element $element) {
            $element->setTitle('Operating System');
        });

        $columns->update('created_at', function(Element $element) {
            $element->setTitle('First Seen');
            $element->sortable();
        });
        $columns->move('created_at', 'after:os');


        return $columns;
    }

    public function form()
    {
        $form = $this->scaffoldForm();

        // Hide columns.
        // $form->without('id');
        // $form->update('status', function ($element) {
            // $element->setInput(
                // new Hidden('status')
            // );
        // });
        //
        return $form;
    }
}
