<?php

namespace App\Nova;

use App\Nova\Filters\ChatbotUserLoggedIn;
use App\Nova\Filters\InteractedChatbotUser;
use Carlson\NovaLinkField\Link;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChatbotUser extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'OpenDialogAi\ConversationLog\ChatbotUser';

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'user_id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'user_id',
    ];

    /**
     * Default ordering for index query.
     *
     * @var array
     */
    public static $indexDefaultOrder = [
        'user_id' => 'desc'
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return 'Chatbot Users';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->getQuery()->orders = null;

        return $query
            ->select('chatbot_users.*')
            ->leftJoin('messages', 'messages.user_id', '=', 'chatbot_users.user_id')
            ->orderBy('messages.microtime', 'desc');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Gravatar::make('', 'email')->sortable()->hideFromIndex(),

            Link::make('Conversation Log', 'user_id')
                ->details([
                    'href' => '/admin/conversation-log/' . $this->user_id,
                    'text' => 'view ',
                    'newTab' => false,
                ])->hideFromIndex(),

            ID::make('User ID', 'user_id')->sortable()->fillUsing(function () {
                return;
            }),
            DateTime::make('First Seen', 'created_at')->sortable()->fillUsing(function () {
                return;
            }),

            DateTime::make('Last Seen', function () {
                if ($this->messages->count()) {
                    return $this->messages->first()->created_at->format('Y-m-d H:i:s');
                }
            })->sortable(),

            Text::make('First name', 'first_name')->sortable()->hideFromIndex(),
            Text::make('Last name', 'last_name')->sortable()->hideFromIndex(),
            Text::make('IP address', 'ip_address')->sortable()->hideFromIndex(),
            Text::make('Country', 'country')->sortable(),
            Text::make('Browser language', 'browser_language')->sortable()->hideFromIndex(),
            Text::make('Operating system', 'os')->sortable(),
            Text::make('Browser', 'browser')->hideFromIndex(),
            Text::make('Timezone', 'timezone')->hideFromIndex(),
            Text::make('Platform', 'platform')->hideFromIndex(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [
            new InteractedChatbotUser(),
            new ChatbotUserLoggedIn(),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
