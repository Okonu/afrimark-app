<?php

namespace App\Filament\Client\Resources\BusinessResource\Widgets;

use App\Models\Business;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BusinessStatsOverview extends BaseWidget
{
    public ?Business $record = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Team Size', $this->record?->users()->count())
                ->description('Total team members')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 4, 6, 8, 5, 3, 8])
                ->color('success'),

            Stat::make('Document Status', $this->getDocumentStatusText())
                ->description($this->getDocumentDescription())
                ->descriptionIcon('heroicon-m-document-check')
                ->color($this->getDocumentStatusColor()),

            Stat::make('Business Age', $this->getBusinessAge())
                ->description('Since registration')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
        ];
    }

    protected function getDocumentStatusText(): string
    {
        $total = $this->record?->documents()->count() ?? 0;
        $verified = $this->record?->documents()->where('status', 'verified')->count() ?? 0;

        return "{$verified}/{$total} Verified";
    }

    protected function getDocumentDescription(): string
    {
        $pending = $this->record?->documents()->where('status', 'pending')->count() ?? 0;
        return $pending ? "{$pending} pending verification" : "All documents verified";
    }

    protected function getDocumentStatusColor(): string
    {
        $pending = $this->record?->documents()->where('status', 'pending')->count() ?? 0;
        return $pending ? 'warning' : 'success';
    }

    protected function getBusinessAge(): string
    {
        if (!$this->record) return 'N/A';

        $created = $this->record->created_at;
        $now = now();

        $years = $created->diffInYears($now);
        $months = $created->diffInMonths($now) % 12;

        if ($years > 0) {
            return $years . 'y ' . $months . 'm';
        }

        return $months . ' months';
    }
}
