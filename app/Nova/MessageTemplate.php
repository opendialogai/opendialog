<?php

namespace App\Nova;

use App\MessageBuilder\MessageBuilder;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\BelongsTo;
use OpenDialogAi\ResponseEngine\Rules\MessageXML;
use OpenDialogAi\ResponseEngine\Rules\MessageConditions;

class MessageTemplate extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\ResponseEngine\MessageTemplate';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * Do not display this resource on the sidebar.
     */
    public static $displayInNavigation = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name',
    ];

    /**
     * The human-friendly label.
     */
    public static function label()
    {
        return 'Message Templates';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            BelongsTo::make('Outgoing Intent', 'outgoingIntent', 'App\Nova\OutgoingIntent'),
            Text::make('Name')->sortable(),
            DateTime::make('Created At')
                ->sortable()
                ->onlyOnIndex(),
            DateTime::make('Updated At')
                ->sortable()
                ->onlyOnIndex(),
            Code::make('Conditions', 'conditions')
                ->hideFromIndex()
                ->language('yaml')
                ->rules(new MessageConditions()),
            MessageBuilder::make('Message Mark-up', 'message_markup')
                ->onlyOnDetail()
                ->rules('required', new MessageXML()),
            Code::make('Message Mark-up', 'message_markup')
                ->language('xml')
                ->onlyOnForms()
                ->rules('required', new MessageXML())
                ->withMeta(['value' => $this->message_markup ?? "<message>\n\n</message>"]),
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
        return [];
    }
}
