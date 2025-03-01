<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthService
{
    /**
     * Redirect to social provider
     *
     * @param string $provider
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from social provider
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(16)),
                ]);
            }

            Auth::login($user);

            return $this->getNextStepUrl($user);

        } catch (\Exception $e) {
            return redirect()->route('filament.client.auth.login')
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }
    }

    /**
     * Determine the next step in the registration flow
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function getNextStepUrl(User $user)
    {
        $business = $user->businesses()->first();

        if (!$business) {
            return redirect()->route('filament.client.auth.business-information');
        }

        if (!$business->email_verified_at) {
            return redirect()->route('filament.client.auth.email-verification');
        }

        $hasDocuments = $business->documents()->count() > 0;

        if (!$hasDocuments) {
            return redirect()->route('filament.client.auth.document-upload');
        }

        return redirect()->route('filament.client.pages.dashboard');
    }
}
