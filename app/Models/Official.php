<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Official extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'province',   // nullable for non-provincial roles (Regional DPSC, Head of Property)
        'role',       // Provincial DPSC, Regional DPSC, Head of Property
        'fullname',   // official's full name
        'active',     // boolean
        'user_id', // NEW
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Booted method to automatically log changes to officials
     */
    protected static function booted()
    {
        // Log creation
        static::created(function ($official) {
            OfficialHistory::create([
                'official_id' => $official->id,
                'action' => 'added',
                'new_value' => json_encode($official->toArray()),
                'changed_by' => Auth::id(),
            ]);
        });

        // Log updates
        static::updated(function ($official) {
            OfficialHistory::create([
                'official_id' => $official->id,
                'action' => 'updated',
                'old_value' => json_encode($official->getOriginal()),
                'new_value' => json_encode($official->getChanges()),
                'changed_by' => Auth::id(),
            ]);
        });

        // Log deletion
        static::deleted(function ($official) {
            OfficialHistory::create([
                'official_id' => $official->id,
                'action' => 'deleted',
                'old_value' => json_encode($official->toArray()),
                'changed_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Relationship to history
     */
    public function history()
    {
        return $this->hasMany(OfficialHistory::class);
    }
}
