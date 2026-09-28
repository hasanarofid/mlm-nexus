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

        $defaultRewards = [
            [
                'id' => 1,
                'name' => 'Silver Reward',
                'pairs' => 10,
                'bonus_cash' => 1000000,
                'description' => 'HP Android / Rp 1 Juta',
            ],
            [
                'id' => 2,
                'name' => 'Gold Reward',
                'pairs' => 50,
                'bonus_cash' => 5000000,
                'description' => 'Laptop / Rp 5 Juta',
            ],
            [
                'id' => 3,
                'name' => 'Platinum Reward',
                'pairs' => 250,
                'bonus_cash' => 25000000,
                'description' => 'Motor / Rp 25 Juta',
            ],
            [
                'id' => 4,
                'name' => 'Diamond Reward',
                'pairs' => 1000,
                'bonus_cash' => 150000000,
                'description' => 'Mobil / Rp 150 Juta',
            ],
            [
                'id' => 5,
                'name' => 'Crown Reward',
                'pairs' => 5000,
                'bonus_cash' => 750000000,
                'description' => 'Rumah Mewah / Rp 750 Juta',
            ],
        ];

        $savedRewards = json_decode($settings['network_rewards'] ?? '[]', true);
        $rewards = !empty($savedRewards) ? $savedRewards : $defaultRewards;

        return Inertia::render('Admin/Settings', [
            'config' => [
                'pin_price' => (float) ($settings['pin_price'] ?? 200000),
                'sponsor_percent' => (float) ($settings['sponsor_percent'] ?? 100),
                'pairing_percent' => (float) ($settings['pairing_percent'] ?? 50),
                'titik_percent' => (float) ($settings['titik_percent'] ?? 1),
                'silver_reward_percent' => (float) ($settings['silver_reward_percent'] ?? 500),
                'gold_reward_percent' => (float) ($settings['gold_reward_percent'] ?? 2500),
                'platinum_reward_percent' => (float) ($settings['platinum_reward_percent'] ?? 12500),
                'diamond_reward_percent' => (float) ($settings['diamond_reward_percent'] ?? 75000),
                'crown_reward_percent' => (float) ($settings['crown_reward_percent'] ?? 375000),
                'business_mode' => ($settings['business_mode'] ?? 'pin') === 'pin',
                'min_withdrawal' => (float) ($settings['min_withdrawal'] ?? 250000),
                'max_level_depth' => (int) ($settings['max_level_depth'] ?? 0),
                'allow_sponsor_exceed' => ($settings['allow_sponsor_exceed'] ?? '1') === '1',
                'allow_pairing_exceed' => ($settings['allow_pairing_exceed'] ?? '1') === '1',
                'allow_titik_exceed' => ($settings['allow_titik_exceed'] ?? '1') === '1',
                'allow_reward_exceed' => ($settings['allow_reward_exceed'] ?? '1') === '1',
            ],
            'rewards' => $rewards,
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
            'pairing_percent' => 'required|numeric|min:0',
            'titik_percent' => 'required|numeric|min:0',
            'silver_reward_percent' => 'nullable|numeric|min:0',
            'gold_reward_percent' => 'nullable|numeric|min:0',
            'platinum_reward_percent' => 'nullable|numeric|min:0',
            'diamond_reward_percent' => 'nullable|numeric|min:0',
            'crown_reward_percent' => 'nullable|numeric|min:0',
            'business_mode' => 'required|boolean',
            'min_withdrawal' => 'required|numeric|min:0',
            'max_level_depth' => 'required|integer|min:0',
            'allow_sponsor_exceed' => 'nullable|boolean',
            'allow_pairing_exceed' => 'nullable|boolean',
            'allow_titik_exceed' => 'nullable|boolean',
            'allow_reward_exceed' => 'nullable|boolean',
        ]);

        Setting::setValue('pin_price', $validated['pin_price'], 'text');
        Setting::setValue('sponsor_percent', $validated['sponsor_percent'], 'text');
        Setting::setValue('pairing_percent', $validated['pairing_percent'], 'text');
        Setting::setValue('titik_percent', $validated['titik_percent'], 'text');
        Setting::setValue('silver_reward_percent', $validated['silver_reward_percent'] ?? 500, 'text');
        Setting::setValue('gold_reward_percent', $validated['gold_reward_percent'] ?? 2500, 'text');
        Setting::setValue('platinum_reward_percent', $validated['platinum_reward_percent'] ?? 12500, 'text');
        Setting::setValue('diamond_reward_percent', $validated['diamond_reward_percent'] ?? 75000, 'text');
        Setting::setValue('crown_reward_percent', $validated['crown_reward_percent'] ?? 375000, 'text');
        Setting::setValue('business_mode', $validated['business_mode'] ? 'pin' : 'product', 'text');
        Setting::setValue('min_withdrawal', $validated['min_withdrawal'], 'text');
        Setting::setValue('max_level_depth', $validated['max_level_depth'], 'text');
        Setting::setValue('allow_sponsor_exceed', ($request->input('allow_sponsor_exceed') ? '1' : '0'), 'text');
        Setting::setValue('allow_pairing_exceed', ($request->input('allow_pairing_exceed') ? '1' : '0'), 'text');
        Setting::setValue('allow_titik_exceed', ($request->input('allow_titik_exceed') ? '1' : '0'), 'text');
        Setting::setValue('allow_reward_exceed', ($request->input('allow_reward_exceed') ? '1' : '0'), 'text');

        return redirect()->back()->with('success', 'Konfigurasi persentase bonus & biaya pendaftaran berhasil disimpan.');
    }

    /**
     * Update reward packages list.
     */
    public function updateRewards(Request $request)
    {
        $rewards = $request->input('rewards', []);
        Setting::setValue('network_rewards', json_encode($rewards), 'json');

        return redirect()->back()->with('success', 'Pengaturan Paket Reward Jaringan berhasil diperbarui.');
    }
}
