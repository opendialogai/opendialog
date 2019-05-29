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

          ->push('status')

          ->push('yaml_validation_status')
          ->update('yaml_validation_status', function ($element) {
              $element->setTitle('Yaml');
          })

          ->push('yaml_schema_validation_status')
          ->update('yaml_schema_validation_status', function ($element) {
              $element->setTitle('Schema');
          })

          ->push('scenes_validation_status')
          ->update('scenes_validation_status', function ($element) {
              $element->setTitle('Scenes');
          })

          ->push('model_validation_status')
          ->update('model_validation_status', function ($element) {
              $element->setTitle('Model');
          })

          ->without(['model', 'notes']);
		}
}

class Conversation extends \OpenDialogAi\ConversationBuilder\Conversation implements \Terranet\Presentable\PresentableInterface
{
    use \Terranet\Presentable\PresentableTrait;

    protected $fillable = [
        'id',
        'name',
        'model',
        'notes',
        'status',
        'yaml_validation_status',
        'yaml_schema_validation_status',
        'scenes_validation_status',
        'model_validation_status',
    ];

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
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['yaml']));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['yaml']));

        $commonMarkConverter = new CommonMarkConverter([], $environment);

        return $commonMarkConverter->convertToHtml("```\n" . $this->presentable->model . "\n```");
    }
}
