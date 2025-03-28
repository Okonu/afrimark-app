<?php

use App\Filament\Client\Pages\Auth\BusinessInformation;
use App\Filament\Client\Pages\Auth\DocumentUpload;
use App\Filament\Client\Pages\Auth\EmailVerification;
use App\Filament\Client\Pages\DisputesPageManager;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Debtor\DebtorVerificationController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('debug-route', function() {
    return 'Debug route works!';
});

Filament\Facades\Filament::registerPages([
    DisputesPageManager::class,
]);

Route::get('debtor/verify', [DebtorVerificationController::class, 'verify'])
    ->name('debtor.verify');

Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])->name('social.login');
Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback']);

// Business email verification
Route::get('verify-email/{token}', [App\Http\Controllers\Business\VerificationController::class, 'verify'])
    ->name('business.verification.verify');

// Debtor dispute from email
Route::get('/debtor/dispute/{id}', function ($id) {
    return redirect()->route('filament.client.resources.disputes.create', ['debtor' => $id]);
})->name('debtor.dispute');

Route::get('/business/verify/{token}', function (string $token) {
    $verificationService = app(App\Services\Business\VerificationService::class);
    $verified = $verificationService->verifyBusinessEmail($token);

    if ($verified) {
        return redirect()->route('filament.client.pages.dashboard')
            ->with('status', 'Your business email has been verified successfully!');
    }

    return redirect()->route('filament.client.auth.email-verification')
        ->with('error', 'Invalid or expired verification link.');
})->name('business.verify');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/business-information', BusinessInformation::class)
        ->name('filament.client.auth.business-information');

    Route::get('/client/email-verification', EmailVerification::class)
        ->name('filament.client.auth.email-verification');

    Route::get('/client/document-upload', DocumentUpload::class)
        ->name('filament.client.auth.document-upload');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/debtors/template/download', [\App\Http\Controllers\Debtor\DebtorTemplateController::class, 'download'])
        ->name('debtors.template.download');
});
