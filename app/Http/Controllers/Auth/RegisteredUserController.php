<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $ref = $request->query('ref') ?? $request->query('referral') ?? '';

        return Inertia::render('Auth/Register', [
            'referral_code' => $ref,
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
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
            'ktp_image' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral' => 'required|string|max:255',
        ], [
            'referral.required' => 'Kode Referral / Username Sponsor wajib diisi.',
        ]);

        $sponsor = User::where('username', trim($request->referral))
            ->orWhere('email', trim($request->referral))
            ->first();

        if (!$sponsor) {
            throw ValidationException::withMessages([
                'referral' => 'Kode Referral / Username Sponsor tidak ditemukan atau tidak valid.',
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
