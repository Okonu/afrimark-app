<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Services\Business\VerificationService;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Contracts\View\View;

class EmailVerification extends SimplePage
{
    protected static string $view = 'filament.client.pages.auth.email-verification';

    public $business;

    public function mount()
    {
        if (!Auth::check()) {
            $this->redirect(route('filament.client.auth.login'));
            return;
        }

        $user = Auth::user();
        $this->business = $user->businesses()->first();

        if (!$this->business) {
            $this->redirect(route('filament.client.auth.business-information'));
            return;
        }

        if ($this->business->email_verified_at) {
            $redirectInfo = Session::get('redirect_after_registration');

            if ($redirectInfo && isset($redirectInfo['route'])) {
                $route = $redirectInfo['route'];
                $params = $redirectInfo['params'] ?? [];

                Session::forget('redirect_after_registration');

                $this->redirect(route($route, $params));
                return;
            }

            $this->redirect(route('filament.client.auth.document-upload'));
            return;
        }
    }

    public function resendVerificationEmail(VerificationService $verificationService)
    {
        if (!$this->business) {
            return;
        }

        $verificationService->sendBusinessEmailVerification($this->business);

        Notification::make()
            ->title('Verification Email Sent')
            ->body('Please check your business email inbox for the verification link.')
            ->success()
            ->send();
    }

    public function skipVerification()
    {
        $redirectInfo = Session::get('redirect_after_registration');

        if ($redirectInfo && isset($redirectInfo['route'])) {
            $route = $redirectInfo['route'];
            $params = $redirectInfo['params'] ?? [];

            Session::forget('redirect_after_registration');

            $this->redirect(route($route, $params));
            return;
        }

        $this->redirect(route('filament.client.auth.document-upload'));
    }
}
