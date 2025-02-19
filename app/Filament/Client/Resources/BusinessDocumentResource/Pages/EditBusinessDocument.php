<?php

namespace App\Filament\Client\Resources\BusinessDocumentResource\Pages;

use App\Filament\Client\Resources\BusinessDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusinessDocument extends EditRecord
{
    protected static string $resource = BusinessDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
