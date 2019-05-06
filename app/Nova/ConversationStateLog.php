<?php

namespace App\Nova;

use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Http\Requests\NovaRequest;


class ConversationStateLog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\ConversationBuilder\ConversationStateLog';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Do not display this resource on the sidebar.
     */
    public static $displayInNavigation = false;

    /**
     * Set display name.
     */
    public static function label()
    {
        return 'State Logs';
    }

    /**
     * Set display name.
     */
    public static function singularLabel()
    {
        return 'State Log';
    }

    /**
     * Don't allow creating items.
     */
    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    /**
     * Don't allow deleting items.
     */
    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    /**
     * Don't allow updating items.
     */
    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'message',
        'type',
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
            DateTime::make('Date', 'created_at')
                ->sortable(),
            Text::make('Type')
                ->sortable(),
            Text::make('Message'),
            HasOne::make('Conversation'),
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
