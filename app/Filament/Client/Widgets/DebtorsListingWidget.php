<?php

namespace App\Filament\Client\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Debtor;

class DebtorsListingWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Debtor Activities';

    protected int | string | array $columnSpan = 'full';
    protected bool $showRecordsPerPageDropdown = false;

    protected function getTableQuery(): Builder
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return Debtor::query()->where('id', 0);
        }

        return Debtor::query()
            ->where(function ($query) use ($business) {
                $query->whereHas('businesses', function ($subQuery) use ($business) {
                    $subQuery->where('businesses.id', $business->id);
                })
                    ->orWhere('kra_pin', $business->registration_number);
            })
            ->whereIn('status', ['active', 'disputed', 'pending'])
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Business Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('business.name')
                ->label('Listed By')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('amount_owed')
                ->label('Amount')
                ->money('KES')
                ->sortable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'danger' => 'disputed',
                    'warning' => 'pending',
                    'success' => 'active',
                    'primary' => 'paid',
                ]),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Listed Date')
                ->dateTime()
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->url(fn (Debtor $record): string => route('filament.client.resources.debtors.view', $record)),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10];
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 5;
    }
}
