<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BonusLog;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $ref = $request->query('sponsor') 
            ?? $request->query('ref') 
            ?? $request->query('referral') 
            ?? $request->query('reff') 
            ?? '';

        $cleanRef = ltrim(trim($ref), '@');
        $sponsorInfo = null;
        if (!empty($cleanRef)) {
            $sponsor = User::where('username', $cleanRef)
                ->orWhere('username', $ref)
                ->orWhere('email', $ref)
                ->first();
            if ($sponsor) {
                $sponsorInfo = [
                    'name' => $sponsor->name,
                    'username' => $sponsor->username,
                    'package_name' => $sponsor->package_name ?: 'Standard',
                ];
            }
        }

        return Inertia::render('Auth/Register', [
            'referral_code' => (string) $ref,
            'initial_sponsor' => $sponsorInfo,
        ]);
    }

    /**
     * Check if sponsor username/referral is valid (for live validation).
     */
    public function checkSponsor(Request $request)
    {
        $username = trim($request->query('username', ''));
        $cleanUsername = ltrim($username, '@');

        if (empty($username)) {
            return response()->json([
                'valid' => false,
                'message' => 'Username sponsor wajib diisi.',
            ]);
        }

        $sponsor = User::where('username', $cleanUsername)
            ->orWhere('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($sponsor) {
            return response()->json([
                'valid' => true,
                'sponsor' => [
                    'name' => $sponsor->name,
                    'username' => $sponsor->username,
                    'package_name' => $sponsor->package_name ?: 'Standard',
                ],
                'message' => 'Sponsor valid: ' . $sponsor->name . ' (@' . $sponsor->username . ')',
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Username/Kode Sponsor "' . $username . '" tidak ditemukan. Mohon periksa kembali.',
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:25',
            'is_left_handed' => 'required|string|in:Iya,Tidak',
            'beneficiary_name' => 'required|string|max:255',
            'beneficiary_birth_date' => 'required|date',
            'beneficiary_relation' => 'required|string|max:100',
            'emergency_phone' => 'required|string|max:25',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'ktp_image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral' => 'required|string|max:255',
        ], [
            'referral.required' => 'Kode Referral / Username Sponsor wajib diisi.',
        ]);

        $rawReferral = trim($request->referral);
        $cleanReferral = ltrim($rawReferral, '@');

        $sponsor = User::where('username', $cleanReferral)
            ->orWhere('username', $rawReferral)
            ->orWhere('email', $rawReferral)
            ->first();

        if (!$sponsor) {
            throw ValidationException::withMessages([
                'referral' => 'Kode Referral / Username Sponsor "' . $rawReferral . '" tidak ditemukan dalam sistem. Pendaftaran tidak dapat diproses jika kode sponsor salah.',
            ]);
        }

        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $file = $request->file('ktp_image');
            $filename = 'ktp_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destination = public_path('images/ktp');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $ktpPath = '/images/ktp/' . $filename;
        }

        // Generate username unik dari email
        $usernameBase = strtolower(explode('@', $request->email)[0]);
        $usernameBase = preg_replace('/[^a-z0-9_]/', '', $usernameBase) ?: 'member';
        $username = $usernameBase;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $usernameBase . $counter++;
        }

        $user = null;
        DB::transaction(function () use ($request, $sponsor, $username, $ktpPath, &$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $username,
                'phone' => $request->phone,
                'is_left_handed' => $request->is_left_handed,
                'beneficiary_name' => $request->beneficiary_name,
                'beneficiary_birth_date' => $request->beneficiary_birth_date,
                'beneficiary_relation' => $request->beneficiary_relation,
                'beneficiary_phone' => $request->emergency_phone,
                'emergency_phone' => $request->emergency_phone,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name ?: $request->name,
                'ktp_image' => $ktpPath,
                'password' => Hash::make($request->password),
                'parent_id' => $sponsor->id,
            ]);

            try {
                $user->assignRole('client');
            } catch (\Throwable $e) {
                // Role fallback
            }

            // Allocation Bonus Sponsor (Gen 1: Rp 250.000) & Bonus Tim (Gen 2-10: Rp 5.000 / member)
            $sponsorBonus = 250000;
            $bonusPerGen = 5000;

            // Direct Sponsor (Gen 1)
            $sponsor->increment('saldo', $sponsorBonus);
            $sponsor->increment('total_bonus', $sponsorBonus);

            BonusLog::create([
                'transaction_code' => 'SP' . sprintf('%03d', BonusLog::count() + 1),
                'user_id' => $sponsor->id,
                'category' => 'sponsor',
                'source_user_id' => $user->id,
                'description' => "Bonus Sponsor Langsung dari pendaftaran @{$user->username}",
                'amount' => $sponsorBonus,
            ]);

            WalletTransaction::create([
                'user_id' => $sponsor->id,
                'type' => 'in',
                'category' => 'bonus_sponsor',
                'amount' => $sponsorBonus,
                'description' => "Bonus Sponsor Langsung dari pendaftaran @{$user->username}",
            ]);

            // Multi-tier Gen 2 s/d Gen 10 Uplines (Rp 5.000 / level per member baru)
            $currentUpline = $sponsor;
            for ($gen = 2; $gen <= 10; $gen++) {
                if (!$currentUpline->parent_id) {
                    break;
                }

                $upline = User::find($currentUpline->parent_id);
                if (!$upline) {
                    break;
                }

                $upline->increment('saldo', $bonusPerGen);
                $upline->increment('total_bonus', $bonusPerGen);

                BonusLog::create([
                    'transaction_code' => 'G' . sprintf('%03d', BonusLog::count() + 1),
                    'user_id' => $upline->id,
                    'category' => 'generasi',
                    'source_user_id' => $user->id,
                    'description' => "Bonus Tim Gen {$gen} dari pendaftaran @{$user->username}",
                    'amount' => $bonusPerGen,
                ]);

                WalletTransaction::create([
                    'user_id' => $upline->id,
                    'type' => 'in',
                    'category' => 'bonus_generasi',
                    'amount' => $bonusPerGen,
                    'description' => "Bonus Tim Gen {$gen} dari pendaftaran @{$user->username}",
                ]);

                $currentUpline = $upline;
            }
        });

        event(new Registered($user));

        try {
            $user->notify(new \App\Notifications\WelcomeRegisterNotification($user, $request->password));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengirim email pendaftaran: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
