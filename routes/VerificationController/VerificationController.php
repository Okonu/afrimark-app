<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\Business\VerificationService;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    protected $verificationService;

    public function __construct(VerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Verify business email.
     *
     * @param string $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify($token)
    {
        $verified = $this->verificationService->verifyBusinessEmail($token);

        if ($verified) {
            return redirect()->route('filament.client.auth.document-upload')
                ->with('success', 'Email verified successfully! You can now continue with your business registration.');
        }

        return redirect()->route('filament.client.auth.email-verification')
            ->withErrors(['token' => 'Invalid or expired verification token.']);
    }
}
