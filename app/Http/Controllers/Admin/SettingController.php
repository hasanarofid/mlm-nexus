<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display the system settings page matching Image 2 mockup.
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return Inertia::render('Admin/Settings', [
            'config' => [
                'pin_price' => (float) ($settings['pin_price'] ?? 200000),
                'sponsor_percent' => (float) ($settings['sponsor_percent'] ?? 100),
                'generation_bonus' => (float) ($settings['generation_bonus'] ?? 5000),
                'business_mode' => ($settings['business_mode'] ?? 'pin') === 'pin',
                'min_withdrawal' => (float) ($settings['min_withdrawal'] ?? 0),
                'max_level_depth' => (int) ($settings['max_level_depth'] ?? 0),
                'allow_sponsor_exceed' => ($settings['allow_sponsor_exceed'] ?? '1') === '1',
            ],
        ]);
    }

    /**
     * Update global system bonus settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'pin_price' => 'required|numeric|min:0',
            'sponsor_percent' => 'required|numeric|min:0',
            'generation_bonus' => 'nullable|numeric|min:0',
            'business_mode' => 'required|boolean',
            'min_withdrawal' => 'nullable|numeric|min:0',
            'max_level_depth' => 'required|integer|min:0',
            'allow_sponsor_exceed' => 'nullable|boolean',
        ]);

        Setting::setValue('pin_price', $validated['pin_price'], 'text');
        Setting::setValue('sponsor_percent', $validated['sponsor_percent'], 'text');
        if (isset($validated['generation_bonus'])) {
            Setting::setValue('generation_bonus', $validated['generation_bonus'], 'text');
        }
        Setting::setValue('business_mode', $validated['business_mode'] ? 'pin' : 'product', 'text');
        Setting::setValue('min_withdrawal', $validated['min_withdrawal'] ?? 0, 'text');
        Setting::setValue('max_level_depth', $validated['max_level_depth'], 'text');
        Setting::setValue('allow_sponsor_exceed', ($request->input('allow_sponsor_exceed') ? '1' : '0'), 'text');

        return redirect()->back()->with('success', 'Konfigurasi bonus & sistem jaringan berhasil disimpan.');
    }

    /**
     * Update reward packages list (kept for backward compatibility).
     */
    public function updateRewards(Request $request)
    {
        $rewards = $request->input('rewards', []);
        Setting::setValue('network_rewards', json_encode($rewards), 'json');

        return redirect()->back()->with('success', 'Pengaturan Paket Reward Jaringan berhasil diperbarui.');
    }
}
