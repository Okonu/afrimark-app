<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['business_id'] = Auth::user()->businesses()->first()?->id;

        if (!isset($data['due_amount']) && isset($data['invoice_amount'])) {
            $data['due_amount'] = $data['invoice_amount'];
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
