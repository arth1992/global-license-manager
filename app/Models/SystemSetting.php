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
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'company_address',
        'gstin',
        'state_code',
        'state_name',
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
                'company_address' => "123 Tech Park, Indiranagar\nBangalore, Karnataka - 560038",
                'gstin' => '29AAAAA1111A1Z1',
                'state_code' => '29',
                'state_name' => 'Karnataka',
            ]
        );
    }
}
