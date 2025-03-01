<?php

namespace App\Filament\Client\Pages\Auth;

use App\Models\Business;
use App\Services\Business\VerificationService;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class EmailVerification extends SimplePage
{
    protected static string $view = 'filament.client.pages.auth.email-verification';

    protected $business;

    public function mount()
    {
        if (!Auth::check()) {
            $this->redirect(route('filament.client.auth.login'));
            return;
        }

        $user = Auth::user();
        $this->business = $user->businesses()->first();

        if (!$this->business || $this->business->email_verified_at) {
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
        $this->redirect(route('filament.client.auth.document-upload'));
    }

    public function render(): View
    {
        return view('filament.client.pages.auth.email-verification', [
            'business' => $this->business,
        ]);
    }
}
