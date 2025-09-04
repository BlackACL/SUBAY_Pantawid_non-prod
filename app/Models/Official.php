<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    protected $fillable = [
        'province',
        'role',
        'fullname',
        'active',
    ];
}
