<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivationRequest extends Model
{
    protected $fillable = [
        'request_id',
        'license_id',
        'fingerprint',
        'domain',
        'ip_address',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function markAsUsed(): void
    {
        $this->update(['status' => 'used']);
    }
}
