<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import_invoices')
                ->label('Import Invoices')
                ->url(fn (): string => $this->getResource()::getUrl('import'))
                ->icon('heroicon-o-document-plus'),
        ];
    }
}
