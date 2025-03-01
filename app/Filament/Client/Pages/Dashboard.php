<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Widgets\OnboardingProgressWidget;
use App\Filament\Client\Widgets\BusinessStatsWidget;
use App\Filament\Client\Widgets\DebtorsListingWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            OnboardingProgressWidget::class,
            BusinessStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            DebtorsListingWidget::class,
        ];
    }
}
