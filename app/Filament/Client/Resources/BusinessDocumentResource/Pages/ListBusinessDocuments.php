<?php

namespace App\Filament\Client\Resources\BusinessDocumentResource\Pages;

use App\Filament\Client\Resources\BusinessDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusinessDocuments extends ListRecords
{
    protected static string $resource = BusinessDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
