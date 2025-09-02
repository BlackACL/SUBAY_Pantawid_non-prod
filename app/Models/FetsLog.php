<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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