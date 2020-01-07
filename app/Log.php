<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string request_id
 * @property string user_id
 * @property string message
 * @property string context
 */
class Log extends Model
{
    protected $fillable = ['request_id', 'user_id', 'message', 'context'];

    protected $casts = [
        'context' => 'array'
    ];
}
