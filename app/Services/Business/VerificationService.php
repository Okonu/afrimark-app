<?php

namespace App\Services\Business;

use App\Models\Business;
use App\Models\BusinessVerification;
use App\Mail\BusinessVerificationMail;
use Illuminate\Support\Facades\Log;
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
        if (!$business) {
            Log::error('Cannot send verification email - business is null');
            return;
        }

        if (!$business->email) {
            Log::error('Cannot send verification email - business email is null', ['business_id' => $business->id]);
            return;
        }

        $token = Str::random(64);

        Log::info('Sending verification email', [
            'business_id' => $business->id,
            'business_email' => $business->email,
            'token' => $token,
        ]);

        BusinessVerification::where('business_id', $business->id)->delete();

        BusinessVerification::create([
            'business_id' => $business->id,
            'token' => $token,
        ]);

        try {
            Mail::to($business->email)->send(new BusinessVerificationMail($business, $token));
            Log::info('Verification email sent successfully', ['business_email' => $business->email]);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'business_email' => $business->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
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
            Log::warning('Invalid verification token attempted', ['token' => $token]);
            return false;
        }

        $business = Business::find($verification->business_id);

        if (!$business) {
            Log::error('Business not found for verification', [
                'business_id' => $verification->business_id,
                'token' => $token
            ]);
            return false;
        }

        $business->email_verified_at = now();
        $business->save();

        $verification->verified_at = now();
        $verification->save();

        Log::info('Business email verified successfully', [
            'business_id' => $business->id,
            'business_email' => $business->email
        ]);

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
