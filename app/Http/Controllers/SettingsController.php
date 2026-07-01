<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
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
        ]);

        $settings = SystemSetting::getActive();
        $settings->update($validated);

        return back()->with('success', 'System settings updated successfully.');
    }
}
