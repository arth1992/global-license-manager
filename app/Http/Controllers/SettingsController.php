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
}
