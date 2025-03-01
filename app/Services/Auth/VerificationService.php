<?php

namespace App\Services\Business;

use App\Models\Business;
use App\Models\BusinessVerification;
use App\Mail\BusinessVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VerificationService
{
    /**
     * Send business email verification
     *
     * @param \App\Models\Business $business
     * @return void
     */
    public function sendBusinessEmailVerification(Business $business)
    {
        $token = Str::random(64);

        BusinessVerification::where('business_id', $business->id)->delete();

        BusinessVerification::create([
            'business_id' => $business->id,
            'token' => $token,
        ]);

        Mail::to($business->email)->send(new BusinessVerificationMail($business, $token));
    }

    /**
     * Verify business email with token
     *
     * @param string $token
     * @return bool
     */
    public function verifyBusinessEmail($token)
    {
        $verification = BusinessVerification::where('token', $token)->first();

        if (!$verification) {
            return false;
        }

        $business = Business::find($verification->business_id);
        $business->email_verified_at = now();
        $business->save();

        $verification->verified_at = now();
        $verification->save();

        return true;
    }

    /**
     * Check if a business has a verified email
     *
     * @param \App\Models\Business $business
     * @return bool
     */
    public function hasVerifiedEmail(Business $business)
    {
        return $business->email_verified_at !== null;
    }
}
