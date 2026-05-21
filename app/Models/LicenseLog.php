<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'license_id',
        'event',
        'ip_address',
        'fingerprint',
        'is_success',
        'notes',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
