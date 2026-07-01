<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingLog extends Model
{
    protected $fillable = [
        'license_id',
        'status',
        'notes',
        'sync_month',
        'sync_year',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
