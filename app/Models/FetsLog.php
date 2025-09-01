<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperFetsLog
 */
class FetsLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'fets_no',
        'property_no',
        'action',
        'actor',
        'actor_role',
        'remarks',
        'created_at',
    ];
}
