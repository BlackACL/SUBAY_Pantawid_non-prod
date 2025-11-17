<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetsItem extends Model
{
    protected $fillable = [
        'fets_document_id',
        'inventory_id',
        'property_no',
    ];

    /**
     * Get the FETS document this item belongs to
     */
    public function fetsDocument()
    {
        return $this->belongsTo(FetsDocument::class);
    }

    /**
     * Get the inventory item
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
