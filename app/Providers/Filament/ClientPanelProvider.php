<?php

namespace App\Providers\Filament;

use App\Filament\Client\Pages\Auth\BusinessInformation;
use App\Filament\Client\Pages\Auth\ContactPersonDetails;
use App\Filament\Client\Pages\Auth\DocumentUpload;
use App\Filament\Client\Pages\Auth\EmailVerification;
use App\Filament\Client\Pages\Auth\Login;
use App\Filament\Client\Pages\Auth\BusinessRegistration;
use App\Filament\Client\Widgets\BusinessStatsWidget;
use App\Filament\Client\Widgets\DebtorsListingWidget;
use App\Filament\Client\Widgets\OnboardingProgressWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ClientPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('client')
            ->path('client')
            ->login(Login::class)
            ->registration(ContactPersonDetails::class)
            ->passwordReset()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Client/Resources'), for: 'App\\Filament\\Client\\Resources')
            ->discoverPages(in: app_path('Filament/Client/Pages'), for: 'App\\Filament\\Client\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                BusinessStatsWidget::class,
                OnboardingProgressWidget::class,
                DebtorsListingWidget::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Client/Widgets'), for: 'App\\Filament\\Client\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->brandName('Business Portal')
            ->favicon(asset('images/favicon.png'));
    }
}
