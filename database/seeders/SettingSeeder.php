<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'TALENTA52',
                'type' => 'text',
            ],
            [
                'key' => 'company_name',
                'value' => 'PT.Talenta52 Punya Kita',
                'type' => 'text',
            ],
            [
                'key' => 'company_owner',
                'value' => 'PT.Talenta52 Punya Kita',
                'type' => 'text',
            ],
            [
                'key' => 'company_copyright',
                'value' => 'PT.Talenta52 Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
                'type' => 'text',
            ],
            [
                'key' => 'company_banks',
                'value' => json_encode([
                    [
                        'type'           => 'bank',
                        'bank_name'      => 'Bank BRI',
                        'account_number' => '806401000095564',
                        'account_name'   => 'PT.Talenta52 Punya Kita',
                    ],
                ]),
                'type' => 'json',
            ],
            // company_profile: JSON tunggal yang dibaca Setting::get('company_profile')
            // Berisi banks, whatsapp, name, owner, copyright untuk DashboardController & PremiInvoice
            [
                'key' => 'company_profile',
                'value' => json_encode([
                    'name'      => 'PT.Talenta52 Punya Kita',
                    'owner'     => 'PT.Talenta52 Punya Kita',
                    'copyright' => 'PT.Talenta52 Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
                    'whatsapp'  => '6281234567890',
                    'banks'     => [
                        [
                            'type'           => 'bank',
                            'bank_name'      => 'Bank BRI',
                            'account_number' => '806401000095564',
                            'account_name'   => 'PT.Talenta52 Punya Kita',
                        ],
                    ],
                ]),
                'type' => 'json',
            ],
            [
                'key' => 'site_description',
                'value' => 'Platform jaringan mitra & manajemen bonus unilevel matahari multi-tier TALENTA52.',
                'type' => 'textarea',
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '6281234567890',
                'type' => 'text',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
