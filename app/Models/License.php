<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'client_name',
        'client_email',
        'max_tenants',
        'features',
        'status',
        'fingerprint',
        'domain',
        'license_key',
        'expires_at',
        'activated_at',
    ];

    protected $casts = [
        'features'     => 'array',
        'expires_at'   => 'datetime',
        'activated_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (License $license) {
            if (empty($license->uuid)) {
                $license->uuid = (string) Str::uuid();
            }
        });
    }

    public function activationRequests(): HasMany
    {
        return $this->hasMany(ActivationRequest::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LicenseLog::class);
    }

    public function billingLogs(): HasMany
    {
        return $this->hasMany(BillingLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function log(string $event, ?string $ip = null, ?string $fingerprint = null, bool $success = true, ?string $notes = null): void
    {
        $this->logs()->create([
            'event'       => $event,
            'ip_address'  => $ip,
            'fingerprint' => $fingerprint,
            'is_success'  => $success,
            'notes'       => $notes,
        ]);
    }
}
