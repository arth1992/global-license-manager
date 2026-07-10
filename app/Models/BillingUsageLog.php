<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingUsageLog extends Model
{
    protected $fillable = [
        'license_id',
        'active_applicant_count',
        'school_breakdown',
        'sync_month',
        'sync_year',
        'status',
        'error_message',
    ];

    protected $casts = [
        'school_breakdown' => 'array',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
