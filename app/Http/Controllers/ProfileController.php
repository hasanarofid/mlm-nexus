<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the Corporate & Administrator / Member Profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $isAdmin = ($user->username === 'admin' || $user->email === 'admin@talenta52.com');
        if (!$isAdmin) {
            try {
                $isAdmin = $user->hasRole('admin');
            } catch (\Throwable $e) {
                $isAdmin = false;
            }
        }

        $settings = Setting::all()->pluck('value', 'key');
        $companyBanks = json_decode($settings['company_banks'] ?? '[]', true);

        return Inertia::render('Profile/Edit', [
            'is_admin' => $isAdmin,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? '',
                'nik' => $user->nik ?? '',
                'ktp_image' => $user->ktp_image ?? null,
                'ktp_image_url' => !empty($user->ktp_image) ? (str_starts_with($user->ktp_image, '/') ? $user->ktp_image : '/' . $user->ktp_image) : null,
                'gender' => $user->gender ?? '',
                'birth_place' => $user->birth_place ?? '',
                'birth_date' => $user->birth_date ? (is_string($user->birth_date) ? substr($user->birth_date, 0, 10) : $user->birth_date->format('Y-m-d')) : '',
                'religion' => $user->religion ?? '',
                'marital_status' => $user->marital_status ?? '',
                'last_education' => $user->last_education ?? '',
                'occupation' => $user->occupation ?? '',
                'address' => $user->address ?? '',
                'province' => $user->province ?? '',
                'city' => $user->city ?? '',
                'district' => $user->district ?? '',
                'village' => $user->village ?? '',
                'postal_code' => $user->postal_code ?? '',
                'beneficiary_name' => $user->beneficiary_name ?? '',
                'beneficiary_relation' => $user->beneficiary_relation ?? '',
                'beneficiary_phone' => $user->beneficiary_phone ?? '',
                'bank_name' => $user->bank_name ?? 'Bank BRI',
                'bank_account_number' => $user->bank_account_number ?? '',
                'bank_account_name' => $user->bank_account_name ?? '',
            ],
            'admin_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? 'admin',
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
            ],
            'company_profile' => [
                'name' => $settings['company_name'] ?? 'PT.Talenta52 Punya Kita',
                'owner' => $settings['company_owner'] ?? 'PT.Talenta52 Punya Kita',
                'copyright' => $settings['company_copyright'] ?? 'PT.Talenta52 Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
                'logo_url' => !empty($settings['site_logo']) ? (str_starts_with($settings['site_logo'], '/') ? $settings['site_logo'] : '/' . $settings['site_logo']) : null,
                'banks' => is_array($companyBanks) && count($companyBanks) > 0 ? $companyBanks : [
                    [
                        'type' => 'bank',
                        'bank_name' => 'Bank BRI',
                        'account_number' => '806401000095564',
                        'account_name' => 'PT.Talenta52 Punya Kita',
                    ],
                    [
                        'type' => 'ewallet',
                        'bank_name' => 'DANA (E-Wallet)',
                        'account_number' => '081234567890',
                        'account_name' => 'PT.Talenta52 Punya Kita',
                    ]
                ],
            ],
            'status' => session('status'),
        ]);
    }

    /**
     * Update Profile Credentials & Bank Accounts.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = ($user->username === 'admin' || $user->email === 'admin@talenta52.com');
        if (!$isAdmin) {
            try {
                $isAdmin = $user->hasRole('admin');
            } catch (\Throwable $e) {
                $isAdmin = false;
            }
        }

        if ($request->has('site_logo') && !($request->file('site_logo') instanceof \Illuminate\Http\UploadedFile)) {
            $request->request->remove('site_logo');
        }

        if ($request->has('ktp_image') && !($request->file('ktp_image') instanceof \Illuminate\Http\UploadedFile)) {
            $request->request->remove('ktp_image');
        }

        $rules = [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
        ];

        if ($isAdmin) {
            $rules['company_name'] = 'required|string|max:100';
            $rules['company_owner'] = 'required|string|max:100';
            $rules['company_copyright'] = 'required|string|max:255';
            $rules['site_logo'] = 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048';
        } else {
            $rules['nik'] = 'nullable|string|max:20';
            $rules['ktp_image'] = 'nullable|file|mimes:png,jpg,jpeg,webp|max:5120';
            $rules['gender'] = 'nullable|string|max:20';
            $rules['birth_place'] = 'nullable|string|max:100';
            $rules['birth_date'] = 'nullable|date';
            $rules['religion'] = 'nullable|string|max:50';
            $rules['marital_status'] = 'nullable|string|max:50';
            $rules['last_education'] = 'nullable|string|max:50';
            $rules['occupation'] = 'nullable|string|max:100';
            $rules['address'] = 'nullable|string|max:500';
            $rules['province'] = 'nullable|string|max:100';
            $rules['city'] = 'nullable|string|max:100';
            $rules['district'] = 'nullable|string|max:100';
            $rules['village'] = 'nullable|string|max:100';
            $rules['postal_code'] = 'nullable|string|max:10';
            $rules['beneficiary_name'] = 'nullable|string|max:100';
            $rules['beneficiary_relation'] = 'nullable|string|max:50';
            $rules['beneficiary_phone'] = 'nullable|string|max:25';
            $rules['bank_name'] = 'nullable|string|max:100';
            $rules['bank_account_number'] = 'nullable|string|max:100';
            $rules['bank_account_name'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        if ($isAdmin) {
            Setting::setValue('company_name', $validated['company_name'] ?? 'PT.Talenta52 Punya Kita', 'text');
            Setting::setValue('company_owner', $validated['company_owner'] ?? 'PT.Talenta52 Punya Kita', 'text');
            Setting::setValue('company_copyright', $validated['company_copyright'] ?? 'PT.Talenta52 Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.', 'text');

            if ($request->hasFile('site_logo')) {
                $file = $request->file('site_logo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/settings'), $filename);
                Setting::setValue('site_logo', '/images/settings/' . $filename, 'image');
            }
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        if (array_key_exists('phone', $validated) && \Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
            $user->phone = $validated['phone'];
        }

        $foundationFields = [
            'nik', 'gender', 'birth_place', 'birth_date', 'religion',
            'marital_status', 'last_education', 'occupation',
            'address', 'province', 'city', 'district', 'village', 'postal_code',
            'beneficiary_name', 'beneficiary_relation', 'beneficiary_phone',
            'bank_name', 'bank_account_number', 'bank_account_name'
        ];

        foreach ($foundationFields as $field) {
            if (array_key_exists($field, $validated) && \Illuminate\Support\Facades\Schema::hasColumn('users', $field)) {
                $user->{$field} = $validated[$field];
            }
        }

        if ($request->hasFile('ktp_image') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'ktp_image')) {
            $file = $request->file('ktp_image');
            $filename = 'ktp_' . $user->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('images/ktp');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $user->ktp_image = '/images/ktp/' . $filename;
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Add or delete company bank account & virtual wallet.
     */
    public function updateBanks(Request $request): RedirectResponse
    {
        $banks = $request->input('banks', []);
        Setting::setValue('company_banks', json_encode($banks), 'json');

        return Redirect::route('profile.edit')->with('success', 'Daftar rekening bank & virtual wallet perusahaan berhasil diperbarui.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
