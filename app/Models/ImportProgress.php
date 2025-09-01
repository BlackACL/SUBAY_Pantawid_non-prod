<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperImportProgress
 */
class ImportProgress extends Model
{
    protected $table = 'import_progress';

    protected $fillable = [
        'type',
        'total',
        'processed',
    ];
}
