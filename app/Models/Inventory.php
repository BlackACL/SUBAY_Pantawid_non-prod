<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'units'; // if your table name is 'units'
    protected $fillable = ['receiver', 'updated_at']; // add more if needed
}
