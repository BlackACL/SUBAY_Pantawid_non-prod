<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperFetsDocument
 */
class FetsDocument extends Model
{
    protected $fillable = [
        'to_receiver',
        'to_office',
        'remarks',
        'status',
        'file_name',
        'file_path',
        'form_data',
        'user_id',
        'transfer_movement',
        'repair_destination',
        // Normalized foreign keys
        'to_user_id',
        'to_office_id',
    ];

    public function submitter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected $casts = [
        'form_data' => 'array',
    ];

    /**
     * 🌍 Normalized relationships (NEW)
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function toOffice()
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }

    public function fetsItems()
    {
        return $this->hasMany(FetsItem::class);
    }

    /**
     * Get all inventory items through the junction table
     */
    public function inventoryItems()
    {
        return $this->belongsToMany(Inventory::class, 'fets_items', 'fets_document_id', 'inventory_id');
    }
}
