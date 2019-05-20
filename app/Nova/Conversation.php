<?php

namespace App\Nova;

use App\RevisionViewer\RevisionViewer;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;
use OpenDialogAi\ResponseEngine\OutgoingIntent;
use Symfony\Component\Yaml\Yaml;

class Conversation extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\ConversationBuilder\Conversation';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')
                ->sortable()
                ->withMeta(['type' => 'hidden']),
            Code::make('Model')
                ->help('Paste/edit the YAML representation of the conversation model here')
                ->language('yaml')
                ->onlyOnForms(),
            Textarea::make('Notes'),
            Status::make('Status')
                ->exceptOnForms()
                ->failedWhen(['invalid'])
                ->loadingWhen(['imported']),
            Status::make('Yaml', 'yaml_validation_status')
                ->exceptOnForms()
                ->failedWhen(['invalid'])
                ->loadingWhen(['waiting']),
            Status::make('Schema', 'yaml_schema_validation_status')
                ->exceptOnForms()
                ->failedWhen(['invalid'])
                ->loadingWhen(['waiting']),
            Status::make('Scenes', 'scenes_validation_status')
                ->exceptOnForms()
                ->failedWhen(['invalid'])
                ->loadingWhen(['waiting']),
            Status::make('Model', 'model_validation_status')
                ->exceptOnForms()
                ->failedWhen(['invalid'])
                ->loadingWhen(['waiting']),
            Text::make('Outgoing intents', function () {
                $output = '';

                $yaml = Yaml::parse($this->model)['conversation'];

                foreach ($yaml['scenes'] as $sceneId => $scene) {
                    foreach ($scene['intents'] as $intent) {
                        foreach ($intent as $tag => $value) {
                            if ($tag == 'b') {
                                foreach ($value as $key => $intent) {
                                    if ($key == 'i') {
                                        $outgoingIntent = OutgoingIntent::where('name', $intent)->first();

                                        if ($outgoingIntent) {
                                            $output .= '<div><a href="/admin/resources/outgoing-intents/' . $outgoingIntent->id . '">' . $intent . '</a></div>';
                                        } else {
                                            $output .= '<div><a href="/admin/resources/outgoing-intents/new">' . $intent . '</a></div>';
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
            })
                ->onlyOnDetail()
                ->asHtml(),
            HasMany::make(__('State Logs'), 'conversationStateLogs', ConversationStateLog::class),

            RevisionViewer::make(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            new Actions\PublishConversation(),
            new Actions\UnpublishConversation(),
        ];
    }
}
