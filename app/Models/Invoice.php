<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id',
        'invoice_number',
        'invoice_month',
        'invoice_year',
        'base_fee',
        'applicant_count',
        'school_breakdown',
        'applicant_fee',
        'discount_applied',
        'total_amount',
        'status',
        'pdf_path',
        'receipt_pdf_path',
        'transaction_id',
    ];

    protected $casts = [
        'school_breakdown' => 'array',
    ];

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}
