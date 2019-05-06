<?php

namespace App\Nova;

use App\MessageType\MessageType;
use App\Nova\Filters\MessageFromType;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;

class Message extends Resource
{
    public static $indexDefaultOrder = [
        'microtime' => 'asc'
    ];

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\ConversationLog\Message';

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
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * The number of resources to show per page via relationships.
     *
     * @var int
     */
    public static $perPageViaRelationship = 20;

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['chatbotUser'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('User ID', 'user_id')->hideFromIndex(),
            DateTime::make('Sent', 'microtime')->resolveUsing(function ($microtime) {
                return $microtime;
            })->sortable(),
            MessageType::make('From', 'author')->resolveUsing(function ($name) {
                if ($name == 'me') {
                    return 'user';
                } else {
                    return 'chatbot';
                }
            }),
            Text::make('Message', 'message'),
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
        return [
            new MessageFromType(),
        ];
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
