<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'razorpay_key_id',
        'razorpay_key_secret',
        'bank_details',
        'logo_url',
        'brand_color',
    ];

    /**
     * Get the active settings record.
     */
    public static function getActive()
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'razorpay_key_id' => env('RAZORPAY_KEY_ID'),
                'razorpay_key_secret' => env('RAZORPAY_KEY_SECRET'),
                'bank_details' => 'Please transfer to: Global Admission Manager INC. Account #123456789. Routing #987654321',
                'brand_color' => '#0f172a',
            ]
        );
    }
}
