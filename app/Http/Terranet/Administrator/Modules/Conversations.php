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
    protected $model = 'App\Http\Terranet\Administrator\Modules\Conversation';

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
        $form->without('id');
        $form->update('status', function ($element) {
            $element->setInput(
                new Hidden('status')
            );
        });
        $form->update('yaml_validation_status', function ($element) {
            $element->setInput(
                new Hidden('yaml_validation_status')
            );
        });
        $form->update('yaml_schema_validation_status', function ($element) {
            $element->setInput(
                new Hidden('yaml_schema_validation_status')
            );
        });
        $form->update('scenes_validation_status', function ($element) {
            $element->setInput(
                new Hidden('scenes_validation_status')
            );
        });
        $form->update('model_validation_status', function ($element) {
            $element->setInput(
                new Hidden('model_validation_status')
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
        'outgoingIntents',
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

    public function outgoingIntents()
    {
				$output = '';

				$yaml = Yaml::parse($this->presentable->model)['conversation'];

				foreach ($yaml['scenes'] as $sceneId => $scene) {
						foreach ($scene['intents'] as $intent) {
								foreach ($intent as $tag => $value) {
										if ($tag == 'b') {
												foreach ($value as $key => $intent) {
														if ($key == 'i') {
																$outgoingIntent = OutgoingIntent::where('name', $intent)->first();

																if ($outgoingIntent) {
																		$output .= '<div><a href="/cms/outgoing_intents/' . $outgoingIntent->id . '">' . $intent . '</a></div>';
																} else {
																		$output .= '<div><a href="/cms/outgoing_intents/new">' . $intent . '</a></div>';
																}
																break;
														}
												}
												break;
										}
								}
						}
				}

				return $output;
		}
}
