<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class InteractedChatbotUser extends Filter
{
    public $name = 'Interacted with chatbot';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        if ($value == 'yes') {
            return $query->whereRaw('(select count(user_id) from messages where messages.user_id = ' .
                'chatbot_users.user_id and author != "them") > 0');
        } elseif ($value == 'no') {
            return $query->whereRaw('(select count(user_id) from messages where messages.user_id = ' .
                'chatbot_users.user_id and author != "them") = 0');
        }
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Yes' => 'yes',
            'No' => 'no',
        ];
    }

    public function default()
    {
        return ['Yes' => 'yes'];
    }
}
