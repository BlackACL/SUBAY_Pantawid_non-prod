<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperFetsDocument
 */
class FetsDocument extends Model
{
    protected $fillable = [
        'fets_no',
        'property_no',
        'to_receiver',
        'to_office',
        'remarks',
        'status',
        'file_name',
        'file_path',
        'form_data',
        'user_id',
    ];

public function submitter() {
    return $this->belongsTo(User::class, 'user_id');
}

public function verifier() {
    return $this->belongsTo(User::class, 'verified_by');
}

public function approver() {
    return $this->belongsTo(User::class, 'approved_by');
}

    protected $casts = [
        'form_data' => 'array',
    ];
}
