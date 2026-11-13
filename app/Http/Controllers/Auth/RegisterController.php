<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralRewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Role-aware registration — spec §4: choose account type → capture contact → verify.
 * Paid roles (farmer/supplier) are routed to subscription checkout; free roles onboard directly.
 */
class RegisterController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.register', [
            'referralCode' => $request->session()->get('referral_code'),
        ]);
    }

    public function store(Request $request, ReferralRewardService $referrals)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:32', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'role' => ['required', 'in:farmer,supplier,buyer,transporter,job_seeker'],
            'terms' => ['accepted'],
        ]);

        $role = Role::from($data['role']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'password' => Hash::make($data['password']),
            'referral_code' => 'OGA-'.strtoupper(Str::random(6)),
        ]);

        $user->assignRole($role);

        // A5 step 2: record direct attribution from the invite session (30-day window).
        $code = $request->session()->pull('referral_code');
        if ($code) {
            $referrals->attributeSignup($user, $code);
        }

        $user->sendEmailVerificationNotification();
        Auth::login($user);

        // Spec §4 routing: paid roles → subscription checkout; free roles → dashboard.
        return $role->isPaid()
            ? redirect()->route('subscription.plans')->with('status', 'Complete your subscription to activate your account.')
            : redirect()->route('dashboard');
    }
}
