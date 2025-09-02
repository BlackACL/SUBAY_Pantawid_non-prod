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
        'property_no',
        'action',
        'actor',
        'actor_role',
        'remarks',
        'created_at',
    ];
}