<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => CompanySetting::get('company_name', 'Havells India Ltd'),
            'company_gstin' => CompanySetting::get('company_gstin', ''),
            'company_address' => CompanySetting::get('company_address', ''),
            'company_state' => CompanySetting::get('company_state', 'Delhi'),
            'company_phone' => CompanySetting::get('company_phone', ''),
            'company_email' => CompanySetting::get('company_email', ''),
            'whatsapp_api_url' => CompanySetting::get('whatsapp_api_url', ''),
            'whatsapp_api_token' => CompanySetting::get('whatsapp_api_token', ''),
            'whatsapp_phone_number_id' => CompanySetting::get('whatsapp_phone_number_id', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_gstin' => 'nullable|string|size:15',
            'company_address' => 'nullable|string',
            'company_state' => 'required|string|max:100',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email',
            'whatsapp_api_url' => 'nullable|url',
            'whatsapp_api_token' => 'nullable|string',
            'whatsapp_phone_number_id' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:100',
            'upi_qr_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('upi_qr_image')) {
            $path = $request->file('upi_qr_image')->store('settings', 'public');
            $validated['upi_qr_image'] = $path;
        }

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                CompanySetting::set($key, $value);
            }
        }

        return back()->with('success', 'Settings updated successfully');
    }
}
