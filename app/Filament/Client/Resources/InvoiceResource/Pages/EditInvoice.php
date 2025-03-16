<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
//            Actions\ForceDeleteAction::make(),
//            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['due_amount']) && isset($data['invoice_amount'])) {
            $data['due_amount'] = $data['invoice_amount'];
        }

        return $data;
    }
}
