<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperInventory
 */
class Inventory extends Model
{
    protected $table = 'inventory';

    /**
     * Columns that can be mass-assigned.
     */
    protected $fillable = [
        'FUND_CODE',
        'PROPERTY_STATUS',
        'ARTICLE_DESCRIPTION',
        'GENERAL_DESCRIPTION',
        'SERIAL_NO',
        'PROPERTY_NO',
        'PAR_NO',
        'PAR_DATE',
        'UNIT',
        'QTY',
        'ACQUISITION_COST',
        'ACQUISITION_DATE',
        'RECEIVER',
        'SUBPAR',
        'ACCOUNT_CODE',
        'WARRANTY',
        'OFFICE',
        'FOUND_IN_STATION',
        'LABELLED',
        'DPO_REMARKS',
        'source_file',
        'imported_at',
    ];

    /**
     * Attribute casting.
     */
protected $casts = [
    'imported_at' => 'datetime',
];


    /**
     * Optional: if you want to automatically update timestamps
     * when using Eloquent create/update methods.
     */
    public $timestamps = true;
}
