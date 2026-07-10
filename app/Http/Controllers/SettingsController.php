<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'settings' => SystemSetting::getActive()
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string',
            'brand_color' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048', // max 2MB
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:255',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|max:255',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'gstin' => 'nullable|string|max:20',
            'state_code' => 'nullable|string|max:10',
            'state_name' => 'nullable|string|max:255',
        ]);

        $settings = SystemSetting::getActive();
        
        if ($request->hasFile('logo')) {
            if ($settings->logo_url) {
                Storage::disk('public')->delete($settings->logo_url);
            }
            $path = $request->file('logo')->store('branding', 'public');
            $validated['logo_url'] = $path;
        }

        $settings->update($validated);

        return back()->with('success', 'System settings updated successfully.');
    }

    public function sendTestEmail(Request $request)
    {
        try {
            $settings = SystemSetting::getActive();
            $toEmail = $settings->smtp_from_address;
            
            if (!$toEmail) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please save your "From Address" before sending a test email.'
                ], 422);
            }

            if ($settings->smtp_host) {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.host' => $settings->smtp_host,
                    'mail.mailers.smtp.port' => (int) $settings->smtp_port,
                    'mail.mailers.smtp.username' => $settings->smtp_username,
                    'mail.mailers.smtp.password' => $settings->smtp_password,
                    'mail.mailers.smtp.encryption' => $settings->smtp_encryption === 'none' ? null : $settings->smtp_encryption,
                    'mail.from.address' => $settings->smtp_from_address,
                    'mail.from.name' => $settings->smtp_from_name ?: config('mail.from.name'),
                ]);
            }
            
            app('mail.manager')->forgetMailers();

            \Illuminate\Support\Facades\Mail::raw(
                "Congratulations!\n\nYour outgoing SMTP server connection has been successfully verified by Global License Manager.\n\nConfigured SMTP Server: {$settings->smtp_host}:{$settings->smtp_port} ({$settings->smtp_encryption})\nSender Identity: {$settings->smtp_from_name} <{$settings->smtp_from_address}>\n\nHave a great day!",
                function ($message) use ($toEmail) {
                    $message->to($toEmail)->subject("GLM SMTP Connection Verification");
                }
            );

            return response()->json([
                'status' => 'success',
                'message' => "Test email successfully sent to {$toEmail}!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
