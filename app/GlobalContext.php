<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string value
 * @property string type
 */
class GlobalContext extends Model
{
    protected $fillable = ['name', 'value', 'type'];
}
