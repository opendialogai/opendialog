<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ChatbotUserLoggedIn extends Filter
{
    public $name = 'Type';

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
        if ($value == 'registered') {
            return $query->whereColumn('chatbot_users.user_id', 'chatbot_users.email');
        }

        return $query->whereColumn('chatbot_users.user_id', '!=', 'chatbot_users.email')
            ->orWhereNull('chatbot_users.email');
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
            'Registered' => 'registered',
            'Non registered' => 'non-registered',
        ];
    }
}
