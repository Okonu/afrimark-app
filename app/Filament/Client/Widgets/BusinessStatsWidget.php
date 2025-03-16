<?php

namespace App\Filament\Client\Widgets;

use App\Models\Debtor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return [];
        }

        $totalOwed = $business->debtors()
            ->wherePivot('business_id', $business->id)
            ->where('status', 'active')
            ->sum('business_debtor.amount_owed');

        $totalOwing = DB::table('debtors')
            ->join('business_debtor', 'debtors.id', '=', 'business_debtor.debtor_id')
            ->where('debtors.kra_pin', $business->registration_number)
            ->where('debtors.status', 'active')
            ->whereNull('debtors.deleted_at')
            ->sum('business_debtor.amount_owed');

        $activeDebtors = $business->debtors()
            ->where('status', 'active')
            ->count();

        $listedByCount = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'active')
            ->count();

        return [
            Stat::make('Total Amount Owed To You', number_format($totalOwed, 2) . ' KES')
                ->description('From active debtors')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Amount You Owe', number_format($totalOwing, 2) . ' KES')
                ->description('To other businesses')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Active Debtors', $activeDebtors)
                ->description('Businesses owing you')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Listed By', $listedByCount)
                ->description('Businesses listing you')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}
