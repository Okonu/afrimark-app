<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDebtors extends ListRecords
{
    protected static string $resource = DebtorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_debtors')
                ->label('Import Debtors')
                ->url(fn (): string => $this->getResource()::getUrl('import'))
                ->icon('heroicon-o-document-plus'),
            Actions\Action::make('import_payments')
                ->label('Import Payments')
                ->url(fn (): string => $this->getResource()::getUrl('import-payments'))
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
