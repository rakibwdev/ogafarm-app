<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * /invite/{code} deep link — spec §7: registration landing with referrer
 * attribution attached in session state; no private referrer data exposed.
 */
class InviteController extends Controller
{
    public function resolve(string $code): RedirectResponse
    {
        // Validate the code shape before persisting; never leak referrer identity here.
        if (preg_match('/^OGA-[A-Z0-9]{6}$/', strtoupper($code))) {
            session(['referral_code' => strtoupper($code)]);
        }

        return redirect()->route('register');
    }
}
