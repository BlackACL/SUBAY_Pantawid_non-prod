<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficialHistory extends Model
{
    // ✅ Point to your existing table
    protected $table = 'officials_history';

    protected $fillable = [
        'official_id',
        'action',
        'old_value',
        'new_value',
        'changed_by',
    ];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
