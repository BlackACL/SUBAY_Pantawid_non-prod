<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'office_name',
        'office_code',
        'place_id',
    ];

    /**
     * Get the place (location) for this office
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get users assigned to this office
     */
    public function users()
    {
        return $this->hasMany(User::class, 'office_id');
    }

    /**
     * Get inventory items in this office
     */
    public function inventoryItems()
    {
        return $this->hasMany(Inventory::class, 'office_id');
    }
}
