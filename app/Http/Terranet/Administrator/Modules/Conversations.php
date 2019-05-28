<?php

namespace App\Http\Terranet\Administrator\Modules;

use Symfony\Component\Yaml\Yaml;
use Terranet\Administrator\Columns\Element;
use Terranet\Administrator\Contracts\Module\Editable;
use Terranet\Administrator\Contracts\Module\Exportable;
use Terranet\Administrator\Contracts\Module\Filtrable;
use Terranet\Administrator\Contracts\Module\Navigable;
use Terranet\Administrator\Contracts\Module\Sortable;
use Terranet\Administrator\Contracts\Module\Validable;
use Terranet\Administrator\Scaffolding;
use Terranet\Administrator\Traits\Module\AllowFormats;
use Terranet\Administrator\Traits\Module\AllowsNavigation;
use Terranet\Administrator\Traits\Module\HasFilters;
use Terranet\Administrator\Traits\Module\HasForm;
use Terranet\Administrator\Traits\Module\HasSortable;
use Terranet\Administrator\Traits\Module\ValidatesForm;

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
    protected $model = 'App\Http\Terranet\Administrator\Modules\Conversation';

    public function linkAttributes()
    {
        return ['icon' => 'fa fa-comments'];
    }

		public function columns()
		{
				return

        $this
          ->scaffoldColumns()
          ->without(['model', 'notes']);
		}
}

class Conversation extends \OpenDialogAi\ConversationBuilder\Conversation implements \Terranet\Presentable\PresentableInterface
{
    use \Terranet\Presentable\PresentableTrait;

    protected $presenter = ConversationPresenter::class;
}

class ConversationPresenter extends \Terranet\Presentable\Presenter
{
    public function title()
    {
        return link_to_route('scaffold.view', $this->presentable->name, [
        	'module' => 'conversations',
        	'id' => $this->presentable
    	]);
    }

    public function model()
    {
        return '<p class="text-muted">' . $this->presentable->model . '</p>';
    }

}

