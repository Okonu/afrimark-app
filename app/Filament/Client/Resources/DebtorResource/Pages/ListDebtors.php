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
            Actions\Action::make('import')
                ->label('Import Debtors')
//                ->url('/client/debtors/import')
                ->icon('heroicon-o-document-plus'),
            Actions\Action::make('import')
                ->label('Import Payments')
//                ->url('/client/debtors/import')
                ->icon('heroicon-o-document-plus'),
        ];
    }
}
