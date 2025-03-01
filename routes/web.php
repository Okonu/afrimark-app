<?php

use App\Filament\Client\Pages\Auth\BusinessInformation;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Business\VerificationController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])->name('social.login');
Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback']);

// Business email verification
Route::get('/business/verify/{token}', [VerificationController::class, 'verify'])->name('business.verify');

// Debtor dispute from email
Route::get('/debtor/dispute/{id}', function ($id) {
    return redirect()->route('filament.client.resources.disputes.create', ['debtor' => $id]);
})->name('debtor.dispute');

Route::get('/business-information', BusinessInformation::class)
    ->name('filament.client.auth.business-information')
    ->middleware(['web', 'auth']);
