<?php

namespace App\Filament\Client\Resources\BusinessDocumentResource\Pages;

use App\Filament\Client\Resources\BusinessDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessDocument extends ViewRecord
{
    protected static string $resource = BusinessDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
